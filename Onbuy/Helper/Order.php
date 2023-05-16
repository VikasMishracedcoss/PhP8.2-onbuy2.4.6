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
class Order extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Framework\objectManagerInterface
     */
    public $_objectManager;
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
     * @var \Magento\Sales\Model\Convert\Order
     */
    protected $_convertOrder;

    /**
     * @var \Magento\Shipping\Model\ShipmentNotifier
     */
    protected $_shipmentNotifier;

    protected $creditmemoLoaderFactory;

    protected $orderService;

    protected $quote;

    protected $quoteManagement;

    protected $logger; 

    protected $currencyFactory;

    protected $productHelper;
    /**
     * Order constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\objectManagerInterface $_objectManager
     * @param \Magento\Quote\Model\QuoteFactory $quote
     * @param \Magento\Quote\Model\QuoteManagement $quoteManagement
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Catalog\Model\ProductRepository $productRepository
     * @param \Magento\Catalog\Model\Product $product
     * @param \Magento\Framework\Json\Helper\Data $_jdecode
     * @param \Ced\Onbuy\Model\ResourceModel\Orders\CollectionFactory $_trademeOrder
     * @param \Magento\Sales\Model\Service\OrderService $orderService
     * @param \Magento\Sales\Controller\Adminhtml\Order\CreditmemoLoaderFactory $creditmemoLoaderFactory
     * @param \Magento\Quote\Api\CartRepositoryInterface $cartRepositoryInterface
     * @param \Magento\Quote\Api\CartManagementInterface $cartManagementInterface
     * @param \Magento\Catalog\Model\ProductFactory $_product
     * @param Data $dataHelper
     */
    public function __construct(
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Sales\Model\Convert\Order $convertOrder,
        \Magento\Shipping\Model\ShipmentNotifier $shipmentNotifier,
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Directory\Model\CountryFactory $countryData,
        \Magento\Framework\objectManagerInterface $_objectManager,
        \Magento\Quote\Model\QuoteFactory $quote,
        \Magento\Quote\Model\QuoteManagement $quoteManagement,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Catalog\Model\Product $product,
        \Magento\Framework\Json\Helper\Data $_jdecode,
        \Ced\Onbuy\Model\ResourceModel\Orders\CollectionFactory $_trademeOrder,
        \Magento\Sales\Model\Service\OrderService $orderService,
        \Magento\Sales\Controller\Adminhtml\Order\CreditmemoLoaderFactory $creditmemoLoaderFactory,
        \Magento\Quote\Api\CartRepositoryInterface $cartRepositoryInterface,
        \Magento\Quote\Api\CartManagementInterface $cartManagementInterface,
        \Magento\Catalog\Model\ProductFactory $_product,
        Logger $logger,
        Data $dataHelper,
        \Magento\Framework\Message\ManagerInterface $manager,
        \Ced\Onbuy\Helper\MultiAccount $multiAccountHelper,
        \Magento\Framework\Registry $registry,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Magento\Catalog\Helper\Product $productHelper
    )
    {
        $this->_orderRepository = $orderRepository;
        $this->_convertOrder = $convertOrder;
        $this->_shipmentNotifier = $shipmentNotifier;
        $this->creditmemoLoaderFactory = $creditmemoLoaderFactory;
        $this->orderService = $orderService;
        $this->cartRepositoryInterface = $cartRepositoryInterface;
        $this->cartManagementInterface = $cartManagementInterface;
        $this->_objectManager = $_objectManager;
        $this->_storeManager = $storeManager;
        $this->quote = $quote;
        $this->countryData = $countryData;
        $this->quoteManagement = $quoteManagement;
        $this->_product = $product;
        $this->customerRepository = $customerRepository;
        $this->productRepository = $productRepository;
        $this->_jdecode = $_jdecode;
        $this->customerFactory = $customerFactory;
        $this->_trademeOrder = $_trademeOrder;
        $this->_product = $_product;
        parent::__construct($context);
        $this->_coreRegistry = $registry;
        $this->datahelper = $dataHelper;
        $this->messageManager = $manager;
        $this->logger = $logger;
        $this->multiAccountHelper = $multiAccountHelper;
        $this->currencyFactory = $currencyFactory;
        $this->productHelper = $productHelper;
    }

    public function fetchOrders($accountIds = [])
    {

        $orderFetchResult = array();
        foreach ($accountIds as $accountId) {
            if ($this->_coreRegistry->registry('onbuy_account'))
                $this->_coreRegistry->unregister('onbuy_account');
            $account = $this->multiAccountHelper->getAccountRegistry($accountId);
            $accountName = $account->getAccountCode();
            $this->datahelper->updateAccountVariable();
            $store_id = $account->getAccountStore();
            $websiteId = $this->_storeManager->getStore()->getWebsiteId();
            $store = $this->_storeManager->getStore($store_id)/*->setCurrentCurrency($currency)*/;
           $response = $this->datahelper->getOrders();
            
            $count = 0;
            $orderArray = [];
            $found = '';
            $orders = [];
            try {
                if (isset($response['results'])) {
                    if (!isset($response['results'][0])){
                        $singleOrder[0] = $response['results'];
                        $response['results'] = $singleOrder;
                    }

                    foreach ($response['results'] as $trademeOrder) {
                        if (isset($trademeOrder['order_id'])) {
                            $email = 'onbuy@order.com';
                            $customer = $this->_objectManager->create('Magento\Customer\Model\Customer')->setWebsiteId($websiteId)->loadByEmail($email);
                            $purchaseOrderid = $trademeOrder['order_id'];
                            $resultdata = $this->_trademeOrder->create()
                                ->addFieldToFilter('status', ['in' => ['shipped', 'acknowledged']])
                                ->addFieldToFilter('onbuy_order_id', $trademeOrder['order_id'])->getData();
                            if (empty($resultdata)) {
                                $ncustomer = $this->getCustomer($trademeOrder['delivery_address'], $customer, $email);
                                if (!$ncustomer) {
                                    return ['error' => 'Error while fetching Orders. Please check Activity Logs.'];
                                } else {
                                    $count = $this->generateQuote($store, $ncustomer, $trademeOrder, $count);
                              
                                }
                            }
                        }
                    }
                }
                if ($count > 0) {
                    $orderFetchResult['success'] = "You have " . $count . " orders from OnBuy for account " . $accountName;
                    $this->notificationSuccess($count);
                } else {
                    $orderFetchResult['error'] = 'No New Orders Found';
                }
            } catch (\Exception $e) {
                $this->sendMail('test', '34343', 'test');
                $orderFetchResult['error'] = "Order Import has some error : Please check activity Logs";
                $this->logger->addError('In Order Fetch: ' . $e->getMessage(), ['path' => __METHOD__]);
            }
        }
        return $orderFetchResult;
    }

    public function getCustomer($buyer, $customer, $email)
    {
        $customerGroupId = $this->scopeConfig->getValue('onbuy_config/order/customer_group');
        $customerData = $customer->getData();
        if (empty($customerData)) {
            try {
                $customer = $this->_objectManager->create('Magento\Customer\Model\Customer');
                $websiteId = $this->_storeManager->getStore()->getWebsiteId();
                $customer->setWebsiteId($websiteId)
                    //->setStoreId($storeId)
                    ->setFirstname($buyer['name'])
                    ->setLastname($buyer['name'])
                    ->setEmail($email)
                    ->setPassword("password")
                    ->setGroupId($customerGroupId);
                $customer->save();
                return $customer;
            } catch (\Exception $e) {
                $this->sendMail($result['order_id'], $order->getIncrementId(), $order_place);
                $this->logger->addError('In Create Customer: has exception '.$e->getMessage(), ['path' => __METHOD__]);
                return false;
            }
        } else {
           
            $customer->setFirstname($buyer['name']);
            $customer->setLastname($buyer['name']);
            $customer->save();
            return $customer;
        }
        //return $customer;
    }

    public function generateQuote($store, $ncustomer, $result, $count)
    {
        
        $order_place = date("Y-m-d");

        try {
            $encodeOrderData = $this->_jdecode->jsonEncode($result);
            if ($this->_coreRegistry->registry('onbuy_account'))
                $account = $this->_coreRegistry->registry('onbuy_account');
            $accountId = isset($account) ? $account->getId() : '';
            $orderWithoutStock = $this->scopeConfig->getValue('onbuy_config/order/order_out_of_stock');
            $shipMethod = $this->scopeConfig->getValue('onbuy_config/order/ship_method');
            $paymentMethod = $this->scopeConfig->getValue('onbuy_config/order/pay_method');
           
            $shippingcost = $result['price_delivery'];
            $cart_id = $this->cartManagementInterface->createEmptyCart();
            
            $quote = $this->cartRepositoryInterface->get($cart_id);
            $quote->setStore($store);
            $quote->setCurrency();
            $customer = $this->customerRepository->getById($ncustomer->getId());
            $quote->assignCustomer($customer);
            $transArray = $result['products'];
            if (!isset($transArray[0]))
            {
                $singleProd = $transArray;
                $transArray[0] = $singleProd;
            }
            

            foreach ($transArray as $transaction) {
                $defaultSku = false;
                $product = false;
                $firstName = $result['delivery_address']['name'];
                $lastName = $result['delivery_address']['name'];
                $date = $result['date'];
                $matches = [];
                preg_match("/\/Date\((.*?)\)\//", $date, $matches);
                if (isset($matches[1])) {
                    $order_place = $matches[1];
                    $finalDate = date("Y-m-d H:i:s", $order_place);
                } else {
                    $order_place = date("Y-m-d H:i:s");
                }
                $product = false;
                $sku = $transaction['sku'];
                if (!empty($sku)) {
                    $product_obj = $this->_objectManager->get('Magento\Catalog\Model\Product');
                    $product = $product_obj->loadByAttribute('sku', $sku);
                }

                if ($product) {
                    $product = $this->_product->create()->load($product->getEntityId());

                    if ($product->getStatus() == '1') {
                        $stockRegistry = $this->_objectManager->get('Magento\CatalogInventory\Api\StockRegistryInterface');
                        /* Get stock item */
                        $stock = $stockRegistry->getStockItem($product->getId(), $product->getStore()->getWebsiteId());
                        $stockstatus = ($stock->getQty() > 0) ? ($stock->getIsInStock() == '1' ? ($stock->getQty() >= $transaction ['quantity'] ? true : false) : false) : false;
                       
                        $orderWithoutStock = $this->scopeConfig->getValue('onbuy_config/order/order_out_of_stock');
                        if (!$stockstatus && $orderWithoutStock == 1) {
                          // print_r($result);die('d');
                            $quote->setIsSuperMode(true);
                            $this->productHelper->setSkipSaleableCheck(true);
                            $product->setIsSuperMode(true);
                            $product->unsSkipCheckRequiredOption();
                            $product->setSkipSaleableCheck(true);
                            $product->setData('salable', true);
                            $quote->setIsSuperMode(true);
                            $quote->setIgnoreOldQty(true);
                            $quote->setData("onbuy_order_id", $result['order_id']);
                            $quote->setData("onbuy_back_order", $orderWithoutStock);
                            $product->setHasOptions(false);
                            $quote->setInventoryProcessed(true);
                            $stockData = ['backorders' => 1,
                                'use_config_backorders' => 0];

                            $product->setStockData($stockData);
                            $stockstatus = true;
                          
                        }
                        if ($stockstatus) {
                            $productArray [] = [
                                'id' => $product->getEntityId(),
                                'qty' => $transaction ['quantity']];
                                $price = $transaction['unit_price'] ;
                                //$price = ($transaction['unit_price'] * 100 )/120;
                            $currencyRate = $this->currencyFactory->create()->load($store->getBaseCurrency())->getAnyRate($store->getCurrentCurrency());
                            $qty = $transaction ['quantity'];
                            $baseprice = $qty * $price;
                            $rowTotal = $price * $qty;
                            $product->setPrice($price)
                             ->setTaxClassId(null)
                            ->setTierPrice([])
                                ->setBasePrice($baseprice)
                                ->setOriginalCustomPrice($price)
                                ->setRowTotal($rowTotal)
                                ->setBaseRowTotal($rowTotal)
                               ->setTax(null);
                            $quote->addProduct($product, (int)$qty);
                        } else {
                            $this->rejectOrder($result , "No Inventory found for Product SKU: ".$product->getSku());
                            $this->sendMail($result['order_id'], $order->getIncrementId(), $order_place);
                        }
                    }
                }
               }
            if (isset($productArray)) {
                $firstname = $lastname = '';
                $lastArray = [];
                $lastname =  $lastName;
                $firstname = $firstName;
                $lastname = $lastName;
                $region = $result ['delivery_address']['country_code'];
                if (isset($result['delivery_address'] ['line_1']) && (!empty($result['delivery_address'] ['line_2']) && is_string($result['delivery_address'] ['line_2']) ||
                        !empty($result['delivery_address'] ['line_3']) && is_string($result['delivery_address'] ['line_3']) /*|| !empty($result['delivery_address'] ['town']) && is_string($result['delivery_address'] ['town'])*/)) {
                    $street = $result['delivery_address'] ['line_1'].' '.$result['delivery_address'] ['line_2'].' '.$result['delivery_address'] ['line_3']/*.' '.$result['delivery_address'] ['town']*/;
                } else {
                    $street = $result['delivery_address'] ['line_1'];
                }
                $phone = 000;
                if (isset($result['buyer']['phone'])) {
                    if (is_array($result['buyer']['phone'])) {
                        $phone = implode(', ', $result['buyer']['phone']);
                        $phone = $phone ==  '' ? 0 : $phone;
                    }
                    if (is_string($result['buyer']['phone'])) {
                        $phone = $result['buyer']['phone'];
                    }
                }

                if (isset($result['delivery_address']['name'])) {
                    $name = explode(" ", $result['delivery_address']['name'], 2);
                    $name = $name ==  '' ? [] : $name;
                    $firstname = $name[0];
                    if (isset($name[1]) && $name[1] !== 0){
                        $lastname = $name[1];
                    } else {
                        $lastname = $firstname;
                    }
                }

                $countriesData = $this->countryData->create()->getCollection();
                $countryId = 'US';
                foreach ($countriesData as $countryData) {
                    if (trim($result['delivery_address']['country']) == $countryData->getName()) {
                        $countryId = $countryData->getData('country_id');
                    }
                }

                $shipAdd = [
                    'firstname' => $firstname,
                    'lastname' => $lastname,
                    'street' => $street,
                    'city' => $result['delivery_address']['town'],
                    'country_id' => $countryId ,
                    'region' => $region,
                    'postcode' => $result ['delivery_address']['postcode'],
                    'telephone' => $phone,
                    'fax' => '',
                    'save_in_address_book' => 1
                ];
             
                $billAdd = [
                    'firstname' => $firstname,
                    'lastname' => $lastname,
                    'street' => $street,
                    'city' => $result['delivery_address']['town'],
                    'country_id' => $countryId ,
                    'region' => $region,
                    'postcode' => $result ['delivery_address']['postcode'],
                    'telephone' => $phone,
                    'fax' => '',
                    'save_in_address_book' => 1
                ];
                $orderData = [
                    'currency_id' => 'USD',
                    'email' => 'test@cedcommerce.com',
                    'shipping_address' => $shipAdd
                ];
            //    print_r($shipAdd);
            //    die(__FILE__);

                $shippingService = $result['delivery_service'];  
                
                $this->registerShippingMethod($shippingService);
                $this->registerShippingAmount($shippingcost);
              
                $quote->getBillingAddress()->addData($billAdd);
                $shippingAddress = $quote->getShippingAddress()->addData($shipAdd);
                $shippingAddress->setCollectShippingRates(true)->collectShippingRates()->setShippingMethod($shipMethod);
                $quote->setPaymentMethod($paymentMethod);
                $quote->setInventoryProcessed(false);
                
                $quote->save();
                $quote->getPayment()->importData([
                    'method' => $paymentMethod
                    
                ]);
                
                $quote->collectTotals()->save();
               
                foreach ($quote->getAllItems() as $item) {
                    $item->setDiscountAmount(0);
                    $item->setBaseDiscountAmount(0);
                    $item->setOriginalCustomPrice($item->getPrice())
                        ->setOriginalPrice($item->getPrice())
                        ->save();
                }
            
                $order = $this->cartManagementInterface->submit($quote);
                $preFix = $this->scopeConfig->getValue('onbuy_config/order/order_id_prefix');
               
                $orderId = $preFix.$order->getIncrementId();
                $order->setIncrementId($orderId)->save();
                $count = isset($order) ? $count + 1 : $count;
                foreach ($order->getAllItems() as $item) {
                    $item->setOriginalPrice($item->getPrice())
                        ->setBaseOriginalPrice($item->getPrice())
                        ->save();
                }
                // after save order
                $orderData = [
                    'onbuy_order_id' => $result['order_id'],
                    'order_place_date' => $order_place,
                    'magento_increment_id' => $order->getIncrementId(),
                    'status' => 'acknowledged',
                    'order_data' => $encodeOrderData,
                    'failed_order_reason' => "",
                    'account_id' => $accountId
                ];
                $trademeModel = $this->_objectManager->create('Ced\Onbuy\Model\Orders')->loadByField('onbuy_order_id', $result['order_id']);
                if ($trademeModel) {
                    $trademeModel->addData($orderData)->save();
                } else {
                    $this->_objectManager->create('Ced\Onbuy\Model\Orders')->addData($orderData)->save();
                }
                $this->sendMail($result['order_id'], $order->getIncrementId(), $order_place);

                $order = $this->_objectManager->create('\Magento\Sales\Model\Order')->load($order->getId());
                $orderstatus = $this->scopeConfig->getValue('payment/paybyonbuy/order_status');
                if($orderstatus=='canceled'){
              
                    $order->cancel();  
                   
                }else{
                $this->generateInvoice($order,$orderstatus);
                }
               
                // $this->generateShipment($order);
                $order->setStatus('processing');
                $order->setState('processing');
                $order->save();
            } else {
               
                $this->rejectOrder($result, "No Product found for Order");
            }
        } catch (\Exception $e) {
           
            $this->rejectOrder($result, $e->getMessage());
            $this->logger->addError('In Generate Quote: '.$e->getMessage(), ['path' => __METHOD__]);
        } catch (\Error $e) {
            $this->rejectOrder($result, $e->getMessage());
            $this->logger->addError('In Generate Quote: '.$e->getMessage(), ['path' => __METHOD__]);
        }
        return $count;
    }

    public function rejectOrder($orderResponseData, $message) {
        $encodeOrderData = $this->_jdecode->jsonEncode($orderResponseData);
        if ($this->_coreRegistry->registry('onbuy_account'))
            $account = $this->_coreRegistry->registry('onbuy_account');
        $accountId = isset($account) ? $account->getId() : '';
        $orderData = [
            'onbuy_order_id' => $orderResponseData['order_id'],
            'order_place_date' => date("Y-m-d"),
            'magento_increment_id' => '',
            'status' => 'failed',
            'order_data' => $encodeOrderData,
            'failed_order_reason' => $message,
            'account_id' => $accountId
        ];
        $trademeModel = $this->_objectManager->create('Ced\Onbuy\Model\Orders')->loadByField('onbuy_order_id', $orderResponseData['order_id']);
        if ($trademeModel) {
            $trademeModel->addData($orderData)->save();
        } else {
            $this->_objectManager->create('Ced\Onbuy\Model\Orders')->addData($orderData)->save();
        }
        $mageId = null;
        $placeDate = null;

    }

    public function sendMail($trademeOrderId, $mageOrderId = null, $placeDate = null)
    {
        try {
            $body = '<table cellpadding="0" cellspacing="0" border="0">
                <tr> <td> <table cellpadding="0" cellspacing="0" border="0">
                    <tr> <td class="email-heading">
                        <h1>You have a new order from OnBuy.</h1>
                        <p> Please review your admin panel."</p>
                    </td> </tr>
                </table> </td> </tr>
                <tr> 
                    <td>
                        <h4>Merchant Order Id' . $trademeOrderId . '</h4>
                    </td>
                    <td>
                        <h4>Magneto Order Id' . $mageOrderId . '</h4>
                    </td>
                    <td>
                        <h4>Order Place Date' . $placeDate . '</h4>
                    </td>
                </tr>  
            </table>';
            $to_email = $this->scopeConfig->getValue('onbuy_config/order/order_notify_email');
           
            $to_name = 'OnBuy Seller';
            $subject = 'Imp: New OnBuy Order Imported';
            $senderEmail = 'onbuyadmin@cedcommerce.com';
            $senderName = 'Onbuy';
            $headers = "MIME-Version: 1.0" . "\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
            $headers .= 'From: ' . $senderEmail . '' . "\r\n";
            mail($to_email, $subject, $body, $headers);
   
            return true;
        } catch (\Exception $e) {
            $this->logger->addError('In Send E-Mail: '.$e->getMessage(), ['path' => __METHOD__]);
        }
    }

    public function generateInvoice($order,$orderstatus)
    {
        
        try {
           
            $invoice = $this->_objectManager->create(
                'Magento\Sales\Model\Service\InvoiceService')->prepareInvoice(
                $order);
            $invoice->register();
            $invoice->save();
            
            $transactionSave = $this->_objectManager->create(
                'Magento\Framework\DB\Transaction')->addObject(
                $invoice)->addObject($invoice->getOrder());
            $transactionSave->save();
          
            
            $order->addStatusHistoryComment(__(
                'Notified customer about invoice #%1.'
                , $invoice->getId()))->setIsCustomerNotified(true)->save();
            $order->setStatus($orderstatus)->save();
           
            
        } catch (\Exception $e) {
            $this->logger->addError('In Generate Invoice: '.$e->getMessage(), ['path' => __METHOD__]);
        }
    }

    //shipment
    // public function generateShipment($order)
    // {
       
    //     $orders = $this->_orderRepository->get($order['entity_id']);

    //     if (!$orders->canShip()) {
    //         throw new \Magento\Framework\Exception\LocalizedException(
    //         __("You can't create the Shipment of this order."));
    //     }
 
    //     $orderShipment = $this->_convertOrder->toShipment($orders);
 
    //     foreach ($orders->getAllItems() AS $orderItem) {
 
    //      if (!$orderItem->getQtyToShip() || $orderItem->getIsVirtual()) {
    //         continue;
    //      }
 
    //      $qty = $orderItem->getQtyToShip();
    //      $shipmentItem = $this->_convertOrder->itemToShipmentItem($orderItem)->setQty($qty);
 
    //      $orderShipment->addItem($shipmentItem);
    //     }

    //     $orderShipment->register();
    //     $orderShipment->getOrder()->setIsInProcess(true);
    //     try {
    //         $orderShipment->save();
    //         $orderShipment->getOrder()->save();
              
    //     } catch (\Exception $e) {
    //         throw new \Magento\Framework\Exception\LocalizedException(
    //         __($e->getMessage())
    //         );
    //     }

    
    // }
    //shipment
    public function notificationSuccess($count)
    {
        $model = $this->_objectManager->create('\Magento\AdminNotification\Model\Inbox');
        $date = date("Y-m-d H:i:s");
        $model->setData('severity', 4);
        $model->setData('date_added', $date);
        $model->setData('title', "New OnBuy Orders");
        $model->setData('description', "Congratulation !! You have received " . $count . " new orders for OnBuy");
        $model->setData('url', "#");
        $model->setData('is_read', 0);
        $model->setData('is_remove', 0);
        $model->save();
        return true;
    }
    /**
     * Set the total shipping in registry
     * @param float $amount
     */
    private function registerShippingMethod($shippingService)
    {
        $this->_coreRegistry->unregister(\Ced\Onbuy\Model\Carrier\Shipbyonbuy::REGISTRY_INDEX_SHIPPING_METHOD);

        $this->_coreRegistry->register(
            \Ced\Onbuy\Model\Carrier\Shipbyonbuy::REGISTRY_INDEX_SHIPPING_METHOD,
            $shippingService
        );
    }

    /**
     * Set the total shipping price in registry
     * @param float $amount
     */
    private function registerShippingAmount($shippingPrice)
    {
        $this->_coreRegistry->unregister(\Ced\Onbuy\Model\Carrier\Shipbyonbuy::REGISTRY_INDEX_SHIPPING_TOTAL);

        $this->_coreRegistry->register(
            \Ced\Onbuy\Model\Carrier\Shipbyonbuy::REGISTRY_INDEX_SHIPPING_TOTAL,
            $shippingPrice
        );
    }

}