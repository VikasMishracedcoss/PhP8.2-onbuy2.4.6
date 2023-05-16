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
 * @package     Ced_Walmart
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license     http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Onbuy\Observer;

use Magento\Framework\Event\ObserverInterface;

class CreditMemo implements ObserverInterface
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

    protected $stockConfiguration;


    public $walmartLogger;

    /**
     * ProductSaveBefore constructor.
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\RequestInterface $request,
        \Ced\Onbuy\Model\OrdersFactory $ordersFactory,
        \Ced\Onbuy\Helper\Data $data,
        \Ced\Onbuy\Helper\MultiAccount $multiAccount,
        \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfiguration

    ) {
        $this->request = $request;
        $this->registry  = $registry;
        $this->data = $data;
        $this->ordersFactory = $ordersFactory;
        $this->objectManager = $objectManager;
        $this->stockConfiguration = $stockConfiguration;
        $this->multiaccount = $multiAccount;
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
        try {
            /* @var $creditmemo \Magento\Sales\Model\Order\Creditmemo */
            $creditmemo = $observer->getEvent()->getCreditmemo();
            $incrementId = $creditmemo->getOrder()->getIncrementId();
            $onbuyOrder = $this->ordersFactory->create()->loadByField('magento_increment_id', $incrementId);
            $orderId = $onbuyOrder->getOnbuyOrderId();
            $accountId = $onbuyOrder->getAccountId();
            $itemsToUpdate = [];
            if ($this->registry->registry('onbuy_account'))
                $this->registry->unregister('onbuy_account');
            $this->multiaccount->getAccountRegistry($accountId);
            $this->data->updateAccountVariable();
            if ($orderId) {


            foreach ($creditmemo->getAllItems() as $item) {
                $qty = $item->getQty();
                if (($qty) || $this->stockConfiguration->isAutoReturnEnabled()) {

                    $productId = $item->getProductId();
                    $parentItemId = $item->getOrderItem()->getParentItemId();
                    /* @var $parentItem \Magento\Sales\Model\Order\Creditmemo\Item */
                    $parentItem = $parentItemId ? $creditmemo->getItemByOrderId($parentItemId) : false;
                    $qty = $parentItem ? $parentItem->getQty() * $qty : $qty;
                    if (isset($itemsToUpdate[$productId])) {
                        $itemsToUpdate[$productId] += $qty;
                    } else {
                        $itemsToUpdate[$productId] = $qty;
                    }
                }
            }

            if (!empty($itemsToUpdate)) {

                foreach ($itemsToUpdate as $productId => $qty) {
                    $oldValue = '';
                    $orderedQty = $qty;
                    try {
                        $stock = $this->objectManager->create('\Magento\CatalogInventory\Model\Stock\StockItemRepository')->get($productId);
                        $newValue = 0;
                        if ($stock->getIsInStock())
                            $newValue = $stock->getQty() + $orderedQty;

                        $model = $this->objectManager->create('Ced\Onbuy\Model\Productchange');

                        $type = \Ced\Onbuy\Model\Productchange::CRON_TYPE_INVENTORY;
                        $model->setProductChange($productId, $oldValue, $newValue, $type, $onbuyOrder->getAccountId());

                    } catch (\Exception $e) {
                        continue;
                    }

                }
                $refundReason = $this->objectManager->create('Magento\Framework\App\Config\ScopeConfigInterface')
                    ->getValue('onbuy_config/order/order_refund');
                $refundData['orders'][] = ['order_id' => $orderId,
                    'order_refund_reason_id' => $refundReason];

                $orderRefund = $this->data->orderRefund(json_encode($refundData));
                if (isset($orderRefund['success']) && $orderRefund['success']){
                    $onbuyOrder->setStatus('refunded');
                    $onbuyOrder->save();
                } else {
                    $this->_logger->addError('Order Refund Failed', ['path' => __METHOD__]);
                }

            }
        }
        } catch (\Exception $e){
            $this->logger->addError('Exception in Order Refund', ['path' => __METHOD__]);
        }
    }


}
