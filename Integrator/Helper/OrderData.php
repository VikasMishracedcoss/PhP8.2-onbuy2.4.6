<?php
/*Class
* Use To Fetch
* All The Order On
* The Basis Of Different Conditions **/

namespace Ced\Integrator\Helper;

class OrderData extends \Magento\Framework\App\Helper\AbstractHelper
{
    public function __construct(
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Framework\Session\SessionManagerInterface $coreSession,
        \Ced\Integrator\Model\Path $path
    ) {
        $this->orderRepository = $orderRepository;
        $this->path = $path;
        $this->coreSession = $coreSession;
        $this->orderfactory = $orderCollectionFactory;
    }

    public function selectedModuleDetails()
    {
        /**Function
         * Used To Fetch
         * Module Detail,Name,AccountId */

        /** Retrieving Module name,Account Id and All File From the Model>Path File */
        $moduleName = $this->path->moduleName();
        $accountId = $this->path->accountId();
        $modelFileCollection = $this->path->getFiles();
        $path = $modelFileCollection['Collection'] . 'Factory';

        /** Creating Instance of The Collection Factory Of The Selected Module */
        $objectManager =  \Magento\Framework\App\ObjectManager::getInstance();
        $order = $objectManager->get($path)->create()->addFieldToSelect('*');
        if (isset($order)) {
            /** Setting FieldFilter When EbayAccount Module is Selected  */
            if ($moduleName == 'EbayMultiAccount') {
                $order->addFieldToFilter('account_id', $accountId)
                    ->addFieldToFilter('magento_order_id', ['notnull' => true])
                    ->addFieldToFilter('magento_order_id', ['neq' => 'N/A']);
            } elseif ($moduleName == 'Amazon') {
                /** Setting FieldFilter When Amazon Module is Selected  */
                $order->addFieldToFilter('account_id', $accountId)
                    ->addFieldToFilter('magento_increment_id', ['notnull' => true])
                    ->addFieldToFilter('magento_increment_id', ['neq' => 'N/A']);
            }
            return $order;
        } else {
            return [];
        }
    }

    public function getAllOrdersDetails()
    {
        /**Function
         * To Get The
         * Details Of All The Orders
         */
        $orderid = [];
        $moduleName = $this->path->moduleName();
        $collection = $this->selectedModuleDetails();

        if ($collection != null) {
            foreach ($collection as $values) {
                /**Fetch OrderId When Amazon Module is Selected */
                if ($moduleName == 'Amazon') {
                    $orderid[] = $values['magento_increment_id'];
                } else {
                    /**Fetch OrderId When any Other Module is Selected */
                    $orderid[] = $values['magento_order_id'];
                }
            }
            return $orderid;
        } else {
            return [];
        }
    }

    public function returnOrderDetails()
    {
        /** Function
         *  Used To Fetch
         *  the Collection Of Orders That Are Returned */

        $datacollection = [];

        $orderArray = $this->getAllOrdersDetails();
        if (empty($orderArray)) {
            $orderArray = ['0'];
        }

        $datacollection = $this->orderfactory->create()
            ->addAttributeToSelect('*')->addAttributeToFilter('increment_id', ['in', $orderArray])
            ->addAttributeToFilter('status', 'closed');
        return $datacollection;
    }

    public function completedOrderDetails()
    {
        /** Function
         *  Used To Fetch
         *  the Collection Of Orders That Are Shipped Succesfully Or Completed */

        $datacollection = [];

        $orderArray = $this->getAllOrdersDetails();
        if ($orderArray != null) {
            $datacollection = $this->orderfactory->create()
                ->addAttributeToSelect('*')->addAttributeToFilter('increment_id', ['in', $orderArray])
                ->addAttributeToFilter('status', 'complete');
            return $datacollection;
        } else {
            return [];
        }
    }

    public function limitCompletedOrder()
    {
        /** Function
         *  Used To Fetch
         *  the Limited Order Details To Show on The Dashboard */

        $datacollection = [];

        $orderArray = $this->getAllOrdersDetails();
        if (empty($orderArray)) {
            $orderArray = ['0'];
        }
        $datacollection = $this->orderfactory->create()
            ->addAttributeToSelect('*')
            ->addAttributeToFilter('increment_id', ['in', $orderArray])
            ->addAttributeToFilter('status', 'complete')
            ->setOrder('increment_id', 'DESC');
        return $datacollection;
    }

    public function pendingOrder()
    {
        /** Function
         *  Used To Fetch
         *  the Pending Order Details To Show on The Dashboard */

        $datacollection = [];

        $orderArray = $this->getAllOrdersDetails();
        if (empty($orderArray)) {
            $orderArray = ['0'];
        }
        $datacollection = $this->orderfactory->create()
            ->addAttributeToSelect('*')->addAttributeToFilter('increment_id', ['in', $orderArray])
            ->addAttributeToFilter('status', 'pending');
        return $datacollection;
    }

    public function bestSellerProduct()
    {
        /** Function
         *  Used To Fetch
         *  Bestselling Product Deatils and Show on Dashboard */

        $productList = [];
        $bestProduct = [];
        $moduleName = $this->path->moduleName();
        $orderData = $this->completedOrderDetails();

        foreach ($orderData as $value) {

            /**Fetch OrderId When Amazon Module is Selected */
            if ($moduleName == 'Amazon') {
                $orderSequence = substr($value['increment_id'], strpos($value['increment_id'], '-') + 1);
                $orderData = $this->orderRepository->get($orderSequence);
            } else {
                /**Fetch OrderId When Any Other Module is Selected */
                $orderData = $this->orderRepository->get($value['increment_id']);
            }

            /**Fetch Order Product Details */
            foreach ($orderData->getitems() as $item) {
                $productSku = $item->getSku();

                /*Inserting All SKUS in The Array*/
                array_push($bestProduct, $productSku);
            }
        }
        /**Get The Count Of The Most Sold Products On The Basis Of Skus */
        $countValue = array_count_values($bestProduct);

        /** Getting Sorted Array of Product On the Basis Of Most Sold */
        arsort($countValue);

        foreach ($countValue as $key => $val) {
            array_push($productList, $key);
        }

        /* Fetch Top 5 Product From The List **/
        if (sizeof($productList) > 5) {
            $topFiveProduct = array_slice($productList, 0, 5);
            return $topFiveProduct;
        } else {
            return $productList;
        }
    }
}
