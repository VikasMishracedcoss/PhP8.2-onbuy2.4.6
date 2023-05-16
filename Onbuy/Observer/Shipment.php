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
 * @category  Ced
 * @package   Ced_Onbuy
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Onbuy\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use \Magento\Framework\App\Config\ScopeConfigInterface;

class Shipment implements ObserverInterface
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
    public $orderhelper;
    public $datahelper;
    public $_jdecode;
    protected $multiAccountHelper;

    /**
     * Shipment constructor.
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\RequestInterface $request
     * @param ScopeConfigInterface $storeManager
     * @param \Ced\Onbuy\Helper\Order $orderhelper
     * @param \Ced\Onbuy\Helper\Logger $logger
     * @param \Ced\Onbuy\Helper\Data $datahelper
     * @param \Magento\Framework\Json\Helper\Data $_jdecode
     * @param \Ced\Onbuy\Helper\MultiAccount $multiAccountHelper
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\RequestInterface $request,
        ScopeConfigInterface $storeManager,
        \Ced\Onbuy\Helper\Order $orderhelper,
        \Ced\Onbuy\Helper\Logger $logger,
        \Ced\Onbuy\Helper\Data $datahelper,
        \Magento\Framework\Json\Helper\Data $_jdecode,
        \Ced\Onbuy\Helper\MultiAccount $multiAccountHelper
    )
    {
        $this->request = $request;
        $this->registry = $registry;
        $this->objectManager = $objectManager;
        $this->scopeConfigManager = $storeManager;
        $this->orderhelper = $orderhelper;
        $this->logger = $logger;
        $this->datahelper = $datahelper;
        $this->_jdecode = $_jdecode;
        $this->multiAccountHelper = $multiAccountHelper;
    }

    /**
     * Product SKU Change event handler
     * @param \Magento\Framework\Event\Observer $observer
     * @return \Magento\Framework\Event\Observer
     */
    public function execute(Observer $observer)
    {
       
        try {
                $shipment = $observer->getEvent()->getShipment();
                
                $order = $shipment->getOrder();
                $shippingMethod = $order->getShippingMethod();
                $trackArray = [];
                foreach ($shipment->getAllTracks() as $track) {
                    $trackArray = $track->getData();
                }


           
            $datahelper = $this->objectManager->get('Ced\Onbuy\Helper\Data');
            $incrementId = $order->getIncrementId();

            $onbuyOrder = $this->objectManager->get('Ced\Onbuy\Model\Orders')->load($incrementId, 'magento_increment_id');
            $onbuyOrderId = $onbuyOrder->getOnbuyOrderId();
            $accountId = $onbuyOrder->getAccountId();
            if ($this->registry->registry('onbuy_account'))
                $this->registry->unregister('onbuy_account');
            $this->multiAccountHelper->getAccountRegistry($accountId);
            $datahelper->updateAccountVariable();
            if ($onbuyOrderId) {
                $shipTodatetime = strtotime(date('Y-m-d H:i:s'));
                $deliverydate = date("Y-m-d", $shipTodatetime) . 'T' . date("H:i:s", $shipTodatetime);
                $orderData = $this->_jdecode->jsonDecode($onbuyOrder->getOrderData(), true);
                //after ack api end
                $trackNumber = "";
                $trackId = '';
                $url = '';

                if (isset($trackArray['track_number'])) {
                    $trackNumber = (string)$trackArray['track_number'];

                    $carriers = json_decode($this->scopeConfigManager->getValue('onbuy_config/order/shipping_methods'), true);
                    foreach ($carriers as $datum) {
                        if (strtolower($trackArray['title']) == strtolower($datum['price'])) {
                            $trackId = $datum['shipping_method'];
                            $url = str_replace('[trackingId]', $trackNumber, $datum['additional_price']);
                            break;
                        }
                    }
                }

                $mappedShippingMethods = $this->scopeConfigManager->getValue('onbuy_config/onbuy_order/global_setting/ship_method');



                $itemsData = [];
                $data = $finalData = [];
                foreach ($shipment/*->getOrder()*/->getAllItems() as $item) {
                    $merchantSku = $item->getSku();

                    $quantityOrdered = $item->getQtyOrdered();
                    $quantityToShip = $item->getQty();

                    foreach ($orderData['products'] as $items) {
                        if ($items['sku'] == $merchantSku) {
                            /*$data[] = ['sku' => $merchantSku,
                                'quantity' => $quantityToShip,
                                'tracking' => ['tracking_id' => $trackId, 'number' => $trackNumber, 'url' => $trackNumber]
                            ];*/
                            $data['sku'] = $merchantSku;
                            $data['quantity'] = $quantityToShip;
                            if (!empty($trackId)){

                                $data['tracking'] = ['tracking_id' => $trackId, 'number' => $trackNumber];
                                if ($url)
                                    $data['tracking']['url'] = $url;
                                if ($trackId == 16 && (empty($trackNumber)|| empty($url))){
                                    unset($data['tracking']);
                                }
                            }
                        }
                        $finalData[] = $data;
                    }

                }
                $shipData['site_id'] = 2000;
                $shipData['orders'][] = ['order_id' => $onbuyOrderId, 'tracking' => $data['tracking']];

                if ($finalData) {
                    $dataResult = $this->datahelper->createShipmentOrderBody(
                        json_encode($shipData)
                    );
                $dataResult='{"success":true, "results":{ "T9SBC8":{ "success":true }}}';
                $dataResult = json_decode($dataResult,true);
                if (isset($dataResult['results'][$onbuyOrderId]['success']) ) {
                    $onbuyModel = $this->objectManager->get('Ced\Onbuy\Model\Orders')->load($incrementId, 'magento_increment_id');
                    $order_except = $this->scopeConfigManager->getValue('onbuy_config/order/order_except');
                    if($order_except!='1'){ 
                        $onbuyModel->setStatus('shipped');
                    }
                    $onbuyModel->setShipmentData($this->_jdecode->jsonEncode($data));
                    $onbuyModel->save();
                    } else {
                        $this->logger->addError('In Order Ship: Failure', ['path' => __METHOD__, 'Response' => json_encode($dataResult)]);
                    }
                }

            }
        }catch (\Exception $e) {

            $this->logger->addError('Ship Order Failed', ['path' => __METHOD__]);
            $this->logger->addError('Ship Order Failed Reason', $e->getMessage());
            return $observer;
        }

        return $observer;
    }
}
