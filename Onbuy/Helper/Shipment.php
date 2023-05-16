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
 * @package     Ced_Onbuy
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license     http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Onbuy\Helper;

/**
 * Class Order
 * @package Ced\Onbuy\Helper
 */

use Magento\Sales\Api\Data\ShipmentInterface;
use Magento\Sales\Api\ShipmentRepositoryInterface;
use Magento\Sales\Api\Data\ShipmentTrackInterface;
use \Magento\Framework\App\Config\ScopeConfigInterface;

class Shipment extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Framework\objectManagerInterface
     */
    public $_objectManager;
    private $shipmentRepository;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    public $_storeManager;
    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    public $_jdecode;
    /**
     * @var \Ced\Onbuy\Model\ResourceModel\Orders\CollectionFactory
     */
    public $_trademeOrder;
    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    public $customerRepository;
    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    public $productRepository;
    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    public $_product;
    /**
     * @var Data
     */
    public $datahelper;
    /**
     * @var \Magento\Quote\Api\CartManagementInterface
     */
    public $cartManagementInterface;
    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    public $cartRepositoryInterface;
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    public $messageManager;
    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    public $customerFactory;
    /**
     * @var \Ced\Onbuy\Helper\MultiAccount
     */
    protected $multiAccountHelper;
    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    public $countryData;
    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $_orderRepository;


    /**
     * @var \Magento\Shipping\Model\ShipmentNotifier
     */
    protected $_shipmentNotifier;

    public function __construct(


        \Magento\Framework\Json\Helper\Data $_jdecode,
        ShipmentRepositoryInterface $shipmentRepository,
        ScopeConfigInterface $storeManager,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Sales\Model\Convert\Order $convertOrder,
        \Magento\Shipping\Model\ShipmentNotifier $shipmentNotifier,
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Catalog\Model\ProductFactory $_product,
        Logger $logger,
        Data $dataHelper,
        \Magento\Framework\Message\ManagerInterface $manager,
        \Ced\Onbuy\Helper\MultiAccount $multiAccountHelper,
        \Magento\Framework\Registry $registry,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Magento\Catalog\Helper\Product $productHelper
    ) {
        $this->shipmentRepository = $shipmentRepository;
        $this->_jdecode = $_jdecode;
        $this->_orderRepository = $orderRepository;
        $this->_convertOrder = $convertOrder;
        $this->_shipmentNotifier = $shipmentNotifier;
        $this->_product = $_product;

        parent::__construct($context);
        $this->_coreRegistry = $registry;
        $this->datahelper = $dataHelper;
        $this->messageManager = $manager;
        $this->logger = $logger;
        $this->multiAccountHelper = $multiAccountHelper;
        $this->currencyFactory = $currencyFactory;
        $this->productHelper = $productHelper;
        $this->scopeConfigManager = $storeManager;
    }

    public function markship($ship)
    {
        
        try {
            if (isset($ship['magento_increment_id']) && $ship['failed_order_reason'] == '') {

                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $orderInfo = $objectManager->create('Magento\Sales\Model\Order')->loadByIncrementId($ship['magento_increment_id']);
                if ($orderInfo->hasShipments()) {

                    $accountId = $ship->getAccountId();
                //  print_r($this->registry->registry('onbuy_account'));
               
                //     if ($this->registry->registry('onbuy_account'))
                   
                //        {$this->registry->unregister('onbuy_account');}
                    //    die(__FILE__);
                    $this->multiAccountHelper->getAccountRegistry($accountId);
                    $this->datahelper->updateAccountVariable();
                    //////////////////////////////////////
                   
                    $shipmentCollection = $orderInfo->getShipmentsCollection();
                 
                    foreach ($shipmentCollection as $shipment) {
                        
                        $shipmentId = $shipment->getId();
                    }
                    $shipment = $this->shipmentRepository->get($shipmentId);

                    $trackArray = [];
                    foreach ($shipment->getAllTracks() as $track) {
                        $trackArray = $track->getData();
                    }

                    if ($ship['onbuy_order_id']) {
                        
                        $shipTodatetime = strtotime(date('Y-m-d H:i:s'));
                        $deliverydate = date("Y-m-d", $shipTodatetime) . 'T' . date("H:i:s", $shipTodatetime);

                        $orderData = $this->_jdecode->jsonDecode($ship->getOrderData(), true);

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

                        $itemsData = [];
                        $data = $finalData = [];
                        foreach ($shipment/*->getOrder()*/->getAllItems() as $item) {
                           
                          
                            $merchantSku = $item->getSku();

                            $quantityOrdered = $item->getQtyOrdered();
                            $quantityToShip = $item->getQty();

                            foreach ($orderData['products'] as $items) {

                                if ($items['sku'] == $merchantSku) {


                                    $data['sku'] = $merchantSku;
                                    $data['quantity'] = $quantityToShip;
                                    if (!empty($trackId)) {

                                        $data['tracking'] = ['tracking_id' => $trackId, 'number' => $trackNumber];
                                        if ($url) {
                                            $data['tracking']['url'] = $url;
                                        }
                                        if ($trackId == 16 && (empty($trackNumber) || empty($url))) {
                                            //die(__FILE__);
                                            unset($data['tracking']);
                                        }
                                    }
                                }
                                $finalData[] = $data;
                            }
                        }
                        $shipData['site_id'] = 2000;
                        $shipData['orders'][] = ['order_id' => $ship['onbuy_order_id'], 'products' => $finalData];
                    
                        if ($finalData) {

                            $dataResult = $this->datahelper->createShipmentOrderBody(
                                json_encode($shipData)
                            );
                        // print_r($dataResult);
                        // die(__FILE__);
                            $incrementId = $ship['magento_increment_id'];
                            $orderId = $ship['onbuy_order_id'];
                            if (isset($dataResult['results'][$orderId]['success']) && $dataResult['results'][$orderId]['success']) {
                                $onbuyModel = $this->objectManager->get('Ced\Onbuy\Model\Orders')->load($incrementId, 'magento_increment_id');
                                $onbuyModel->setStatus('shipped');
                                $onbuyModel->setShipmentData($this->_jdecode->jsonEncode($data));
                                $onbuyModel->save();
                                $this->messageManager->addErrorMessage(
                                    __('Order marked as shipped on Onbuy')
                                );

                            } else {
                               
                                $mes= "Onbuy sent error-:".$dataResult['results'][$orderId]['errorcode']."";
                                //$this->logger->addError('In Order Ship: Failure', ['path' => __METHOD__, 'Response' => json_encode($dataResult)]);
                                $this->messageManager->addErrorMessage(
                                    __($mes)
                                );
                            }
                        }
                    }
                    /////////////////////////////////////////
                } else {

                    $this->messageManager->addErrorMessage(
                        __('Order is not shipped yet')
                    );
                }
            } else {
                $this->messageManager->addErrorMessage(
                    __('Order is not created or it is been failed')
                );
            }
        } catch (\Exception $e) {
            $e->getMessage();
        }
    }
}
