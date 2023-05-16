<?php


namespace Ced\Onbuy\Helper;

use Ced\Onbuy\Model\ProfileFactory;
use Mage;
use Mage_Catalog_Model_Product;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Model\ResourceModel\Stock\Item;
use Magento\Store\Model\StoreManagerInterface;
use Ced\Onbuy\Helper\Logger;
//use Magento\InventorySalesAdminUi\Model\GetSalableQuantityDataBySku;


class Onbuy extends \Magento\Framework\App\Helper\AbstractHelper
{
    const API_URL_SANDBOX = "https://api.onbuy.com/v2/";
    const API_URL = "https://api.onbuy.com/v2/";


    protected $scopeConfig;
    public $apiMode;
    public $apiUrl;

    public $fileIo;
    public $_allowedFeedType = array();

    /**
     * @var mixed
     */
    public $permissions;

    /**
     * @var mixed
     */
    public $oauthCallback;

    /**
     * @var mixed
     */
    public $oauthConsumerKey;

    /**
     * @var mixed
     */
    public $oauthConsumerSecret;

    /**
     * @var mixed
     */
    public $oauthToken;

    /**
     * @var mixed
     */
    public $oauthTokenSecret;

    /**
     * @var string
     */
    public $requestTokenUrl;

    /**
     * @var string
     */
    public $authoriseTokenUrl;

    /**
     * @var string
     */
    public $accessTokenUrl;

    /**
     * @var array
     */
    public $authParams;
    public $directoryList;
    public $json;
    public $adminSession;
    public $multiAccountHelper;
    public $_coreRegistry;
    public $profileproductsFactory;
    public $objectManager;
    public $data;
    /**
     * @var mixed
     */
    public $paymentMethods;

    /**
     * @var mixed
     */
    public $shippingType;

    /**
     * @var mixed
     */
    public $shippingPrice;

    /**
     * @var mixed
     */
    public $shippingMethod;

    /**
     * @var
     */
    public $priceType;
    public $logger;

    /**
     * @var bool
     */
    public $trademeQty;

    public $messageManager;
    protected $_storeManager;
    public $profileFactory;
    public $entityAttribute;
    public $productRepo;
    public $brandexcep;
    public $onbuyconfigimage;
    public $onbuyconfigattrfromparent;
    //private $getSalableQuantityDataBySku;
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Json\Helper\Data $json,
        \Magento\Backend\Model\Session $session,
        \Ced\Onbuy\Model\ProfileFactory $profileFactory,
        \Ced\Onbuy\Model\ResourceModel\Profileproducts\CollectionFactory $profileproductsFactory,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Message\Manager $manager,
        \Ced\Onbuy\Helper\MultiAccount $multiAccountHelper,
        \Ced\Onbuy\Helper\Data $data,
        \Magento\Framework\Registry $registry,
        StoreManagerInterface $_storeManager,
        \Magento\Catalog\Model\ResourceModel\Eav\Attribute $entityAttribute,
        \Magento\Catalog\Model\Product\Attribute\Repository $productRepo,
        Logger $logger,
        DirectoryList $directoryList
        //GetSalableQuantityDataBySku $getSalableQuantityDataBySku
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->directoryList = $directoryList;
        $this->json = $json;
        $this->adminSession = $session;
        $this->multiAccountHelper = $multiAccountHelper;
        $this->_coreRegistry = $registry;
        $this->_storeManager = $_storeManager;
        $this->profileFactory = $profileFactory;
        $this->logger = $logger;
        $this->profileproductsFactory = $profileproductsFactory;
        $this->messageManager = $manager;
        $this->objectManager = $objectManager;
        $this->entityAttribute = $entityAttribute;
        $this->productRepo = $productRepo;
        $this->data = $data;
        $this->fileIo = new \Magento\Framework\Filesystem\Io\File();
        $this->apiMode = $this->scopeConfig->getValue('onbuy_config/account_setting/mode');
        $this->permissions = $this->scopeConfig->getValue('onbuy_config/account_setting/permissions');
        $this->oauthCallback = $this->scopeConfig->getValue('onbuy_config/account_setting/oauth_callback');
        $this->oauthConsumerKey = $this->scopeConfig->getValue('onbuy_config/account_setting/oauth_consumer_key');
        $this->oauthConsumerSecret = $this->scopeConfig->getValue('onbuy_config/account_setting/oauth_consumer_secret');
        $this->oauthToken = $this->scopeConfig->getValue('onbuy_config/account_setting/oauth_token');
        $this->oauthTokenSecret = $this->scopeConfig->getValue('onbuy_config/account_setting/oauth_token_secret');
        $this->apiUrl = $this->apiMode == 'sandbox' ? self::API_URL_SANDBOX : self::API_URL;
        $this->paymentMethods = $this->scopeConfig->getValue('onbuy_config/product_upload/payment_methods');
        $this->shippingType = $this->scopeConfig->getValue('onbuy_config/product_upload/shipping_type');
        $this->shippingPrice = $this->scopeConfig->getValue('onbuy_config/product_upload/shipping_price');
        $this->shippingMethod = $this->scopeConfig->getValue('onbuy_config/product_upload/shipping_method');
        $this->priceType = $this->scopeConfig->getValue('onbuy_config/product_upload/price_type');
        $this->trademeQty = $this->scopeConfig->getValue('onbuy_config/product_upload/trademe_qty');
        $this->brandexcep = $this->scopeConfig->getValue('onbuy_config/product_upload/brand_excep');
        $this->onbuyconfigimage = $this->scopeConfig->getValue('onbuy_config/product_upload/onbuy_config_image');
        $this->onbuyconfigattrfromparent = $this->scopeConfig->getValue('onbuy_config/product_upload/onbuy_config_attr_from_parent');
        //$this->getSalableQuantityDataBySku = $getSalableQuantityDataBySku;
    }

    public function prepareData($product, $pProduct = null)
    {
        // print_r( $this->onbuyconfigattrfromparent);
        // die(__FILE__);
        try {
            $data = array();
            $error = array();

            $account = $this->_coreRegistry->registry('onbuy_account');
            $profileId = $this->getAssignedProfileId($product->getEntityId(), $account->getId());

            $profile = $this->profileFactory->create()->load($profileId);
            if ($product->getTypeId() == 'simple') {
                return $this->createSimpleProductData($product, $profile, null, $pProduct);
            } elseif ($product->getTypeId() == 'configurable') {

                $data = $this->createSimpleProductData($product, $profile, null, $pProduct);

                if (isset($data['error'])) {
                    return $data;
                }
                $variants = $this->prepareConfigData($product, $profile, $pProduct);
                if (isset($variants['error'])) {
                    $error[] = $variants['error'];
                }

                $finalArray = array_merge($data, $variants);
                if (isset($variants['error'])) {
                    $finalArray['error'] = $variants['error'];
                }
            //    echo '<pre>';
            //    print_r($finalArray);
            //    die(__FILE__);
                return $finalArray;
            }
            // prepare required data

        } catch (\Exception $e) {
            $response['error'] = $e->getMessage();

            return $response;
        }
    }

    public function createSimpleProductData($product, $profile, $childProd = null, $pProduct = null,$test= null)
    {
        try {
            if ($childProd != 'child') {
                $data['category_id'] = $profile->getProfileCategory();
                $data['published'] = 1;
            }
            $data['uid'] = $product->getSku();
            if ($pProduct && !empty($pProduct->getOpc())) {
                $data['opc'] = $pProduct->getOpc();
            }
            // prepare required and optional attribute data
            $reqOptAttributes = $this->getReqOptAttributes($product, $profile, $childProd);
            if (isset($reqOptAttributes["summary_points1"]) && !empty($reqOptAttributes["summary_points1"])) {
                //print_r($reqOptAttributes["summary_points1"]); die(__DIR__);
                $reqOptAttributes['summary_points'][] = strip_tags($reqOptAttributes["summary_points1"]);
                unset($reqOptAttributes["summary_points1"]);
            }
            if (isset($reqOptAttributes["summary_points2"]) && !empty($reqOptAttributes["summary_points2"])) {
                $reqOptAttributes['summary_points'][] = $reqOptAttributes["summary_points2"];
                unset($reqOptAttributes["summary_points2"]);
            }
            if (isset($reqOptAttributes["summary_points3"]) && !empty($reqOptAttributes["summary_points3"])) {
                $reqOptAttributes['summary_points'][] = $reqOptAttributes["summary_points3"];
                unset($reqOptAttributes["summary_points3"]);
            }
            if (isset($reqOptAttributes["summary_points4"]) && !empty($reqOptAttributes["summary_points4"])) {
                $reqOptAttributes['summary_points'][] = $reqOptAttributes["summary_points4"];
                unset($reqOptAttributes["summary_points4"]);
            }
            if (isset($reqOptAttributes["summary_points5"]) && !empty($reqOptAttributes["summary_points5"])) {
                $reqOptAttributes['summary_points'][] = $reqOptAttributes["summary_points5"];
                unset($reqOptAttributes["summary_points5"]);
            }
            /*if (isset($reqOptAttributes['stock']['is_in_stock']) && $reqOptAttributes['stock']['is_in_stock'] == 0) {
                $error[] = $product->getSku() . " is out of stock";
            }*/
            $price = $this->getTrademePrice($product, $profile);
            if (isset($price['error']))
                $error[] = $product->getSku() . " : Map price in profile .";
            $qty = 0;
            
       // $msi_status = $this->scopeConfig->getValue('onbuy_config/product_upload/onbuy_config_msi');

            // if($msi_status = '1'){

            // $stockRegistry = $this->objectManager->get( 'Magento\CatalogInventory\Api\StockRegistryInterface' );
            // $salable = $this->getSalableQuantityDataBySku->execute($product->getSku());
            // //multi store and multi account created start
            // $account = $this->_coreRegistry->registry('onbuy_account');
            // //$sellable_qty = $salable[$account->getId() - 1]['qty'];
            // $sellable_qty =  isset($salable[$account->getId() - 1]['qty']) ? $salable[$account->getId() - 1]['qty'] : 0;
            // //multi store and multi account created end
            // //if single account then just uncomment below line 
         
            // //$sellable_qty = isset($salable[0]['qty']) ? $salable[0]['qty'] : 0;

            //     $qty=$sellable_qty;

            // }else{

            if (isset($reqOptAttributes['stock']['qty']))
                $qty = $reqOptAttributes['stock']['qty'];
            elseif (isset($reqOptAttributes['stock']))
                $qty = $reqOptAttributes['stock'];

            //}


            $catDependentAttributes = [];
            if ($product->getTypeId() == 'simple') {
                $condition=$product->getData('onbuy_product_condition');
                $c= isset($condition) ? $condition :'new';
               
                $data['listings'] = [
                   $c=> [
                        'sku' => $reqOptAttributes['sku'],
                        'price' => isset($price['price']) ? (string)$price['price'] : 0,
                        'stock' => $qty,
                        'handling_time' => isset($reqOptAttributes['listings'][$c]['handling_time']) ? $reqOptAttributes['listings'][$c]['handling_time'] : 1,
                        'delivery_weight' => isset($reqOptAttributes['listings'][$c]['delivery_weight']) ? $reqOptAttributes['listings'][$c]['delivery_weight'] : " ",
                        'return_time' => isset($reqOptAttributes['listings'][$c]['return_time']) ? $reqOptAttributes['listings'][$c]['return_time'] : " ",
                        'free_returns' => isset($reqOptAttributes['listings'][$c]['free_returns']) ? $reqOptAttributes['listings'][$c]['free_returns'] : " ",
                        'warranty' => isset($reqOptAttributes['listings'][$c]['warranty']) ? $reqOptAttributes['listings'][$c]['warranty'] : " ",
                        'boost_marketing_commission' => isset($reqOptAttributes['listings'][$c]['boost_marketing_commission']) ? $reqOptAttributes['listings'][$c]['boost_marketing_commission'] : " ",
                    ]
                ];
               
                if (empty($reqOptAttributes['listings'][$c]['return_time'])) {
                    unset($data['listings'][$c]['return_time']);
                }
                if (empty($reqOptAttributes['listings'][$c]['free_returns'])) {
                    unset($data['listings'][$c]['free_returns']);
                }
                if (empty($reqOptAttributes['listings'][$c]['warranty'])) {
                    unset($data['listings'][$c]['warranty']);
                }
                if (empty($reqOptAttributes['listings'][$c]['delivery_weight'])) {
                    unset($data['listings'][$c]['delivery_weight']);
                }

                if (empty($reqOptAttributes['listings'][$c]['boost_marketing_commission'])) {
                    unset($data['listings'][$c]['boost_marketing_commission']);
                }
                unset($reqOptAttributes['listings'][$c]['handling_time']);
                unset($reqOptAttributes['listings'][$c]['return_time']);
                unset($reqOptAttributes['listings'][$c]['delivery_weight']);
                unset($reqOptAttributes['listings'][$c]['free_returns']);
                unset($reqOptAttributes['listings'][$c]['warranty']);
                unset($reqOptAttributes['listings'][$c]['boost_marketing_commission']);

                $catDependentAttributes = $this->getCatDependentAttributes($product, $profile);
                
                if (isset($catDependentAttributes['error'])) {
                    $error[] = $catDependentAttributes['error'];
                    unset($catDependentAttributes['error']);
                }
                if (isset($catDependentAttributes['notice'])) {
                    $this->messageManager->addSuccess($catDependentAttributes['notice']);
                    unset($catDependentAttributes['notice']);
                }
            }

            unset($reqOptAttributes['stock']);
            unset($reqOptAttributes['sku']);
            unset($reqOptAttributes['price']);

            if ($childProd == 'child') {
      
         //    if($msi_status = '1'){

         //    $stockRegistry = $this->objectManager->get( 'Magento\CatalogInventory\Api\StockRegistryInterface' );
         //    $salable = $this->getSalableQuantityDataBySku->execute($product->getSku());
         //    //multi store and multi account created start
         //    $account = $this->_coreRegistry->registry('onbuy_account');
         //   // $sellable_qty = $salable[$account->getId() - 1]['qty'];
         //    //multi store and multi account created end
         //    //if single account then just uncomment below line 
         //    //$sellable_qty = $salable[0]['qty'];
         // $sellable_qty = isset($salable[0]['qty']) ? $salable[0]['qty'] : 0;

         //    $qty=$sellable_qty;
                
         //    }else{

            if (isset($reqOptAttributes['stock']['qty']))
                $qty = $reqOptAttributes['stock']['qty'];
            elseif (isset($reqOptAttributes['stock']))
                $qty = $reqOptAttributes['stock'];

            //}

               
                $parentId = $this->objectManager->create('Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable')->getParentIdsByChild($product->getEntityId());
                if(is_array($parentId)){
                    $parent = $this->objectManager->create('Magento\Catalog\Model\Product')->load($parentId[0]);

                }else{
                $parent = $this->objectManager->create('Magento\Catalog\Model\Product')->load($parentId);
                }              
                $condition=$parent->getData('onbuy_product_condition');
                $c= isset($condition) ? $condition : $c;
                $data['listings'][$c]['group_sku'] = $parent->getSku();
                $data['listings'][$c]['stock'] = $qty;
                unset($reqOptAttributes['brand_name']);
            }

            if (isset($reqOptAttributes['error'])) {
                $error[] = $reqOptAttributes['error'];
                unset($reqOptAttributes['error']);
            }
            if (isset($reqOptAttributes['notice'])) {
                $this->messageManager->addSuccess($reqOptAttributes['notice']);
                unset($reqOptAttributes['notice']);
            }
            $data = array_merge_recursive($data, $reqOptAttributes);
            //todo remove fake response
        


            $data['default_image'] = $this->imageData($product, 'default');
           
            if ($this->imageData($product, 'other')) {
             
                $data['additional_images'] = $this->imageData($product, 'other');
            }

            if ($this->onbuyconfigimage == '1' && $childProd == 'child') {

                $data['default_image'] = $this->imageData($parent, 'default');
            }

            // prepare category dependent attribute data
            $data = array_merge_recursive($data, $catDependentAttributes);
            //for sync product on trademe
     // print_r($data);
     // die(__FILE__);
            // for config product data prepare
            if (!empty($error)) {
                $data['error'] = implode(',', $error);
            }
            if($test=='update' && $childProd='child'){
                unset($data['product_name']);
             }
            return $data;
        } catch (\Exception $e) {
            $data['error'] = $e->getMessage();
            return $data;
        }
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @param $type
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function imageData($product, $type)
    {

        $baseImage = $product->getImage();
        $media = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);

         $images = $product->getMediaGalleryImages();
      
        if ($type == 'default') {
            return $media . 'catalog/product' . $baseImage;
        } else {
            
            $imgData = [];
            $images = $product->getMediaGalleryImages();
           
         
            if ($images->getSize() == 1)
                return false;

            foreach ($images as $key => $image) {
               
                if ($image->getData('file') != $baseImage) {

                    $url = $media . 'catalog/product' . $image->getData('file');
                    $imgData[] = /*$imgData . ', ' . */
                        $url;
                }
            }
            return /*json_encode*/ ($imgData)/*substr($imgData, 2)*/;
        }
    }

    public function getReqOptAttributes($product, $profile, $childProd = null)
    {

        try {
            $data = array();
            $error = array();
            $notice = array();
            $values = '';
            $reqOptAttributes = json_decode($profile->getOptReqAttribute(), true);
            //code added
            $parentId = '';
            $parent = '';

            if ($childProd == 'child') {

                $parentId = $this->objectManager->create('Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable')->getParentIdsByChild($product->getEntityId());
                $parent = $this->objectManager->create('Magento\Catalog\Model\Product')->load($parentId);
            }
            $attr = $this->onbuyconfigattrfromparent;
            if (isset($attr)) {

                $att = explode(',', $attr);
            }

            //code added
            foreach ($reqOptAttributes['required_attributes'] as $key => $value) {
                if ($value/*['_value']*/['magento_attribute_code'] != 'default') {

                    if ($product->getData($value/*['_value']*/['magento_attribute_code']) == '') {
                        if ($product->getTypeId() == 'configurable' && $value['magento_attribute_code'] == 'price') {
                            $childProds = $product->getTypeInstance(true)->getUsedProducts($product);
                            $price = 0;
                            foreach ($childProds as $childProd) {
                                $prodPrice = $this->getTrademePrice($childProd, $profile);
                                if (isset($prodPrice['error'])) {
                                    $error[] = $value/*['_value']*/['magento_attribute_code'] . ' value cannot be empty';
                                    continue;
                                }
                                if ($price < $prodPrice['price'])
                                    $price = $prodPrice['price'];
                            }
                            $data[$value['onbuy_attribute_name']] = $price;
                            continue;
                        }

                        if ($product->getTypeId() == 'configurable' && $value['onbuy_attribute_name'] == 'product_codes') {
                            continue;
                        } elseif ($product->getTypeId() == 'configurable' && $value['onbuy_attribute_name'] == 'mpn') {
                            continue;
                        } elseif ($this->brandexcep) {
                            continue;
                        } else {
                           
                            $error[] = $value/*['_value']*/['magento_attribute_code'] . ' value cannot be empty';
                        }
                    } else {


                        switch ($value['onbuy_attribute_name']) {

                            case 'product_name':
                                if ($childProd == 'child' && $att[0] != '') {

                                    foreach ($att as $key) {
                                        if ($key == 'product_name') {

                                            $title = $parent->getData($value/*['_value']*/['magento_attribute_code']);
 
                                            $data[$value['onbuy_attribute_name']] = substr($title, 0, 150);
                                            break;
                                        } else {

                                            $title = $product->getData($value/*['_value']*/['magento_attribute_code']);
                                            $data[$value['onbuy_attribute_name']] = substr($title, 0, 150);
                                        }
                                    }
                                } else {
                                    $title = $product->getData($value/*['_value']*/['magento_attribute_code']);
                                    $data[$value['onbuy_attribute_name']] = substr($title, 0, 150);
                                }

                                continue 2;

                            case 'product_codes':
                                if ($product->getTypeId() == 'configurable')
                                    continue 2;
                                $productCodes = $product->getData($value/*['_value']*/['magento_attribute_code']);
                                if (!$this->brandexcep)
                                    $data[$value['onbuy_attribute_name']] = [$productCodes];
                                continue 2;

                            case 'mpn':
                                if ($product->getTypeId() == 'configurable')
                                    continue 2;
                                if (strlen($product->getData($value/*['_value']*/['magento_attribute_code'])) > 100) {
                                    $error[] = $value['onbuy_attribute_name'] . " cannot be more than 100 characters. ";
                                    continue 2;
                                }
                                $data[$value['onbuy_attribute_name']] = $product->getData($value/*['_value']*/['magento_attribute_code']);

                                continue 2;

                            case 'sku':
                                if (strlen($product->getData($value/*['_value']*/['magento_attribute_code'])) > 50) {
                                    $error[] = $value['onbuy_attribute_name'] . " cannot be more than 50 characters. ";
                                    continue 2;
                                }

                                $data[$value['onbuy_attribute_name']] = $product->getData($value/*['_value']*/['magento_attribute_code']);

                                continue 2;


                            case 'brand_name':
            $brandname = $product->getData($value/*['_value']*/['magento_attribute_code']);
                        $manufacturerName  = $product->getAttributeText('manufacturer');
            $brandname  = $product->getResource()->getAttribute('manufacturer')->setStoreId(0)->getFrontend()->getValue($product);
                        //print_r($brandname); die();
            $data[$value['onbuy_attribute_name']] = substr($brandname, 0, 200);
            continue 2;

                            default:
                                //--------------------------------------
                                if ($childProd == 'child' && $att[0] != '') {

                                    foreach ($att as $key) {
                                        if ($key == $value['onbuy_attribute_name']) {
                                            //$data[$value['onbuy_attribute_name']]="hdfsf";
                                            $data[$value['onbuy_attribute_name']] = $parent->getData($value/*['_value']*/['magento_attribute_code']);
                                        } else {
                                            $data[$value['onbuy_attribute_name']] = $product->getData($value/*['_value']*/['magento_attribute_code']);
                                        }
                                    }
                                } else {
                                    $data[$value['onbuy_attribute_name']] = $product->getData($value/*['_value']*/['magento_attribute_code']);
                                }


                                //--------------------------------------

                                //$data[$value['onbuy_attribute_name']] = $product->getData($value/*['_value']*/['magento_attribute_code']);
                                continue 2;
                        }
                    }
                } else if (isset($value/*['_value']*/['default']) && $value/*['_value']*/['default'] != '') {

                    if ($value['onbuy_attribute_name'] == 'description') {
                        $value = $this->getDescriptionTemplate($product, $value/*['_value']*/['default']);
                    } elseif ($value['onbuy_attribute_name'] == 'product_codes') {
                        
                        $values = [$value['default']];
                    } elseif ($value['onbuy_attribute_name'] == 'brand_name') {
                        $values = substr($value['default'], 0, 200);
                    } elseif ($value['onbuy_attribute_name'] == 'sku') {
                        if (strlen($value['default']) > 50) {
                            $error[] = $value['onbuy_attribute_name'] . " cannot be more than 50 characters. ";
                            continue;
                        }
                        $values = substr($value['default'], 0, 200);
                    } elseif ($value['onbuy_attribute_name'] == 'mpn') {
                        if (strlen($value['onbuy_attribute_name']) > 100) {
                            $error[] = $value['onbuy_attribute_name'] . " cannot be more than 100 characters. ";
                            continue;
                        }
                        $values = substr($value['default'], 0, 200);
                    } else {
                        $values = $value/*['_value']*/['default'];
                    }
                    //print_r($values); die(__DIR__);
                    $data[$value['onbuy_attribute_name']] = $values;
                } else {
                    $error[] = 'set the default value' . $value/*['_value']*/['magento_attribute_code'];
                }
            }
            foreach ($reqOptAttributes['optional_attributes'] as $key => $value) {
                if ($value/*['_value']*/['magento_attribute_code'] != 'default') {
                    if ($product->getData($value/*['_value']*/['magento_attribute_code']) == '') {
                        $notice[] = $value/*['_value']*/['magento_attribute_code'] . ' value cannot be empty';
                    } else {
                        $attrVal = $product->getData($value/*['_value']*/['magento_attribute_code']);
                        switch ($value['onbuy_attribute_name']) {
                            case 'summary_points':

                                $data[$value['onbuy_attribute_name']] = [$attrVal];
                                continue 2;

                            case 'Subtitle':
                                $data[$key] = substr($attrVal, 0, 50);
                                continue 2;

                            case 'handling_time':
                                $handlingTime = $product->getData($value/*['_value']*/['magento_attribute_code']);
                                $data['listings'][$c][$value['onbuy_attribute_name']] = $handlingTime;
                                continue 2;

                            case 'delivery_weight':
                                $deliveryWeight = $product->getData($value/*['_value']*/['magento_attribute_code']);
                                $data['listings'][$c][$value['onbuy_attribute_name']] = $deliveryWeight;
                                continue 2;

                            case 'mpn':
                                if ($product->getTypeId() == 'configurable')
                                    continue 2;
                                if (strlen($product->getData($value/*['_value']*/['magento_attribute_code'])) > 100) {
                                    $error[] = $value['onbuy_attribute_name'] . " cannot be more than 100 characters. ";
                                    continue 2;
                                }
                                $data[$value['onbuy_attribute_name']] = $product->getData($value/*['_value']*/['magento_attribute_code']);

                                continue 2;

                            case 'rrp':
                                if (is_numeric($attrVal)) {

                                    $data[$value['onbuy_attribute_name']] = $attrVal;
                                } else {
                                    $error[] = $value/*['_value']*/['onbuy_attribute_name'] . ' value cannot non numeric';
                                }
                                continue 2;
                                /*case 'mpn':
                                if ($product->getTypeId() == 'configurable')
                                    continue;
                                $data[$value['onbuy_attribute_name']] = $attrVal;

                                continue;*/

                            default:
                                $data[$value['onbuy_attribute_name']] = $attrVal;
                                continue 2;
                        }
                    }
                } else if (isset($value/*['_value']*/['default']) && $value/*['_value']*/['default'] != '') {

                    switch ($value['onbuy_attribute_name']) {
                        case 'rrp':
                            if (is_numeric($value/*['_value']*/['default'])) {

                                $data[$value['onbuy_attribute_name']] = $value/*['_value']*/['default'];
                            } else {
                                $error[] = $value/*['_value']*/['onbuy_attribute_name'] . ' value cannot non numeric';
                            }
                            continue 2;
                        case 'summary_points':
                            $data[$value['onbuy_attribute_name']] = [$value['default']];
                            continue 2;

                        case 'mpn':
                            if ($product->getTypeId() == 'configurable')
                                continue 2;
                            if (strlen($value['default']) > 100) {
                                $error[] = $value['onbuy_attribute_name'] . " cannot be more than 100 characters. ";
                                continue 2;
                            }
                            $data[$value['onbuy_attribute_name']] = $value['default'];


                            continue 2;


                        default:
                            $data[$value['onbuy_attribute_name']] = $value/*['_value']*/['default'];
                    }
                } else {
                    $notice[] = 'set the default value' . $value/*['_value']*/['magento_attribute_code'];
                }
            }

            if (!empty($error)) {
                $data['error'] = implode(',', $error);
            }

            if (!empty($notice)) {
                $data['notice'] = implode(',', $notice);
            }
        } catch (\Exception $e) {
            $data['error'] = $e->getMessage();
            $this->logger->addError($e->getMessage(), ['path' => __METHOD__]);
        }
        return $data;
    }

    public function getDescriptionTemplate($product, $value = null)
    {
        preg_match_all("/\##(.*?)\##/", $value, $matches);
        foreach (array_unique($matches[1]) as $attrId) {
            $attrValue = $product->getData($attrId);
            $value = str_replace('##' . $attrId . '##', $attrValue, $value);
        }
        $description = array(substr(strip_tags($value), 0, 50000));
        return $description;
    }

    public function getCatDependentAttributes($product, $profile)
    { 
        
        try {
            $data = array();
            $data1 = $data2 = [];
            $error = array();
            $notice = array();
            $techDetails = [];
            $catDependAttributes['required_attributes'] = array();
            $catDependAttributes['optional_attributes'] = array();
            $catDependAttributes = json_decode($profile->getCatDependAttribute(), true);
            if(!empty($catDependAttributes['required_attributes'])){
            foreach ($catDependAttributes['required_attributes'] as $key => $value) {
                $temp = array();
                if ($value/*['_value']*/['magento_attribute_code'] != 'default') {
                    if ($product->getData($value/*['_value']*/['magento_attribute_code']) == '') {
                        $error[] = $value/*['_value']*/['magento_attribute_code'] . ' value cannot be empty';
                    } else if (isset($value['option_mapping']) && !empty($value['option_mapping'])) {
                      
                        $optionArray = json_decode($value['option_mapping'], true);
                        
                        $optionVal = $product->getData($value['magento_attribute_code']);
                        //$optionVal = $product->getData($value['onbuy_attribute_name']);

                        $optionsArray = [];
                        $optionMapped = $optionArray[$optionVal];
                        // print_r($optionMapped);
                        // die(__FILE__);
                        $options = $this->productRepo->get($value['magento_attribute_code'])->getOptions();
                       
                        foreach ($options as $option) {
                            if ($optionVal == $option->getValue()) {
                                $optionsArray = $option->getLabel();
                            }
                        }
                        // print_r($ $optionsArray);
                        // die(__FILE__);
                        //$optionMapped = array_search($optionVal,$optionArray);
                        if ($optionMapped) {
                            $selectedOption = explode(":", $optionMapped);

                            $temp = [
                                'option_id' => $selectedOption[1],
                                'name' => $optionsArray
                            ];
                        }
                    } else {
                        $temp['option_id'] = $product->getData($value['magento_attribute_code']);
                    }
                } elseif (isset($value['default']) && $value['default'] != '') {
                    $temp['option_id'] = $value['default'];
                } else {
                    $error[] = 'set the default value' . $value['magento_attribute_code'];
                }
                $data1[] = !empty($temp) ? $temp : array();
            }
        }    
        if(!empty($catDependAttributes['required_attributes'])){
                foreach ($catDependAttributes['optional_attributes'] as $key => $value) {
                    $temp = array();
                    if ($value['magento_attribute_code'] != 'default') {
                        if ($product->getData($value['magento_attribute_code']) == '') {
                            $notice[] = $value['magento_attribute_code'] . ' value cannot be empty';
                        } else if (isset($value['option_mapping']) && !empty($value['option_mapping'])) {
                            $optionArray = json_decode($value['option_mapping'], true);
                            $optionVal = $product->getData($value['magento_attribute_code']);
                            $optionMapped = $optionArray[$optionVal];
                            //$optionMapped = array_search($optionVal,$optionArray);
                            // print_r($optionMapped);
                            // die(__FILE__);
                            if ($optionMapped) {
                                $selectedOption = explode(":", $optionMapped);
                                $temp['option_id'] = $selectedOption[1];
                            }
                        } else {

                            $details = explode("[Tech", $value['onbuy_attribute_name']);

                            if (isset($details[1])) {

                                $detailId = json_decode($value['options'], true);
                                // print_r($detailId);
                                // die(__FILE__);
                                $temp['value'] = $product->getData($value['magento_attribute_code']);
                                $tech = explode(":", $details[0]);
                                // print_r($tech);
                                //     die(__FILE__); 
                                if (isset($tech['0'])) {

                                    $temp['detail_id'] = trim($tech['1']);
                                }
                                if (isset($value['units'])) {

                                    $temp['unit'] = $value['units'];
                                }

                                $techDetails[] = $temp;
                                continue;
                            } else {

                                $temp['option_id'] = $product->getData($value['magento_attribute_code']);
                            }
                        }
                    } else if (isset($value['default']) && $value['default'] != '') {

                        $details = explode("[Tech", $value['onbuy_attribute_name']);

                        if (isset($details[1])) {
                            $detailId = json_decode($value['options'], true);
                            $temp['value'] = $value['default'];
                            $tech = explode(":", $details[0]);
                            if (isset($tech['1'])) {
                                $temp['detail_id'] = trim($tech['1']);
                            }
                            if (isset($value['units']))
                                $temp['unit'] = $value['units'];
                            $techDetails[] = $temp;
                            continue;
                        } else {
                            $temp['option_id'] = $value['default'];
                        }
                    } else {
                        $notice[] = 'set the default value' . $value/*['_value']*/['magento_attribute_code'];
                    }
                    $data2[] = !empty($temp) ? $temp : array();
                }
        }
            if (!empty($data1) || !empty($data2)) {
                $data['features'] = array_merge_recursive($data1, $data2);
            }
            if (!empty($error)) {
                $data['error'] = implode(',', $error);
            }
            if (!empty($techDetails)) {
                $data['technical_detail'] = $techDetails;
            }

            if (!empty($notice)) {
                $data['notice'] = implode(',', $notice);
            }
        } catch (\Exception $e) {
            $this->logger->addError('In Category Dependent Attribute: has exception ' . $e->getMessage(), ['path' => __METHOD__]);
        }
      
        return $data;
    }

    public function prepareConfigData($product, $profile, $pProduct)
    {
        try {
            /** @var  $product Mage_Catalog_Model_Product */
            $optionSets = array();
            $variants = array();
            $variantsAttr = array();
            $error = array();
            $varProducts = $product->getTypeInstance()->getUsedProducts($product);
            $configAttr = $product->getTypeInstance(true)->getConfigurableAttributesAsArray($product);
            $index = 1;

           
            foreach ($configAttr as $attr) {
                $values = array();
                $tempArray = array();
                $tempArray["variant_" . $index] = ['name' => $attr['label']];
                
                $variantsAttr[$attr['label']] = "variant_" . $index;
                $optionSets = array_merge($optionSets, $tempArray);
               
                $index++;
            }
            // print($tempArray);
            // die(__FILE__);
            $attrs = $product->getTypeInstance(true)->getConfigurableAttributes($product);
            $basePrice = $product->getFinalPrice();

            //$childOpcData = json_decode($pProduct->getChildOpc(),true);
            $childOpcData = $pProduct->getChildOpc();
            foreach ($varProducts as $varProduct) {
                // print_r($varProduct->getData());
                // die(__FILE__);
                $tempArray = [];
                $childProd = $this->objectManager->create('Magento\Catalog\Model\Product')
                    ->loadByAttribute('entity_id', $varProduct->getEntityId());
                    
    $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); 
    $productRepository = $objectManager->get('\Magento\Catalog\Model\ProductRepository');
    $productObj = $productRepository->get($varProduct->getSku());
    $tempArray = $this->createSimpleProductData($productObj, $profile, 'child');
               
                unset($tempArray['error']);
                if (isset($childOpcData[$childProd->getSku()]))
                    $tempArray['opc'] = $childOpcData[$childProd->getSku()];
                if (isset($tempArray['error'])) {
                    $error[] =  $childProd->getSku() . " : " . $tempArray['error'];
                }
                $index = 1;
                foreach ($attrs as $attr) {
                    $prices = $attr->getPrices();
                    if (!empty($prices)) {
                        foreach ($prices as $price) {
                            if ($price['is_percent']) { //if the price is specified in percents
                                $pricesByAttributeValues[$price['value_index']] = (float)$price['pricing_value'] * $basePrice / 100;
                            } else { //if the price is absolute value
                                $pricesByAttributeValues[$price['value_index']] = (float)$price['pricing_value'];
                            }
                        }
                    }
                    $value = $varProduct->getData($attr->getProductAttribute()->getAttributeCode());
                    $lable = $attr->getLabel();
                    $tempArray[$variantsAttr[$lable]] = ['name' => $varProduct->getAttributeText($attr->getProductAttribute()->getAttributeCode())];
                    $index++;
                }
                $variants[] = $tempArray;
            }

            $finalArray = $optionSets;
            $finalArray['variants'] = $variants;
            if (!empty($error)) {
                $finalArray['error'] = implode(',', $error);
            }
        } catch (\Exception $e) {
            $finalArray['error'] = $e->getMessage();
            $this->logger->addError($e->getMessage(), ['path' => __METHOD__]);
        }
        return $finalArray;
    }

    public function getTrademePrice($productObject, $profile)
    {
        try {
            $profilePrice = $this->getReqOptAttributes($productObject, $profile);
            if (!isset($profilePrice['price'])) {
                $error['error'] = $productObject->getSku() . " : Map price in profile .";
                return $error;
            }
            $price = (float)$profilePrice['price'];
            $price = $this->getConvertedPrice($price);
            $price = round($price, 4);
            switch ($this->priceType) {
                case 'plus_fixed':
                    $fixedPrice = trim(
                        $this->scopeConfig->getValue(
                            'onbuy_config/product_upload/fix_price'
                        )
                    );
                    $price = $this->forFixPrice($price, $fixedPrice, 'plus_fixed');
                    break;
                case 'plus_per':
                    $percentPrice = trim(
                        $this->scopeConfig->getValue(
                            'onbuy_config/product_upload/percentage_price'
                        )
                    );
                    $price = $this->forPerPrice($price, $percentPrice, 'plus_per');
                    break;
                case 'min_fixed':
                    $fixedPrice = trim(
                        $this->scopeConfig->getValue(
                            'onbuy_config/product_upload/fix_price_min'
                        )
                    );
                    $price = $this->forFixPrice($price, $fixedPrice, 'min_fixed');
                    break;
                case 'min_per':
                    $percentPrice = trim(
                        $this->scopeConfig->getValue(
                            'onbuy_config/product_upload/percentage_price_min'
                        )
                    );
                    $price = $this->forPerPrice($price, $percentPrice, 'min_per');
                    break;
                case 'differ':
                    $customPriceAttr = trim(
                        $this->scopeConfig->getValue(
                            'onbuy_config/product_upload/different_price'
                        )
                    );
                    try {
                        $product = $this->objectManager->create('Magento\Catalog\Model\Product')->load($productObject->getId());
                        $cprice = (float)$product->getData($customPriceAttr);
                    } catch (\Exception $e) {
                        $this->logger->addError($e->getMessage(), ['path' => __METHOD__]);
                    }

                    $price = (isset($cprice) && $cprice != 0) ? $cprice : $price;
                    $splprice = $price;
                    break;
                default:
                    return array(
                        'price' => (string)round($price, 4),
                    );
            }

            return array(
                'price' => (string)round($price, 4)
            );
        } catch (\Exception $e) {
            $this->logger->addError($e->getMessage(), ['path' => __METHOD__]);
            return false;
        }
    }

    public function getConvertedPrice($price)
    {
        $convertCurrency = trim(
            $this->scopeConfig->getvalue(
                'onbuy_config/product_upload/convert_curreny'
            )
        );
        if ($convertCurrency) {
            $this->_storeManager = $this->objectManager->get('Magento\Store\Model\StoreManagerInterface');
            $toCurrency = /*isset($countryDetails['currency'][0]) ? $countryDetails['currency'][0] : ''*/ 'EUR';
            $currency = $this->objectManager->get('Magento\Directory\Model\CurrencyFactory');
            $currencyRate = $currency->create()->load($this->_storeManager->getStore()->getBaseCurrency()->getCode())->getAnyRate($toCurrency);
            if ($currencyRate)
                $price = $this->_storeManager->getStore()->getBaseCurrency()->convert($price, $toCurrency);
        }

        return $price;
    }

    public function saveResponseOnProduct($resultData, $product)
    {
        $successids = $message = array();
        $message['error'] = "";
        $message['success'] = "";
        reset($resultData); // make sure array pointer is at first element
        $firstKey = key($resultData);
        $accountId = 0;
        $currentAccount = $this->_coreRegistry->registry('onbuy_account');
        if ($currentAccount) {
            $accountId = $currentAccount->getId();
        }
        $prodStatusAccAttr = $this->multiAccountHelper->getProdStatusAttrForAcc($accountId);
        $listingErrorAccAttr = $this->multiAccountHelper->getProdListingErrorAttrForAcc($accountId);
        $uploadTime = $this->multiAccountHelper->getUploadTimeAttrForAcc($accountId);

        if (isset($resultData['error'])) {
            $errors[] =  $resultData['error'];
            $ulLierrorResponse = $this->convertUlLi($errors);
            $listingError = $this->preapareResponse($product->getEntityId(), $firstKey, $product->getSku(), $errors);
            $product->setData($listingErrorAccAttr, $listingError);
            $product->getResource()/*->saveAttribute($product, $prodStatusAccAttr)*/->saveAttribute($product, $listingErrorAccAttr);

            $product->setData($prodStatusAccAttr, 'not_uploaded');
            $product->setData($listingErrorAccAttr, $resultData['error'] /*json_encode*//*(["valid"])*/);
            $product->getResource()->saveAttribute($product, $prodStatusAccAttr)->saveAttribute($product, $listingErrorAccAttr);
        } elseif (isset($resultData['ErrorDescription'])) {

            $errors[] =  $resultData['ErrorDescription'];
            $ulLierrorResponse = $this->convertUlLi($errors);
            $listingError = $this->preapareResponse($product->getEntityId(), $firstKey, $product->getSku(), $errors);
            $product->setData($listingErrorAccAttr, $listingError);
            $product->getResource()/*->saveAttribute($product, $prodStatusAccAttr)*/->saveAttribute($product, $listingErrorAccAttr);
        } elseif (isset($resultData['Success']) && isset($resultData['ListingId'])) {
            $listingId = $this->multiAccountHelper->getProdListingIdAttrForAcc($accountId);
            if ($product->getTypeId() == 'configurable') {
                foreach ($resultData['Variants'] as $variant) {
                    $childProd = $this->objectManager->create('Magento\Catalog\Model\Product')->loadByAttribute('sku', $variant['SKU']);
                    $childProd->setData($listingId, $variant['ListingId']);
                    $childProd->getResource()->saveAttribute($childProd, $listingId);
                }
            }

            $product->setData($prodStatusAccAttr, 'uploaded');
            $product->setData($uploadTime, date("Y-m-d"));
            $product->setData($listingErrorAccAttr, json_encode(["valid"]));
            $product->setData($listingId, $resultData['ListingId']);
            $product->getResource()->saveAttribute($product, $prodStatusAccAttr)->saveAttribute($product, $listingErrorAccAttr)->saveAttribute($product, $listingId)->saveAttribute($product, $uploadTime);
            $successids[] = $product->getSku();
        } elseif (isset($resultData['success']) /*&& isset($resultData['PhotoId'])*/) {
            $trademePhotoId = $this->multiAccountHelper->getProdPhotoIdAttrForAcc($accountId);
            $product->setData($trademePhotoId, implode(',', $resultData['success']));
            $product->getResource()->saveAttribute($product, $trademePhotoId);
        } elseif (isset($resultData['Success']) && isset($resultData['Description'])) {
            $errors[] =  $resultData['Description'];
            $ulLierrorResponse = $this->convertUlLi($errors);
            $listingError = $this->preapareResponse($product->getEntityId(), $firstKey, $product->getSku(), $errors);
            $product->setData($prodStatusAccAttr, 'not_uploaded');
            $product->setData($listingErrorAccAttr, $listingError);
            $product->getResource()->saveAttribute($product, $prodStatusAccAttr)->saveAttribute($product, $listingErrorAccAttr);
        }
    }

    public function convertUlLi($errors)
    {
        $errorMsg = '';
        $errorMsg .= "<br><ul class='all-validation-errors'>";
        foreach ($errors as $error) {
            $errorMsg .= "<li>" . $error . "</li>";
        }
        $errorMsg .= "</ul>";
        return $errorMsg;
    }
    public function preapareResponse($errors, $sku, $variable , $id = null)
    {
        if (is_array($errors)) {
            $errors = json_encode($errors);
        }
        $response = [];
        $response[$variable] =
            [
                "id" => $id,
                "sku" => $sku,
                "url" => "#",
                'errors' => $errors
            ];

        return json_encode($response);
    }

    public function forFixPrice($configPrice ,$fixedPrice = null, $price = null)
    {
        if (is_numeric($fixedPrice) && ($fixedPrice != '')) {
            $fixedPrice = (float)$fixedPrice;
            if ($fixedPrice > 0) {
                $price = $configPrice == 'plus_fixed' ? (float)($price + $fixedPrice)
                    : (float)($price - $fixedPrice);
            }
        }
        return $price;
    }

    /**
     * @param null $price
     * @param null $percentPrice
     * @param $configPrice
     * @return float|null
     */
    public function forPerPrice( $configPrice, $percentPrice = null,$price = null)
    {
        if (is_numeric($percentPrice)) {
            $percentPrice = (float)$percentPrice;
            if ($percentPrice > 0) {
                $price = $configPrice == 'plus_per' ?
                    (float)($price + (($price / 100) * $percentPrice))
                    : (float)($price - (($price / 100) * $percentPrice));
            }
        }
        return $price;
    }

    public function getAssignedProfileId($productId, $accountId)
    {
        $profileId = $this->profileproductsFactory->create()->addFieldToFilter('account_id', $accountId)
            ->addFieldToFilter('product_id', $productId)->getFirstItem()->getProfileId();
            if(!$profileId){
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    $product = $objectManager->create('Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable')->getParentIdsByChild($productId);
    $profileId = $this->profileproductsFactory->create()->addFieldToFilter('account_id', $accountId)
    ->addFieldToFilter('product_id', $productId[0])->getFirstItem()->getProfileId();  
}
        return $profileId;
    }

    public function getMagentoProductAttributeValue($product, $attributeCode)
    {
        if ($product->getData($attributeCode) == "") {
            return $product->getData($attributeCode);
        }

        $attr = $product->getResource()->getAttribute($attributeCode);
        if ($attr && ($attr->usesSource() || $attr->getData('frontend_input') == 'select')) {
            $productAttributeValue = $attr->getSource()->getOptionText($product->getData($attributeCode));
        } else {
            $productAttributeValue = $product->getData($attributeCode);
        }
        return $productAttributeValue;
    }
}
