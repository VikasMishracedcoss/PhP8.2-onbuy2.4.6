<?php

/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement(EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category    Ced
 * @package     Ced_Flubit
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license     http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Onbuy\Observer;

use Ced\Onbuy\Model\ResourceModel\Orders\CollectionFactory;
use Magento\Framework\Event\ObserverInterface;

class CancelOrder implements ObserverInterface
{
    /**
     * Request
     * @var  \Magento\Framework\App\RequestInterface
     */
    public $request;

    /**
     * Object Manager
     * @var \Magento\Framework\ObjectManagerInterface
     */
    public $objectManager;

    /**
     * Registry
     * @var \Magento\Framework\Registry
     */
    public $registry;

    /**
     * @var
     */
    public $flubitLogger;

    /**
     * ProductSaveBefore constructor.
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Registry $registry,
        \Ced\Onbuy\Model\ResourceModel\Orders\CollectionFactory $collectionFactory,
        \Magento\Framework\App\RequestInterface $request

    ) {
        $this->request = $request;
        $this->registry  = $registry;
        $this->collectionFactory = $collectionFactory;
        $this->objectManager = $objectManager;
        $this->_logger =  $this->objectManager->create('\Ced\Onbuy\Helper\Logger');
    }

    public function _construct(
        \Magento\Framework\Message\ManagerInterface $messageManager

    )
    {
        $this->messageManager = $messageManager;

    }

    /**
     * Product SKU Change event handler
     * @param \Magento\Framework\Event\Observer $observer
     * @return \Magento\Framework\Event\Observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {


        try{

            $shipment = $observer->getEvent()->getShipment();
            $incrementId = $observer->getEvent()->getOrder()->getIncrementId();
            $onbuyOrder = $this->collectionFactory->create()->addFieldToFilter('magento_increment_id', $incrementId)->getFirstItem();
            $onbuyOrderId = $onbuyOrder->getOnbuyOrderId();

            if($onbuyOrderId)
            {

                //custom code end
                $shipToDatetime = strtotime(date('Y-m-d H:i:s'));
                $shipToDate = date("Y-m-d", $shipToDatetime) . 'T' . date("H:i:s", $shipToDatetime);
                $trackingUrl = '';
                $shipArray['order_id'] = $onbuyOrderId;
                $reasonId = $this->objectManager->create('Magento\Framework\App\Config\ScopeConfigInterface')
                    ->getValue('onbuy_config/order/order_cancel');
                $reason = $this->objectManager->create('Magento\Framework\App\Config\ScopeConfigInterface')
                    ->getValue('onbuy_config/order/order_cancel_reason');
//                print_r($reasonId);die;
                if ($reasonId == 5){
                    $shipArray['order_cancellation_reason_id'] = $reasonId;
                    $shipArray['cancel_order_additional_info'] = $reason;
                } else
                    $shipArray['order_cancellation_reason_id'] = $reasonId;

                $orderCancelData['orders'][] = $shipArray;
              
                $json= $this->objectManager->create('Ced\Onbuy\Helper\Data')->postRequest('https://api.onbuy.com/v2/orders/cancel?site_id=2000', json_encode($orderCancelData));
//                print_r($json);die;
                $response=json_decode($json,1);
                if(isset($response['success'])) {
                    $flubitModel = $this->objectManager->get('Ced\Flubit\Model\FlubitOrders')->load($incrementId, 'magento_order_id');
                    $flubitModel->setStatus('shipped');
                    $flubitModel->save();
                }
                //magento 1 code reuse end
            } else{

                return $observer;
            }
        }catch(\Exception $e)
        {
            return $observer;
        }
        return $observer;
    }



    /**
     * parserArray
     * {@inheritdoc}
     */
    public function parserArray($shipmentData = array())
    {

        $shipmentData = count($shipmentData)>0?$shipmentData:false;
        if(!$shipmentData)
        {
            return false;
        }
        $customArray = array();
        if (!empty($shipmentData)) {
            $arr = array();
            foreach ($shipmentData["orderLines"]['orderLine'] as $key => $value) {
                if ( in_array($key, $arr)) {
                    continue;
                }
                $count = count($shipmentData["orderLines"]['orderLine']);
                $sku = $value['item']['sku'];
                $shipQuantity = 0;
                $cancelQuantity = 0;
                $quantity = 1;
                if ($value['orderLineStatuses']['orderLineStatus'][0]['status'] == 'Shipped') {
                    $shipQuantity = 1;
                } else {
                    $cancelQuantity = 1;
                }
                $lineNumber = $value['lineNumber'];
                for ( $i = $key+1 ; $i < $count;$i++) {
                    if ($shipmentData["orderLines"]['orderLine'][$i]['item']['sku'] == $sku ) {
                        $quantity++;
                        if (
                            $shipmentData["orderLines"]['orderLine'][$i]['orderLineStatuses']
                            ['orderLineStatus'][0]['status'] == 'Shipped') {
                            $shipQuantity++;
                        } else {
                            $cancelQuantity++;
                        }
                        $lineNumber = $lineNumber.','.$shipmentData["orderLines"]['orderLine'][$i]['lineNumber'];
                        unset($shipmentData["orderLines"]['orderLine'][$i]);
                        array_push($arr, $i);
                        array_values($shipmentData["orderLines"]['orderLine']);
                    }
                }
                $shipmentData["orderLines"]['orderLine'][$key]['lineNumber'] = $lineNumber;
                $customArray[$sku] = $lineNumber;
                $shipmentData["orderLines"]['orderLine'][$key]['orderLineQuantity']['shipQuantity'] = $shipQuantity;
                $shipmentData["orderLines"]['orderLine'][$key]['orderLineQuantity']['amount'] = $quantity;
                $shipmentData["orderLines"]['orderLine'][$key]['orderLineQuantity']['cancelQuantity'] = $cancelQuantity;
            }
            return $customArray;
        }
        return false;
    }
}
