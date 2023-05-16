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

namespace Ced\Onbuy\Controller\Adminhtml\Product;

use Ced\Onbuy\Helper\Onbuy;
use Ced\Onbuy\Model\Source\Productstatus;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\JsonFactory;
use Ced\Onbuy\Helper\Data;
use Ced\Onbuy\Helper\Logger;

/**
 * Class Startupload
 * @package Ced\Onbuy\Controller\Adminhtml\Product
 */
class Startcheckopc extends Action
{
    /**
     * @var PageFactory
     */
    public $resultPageFactory;
    /**
     * @var JsonFactory
     */
    public $resultJsonFactory;

    /**
     * @var Data
     */
    public $dataHelper;
    /**
     * @var Logger
     */
    public $logger;

    /**
     * @var \Ced\Onbuy\Helper\MultiAccount
     */
    protected $multiAccountHelper;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;
    protected $objectManager;
    protected $profileFactory;
    public $accountsFactory;
    public $productstatus;
    public $onbuy;
    public $feeds;
    public $profileProducts;
    /**
     * Startupload constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param JsonFactory $resultJsonFactory
     * @param Data $dataHelper
     * @param Logger $logger
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        JsonFactory $resultJsonFactory,
        Data $dataHelper,
        Logger $logger,
        \Ced\Onbuy\Helper\Onbuy $onbuy,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Ced\Onbuy\Helper\MultiAccount $multiAccountHelper,
        \Ced\Onbuy\Model\ResourceModel\Profileproducts\CollectionFactory $profileProducts,
        \Ced\Onbuy\Model\ResourceModel\Feeds\CollectionFactory $feeds,
        \Ced\Onbuy\Model\Source\Productstatus $productstatus,
        \Ced\Onbuy\Model\AccountsFactory $accountsFactory,
        \Ced\Onbuy\Model\ProfileFactory $profileFactory

    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->dataHelper = $dataHelper;
        $this->logger = $logger;
        $this->onbuy = $onbuy;
        $this->feeds = $feeds;
        $this->productstatus = $productstatus;
        $this->profileProducts = $profileProducts;
        $this->_coreRegistry = $coreRegistry;
        $this->objectManager = $objectManager;
        $this->multiAccountHelper = $multiAccountHelper;
        $this->profileFactory = $profileFactory;
        $this->accountsFactory = $accountsFactory;
    }

  
    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();
        $message = [];
        $message['error'] = "";
        $message['success'] = "";
        $requestData = [];
        $key = $this->getRequest()->getParam('index');
        $totalChunk = $this->_session->getUploadChunks();
        $index = $key + 1;
        if (count($totalChunk) <= $index) {
            $this->_session->unsUploadChunks();
        }
        try {
            if (isset($totalChunk[$key])) {
                $ids = $totalChunk[$key];
                foreach ($ids as $accountId => $prodIds) {
                    $prodDetails = [];
                    if (!is_array($prodIds)) {
                        $prodIds[] = $prodIds;
                    }
                    if ($this->_coreRegistry->registry('onbuy_account'))
                        $this->_coreRegistry->unregister('onbuy_account');
                    $this->multiAccountHelper->getAccountRegistry($accountId);
                    $this->dataHelper->updateAccountVariable();

                    foreach ($prodIds as $id) {
                        $product = $this->objectManager->create('Magento\Catalog\Model\Product')->load($id);

                            if($product->getTypeId() == 'simple')
                            {
                                $profileId = $this->onbuy->getAssignedProfileId($product->getEntityId(), $accountId);
                                /*$pProduct = $this->profileProducts->create()->addFieldToFilter('account_id', $accountId)
                                    ->addFieldToFilter('product_id', $product->getEntityId())->addFieldToFilter('account_id', $accountId)
                                    ->addFieldToFilter('profile_id', $profileId)->getFirstItem();*/
                                    $pProduct = $this->profileProducts->create()->addFieldToFilter('account_id', $accountId)
                                    ->addFieldToFilter('product_id', $product->getEntityId())->addFieldToFilter('account_id', $accountId)
                                    ->addFieldToFilter('profile_id', $profileId)->getFirstItem();
                                $prodDetails[$product->getSku()] = ['profile_id' => $profileId, 'product_id' => $product->getEntityId()];

                                $profile = $this->profileFactory->create()->load($profileId);
                                $profilePrice = $this->onbuy->getReqOptAttributes($product, $profile);
                                
                                $response = '';
                                if(isset($profilePrice['product_codes']))
                                {
                                    $barcode_check = $profilePrice['product_codes'][0];
                                    $response = $this->dataHelper->getRequest("products?site_id=2000&filter[query]=$barcode_check&filter[field]=product_code", true);
                                    
                                    if(empty($response)){ 
                                    $barcode_check = $profilePrice['sku'];
                                    $response = $this->dataHelper->getRequest("products?site_id=2000&filter[query]=$barcode_check&filter[field]=sku", true);
                                    //print_r($response); die(__DIR__);
                                    }
                                }else{
                                    $barcode_check = $profilePrice['sku'];
                                    $response = $this->dataHelper->getRequest("products?site_id=2000&filter[query]=$barcode_check&filter[field]=mpn", true);
                                }
                                $opc_response = json_decode($response,true);


                                $opc_response = json_decode($response,true);
                                $get_opc = '';
                                if(isset($opc_response['results'][0]['opc']))
                                {
                                    $get_opc = $opc_response['results'][0]['opc'];
                                }
                                if($get_opc!='')
                                {
                                    $pProduct->setOpc($get_opc);
                                $pProduct->setProductStatus('processing');
                                $pProduct->setListingError('"valid"');
                                $pProduct->save();
                                }
                            }
                            if($product->getTypeId() == 'configurable')
                            {
                              
                                $parent_id = $product->getEntityId();
                                $childProds = $product->getTypeInstance()->getUsedProducts($product);
                                $childOpc = [];
                                  
                                foreach ($childProds as $childProd) { 
                                    
                                    $childProd = $this->objectManager->create('Magento\Catalog\Model\Product')->load($childProd->getId());
                                   
                                    $profileId = $this->onbuy->getAssignedProfileId($product->getEntityId(), $accountId);
                                   
                                    $pProduct = $this->profileProducts->create()->addFieldToFilter('account_id', $accountId)
                                    ->addFieldToFilter('product_id', $product->getEntityId())->addFieldToFilter('account_id', $accountId)
                                    ->addFieldToFilter('profile_id', $profileId)->getFirstItem();
                                    
                                $prodDetails[$product->getSku()] = ['profile_id' => $profileId, 'product_id' => $product->getEntityId()];

                                $profile = $this->profileFactory->create()->load($profileId);
                                    
                                $profilePrice = $this->onbuy->getReqOptAttributes($childProd, $profile);
                                   
                                 print_r($profilePrice); die();  
                                $response = '';
                                if(isset($profilePrice['product_codes']))
                                {
                                    
                                    $barcode_check = $profilePrice['product_codes'][0];
                                    
                                    $response = $this->dataHelper->getRequest("products?site_id=2000&filter[query]=$barcode_check&filter[field]=product_code", true);

                                   if(empty($response)){ 
                                    $barcode_check = $profilePrice['sku'];
                                    $response = $this->dataHelper->getRequest("products?site_id=2000&filter[query]=$barcode_check&filter[field]=sku", true);
                                    //print_r($response); die(__DIR__);
                                    }
                                    
                                }else{
                                    
                                    $barcode_check = $profilePrice['sku'];
                                    $response = $this->dataHelper->getRequest("products?site_id=2000&filter[query]=$barcode_check&filter[field]=mpn", true);
                                }
                                   
                                $opc_response = json_decode($response,true);
                               
                                $get_opc = '';
                                if(isset($opc_response['results'][0]['opc']))
                                {
                                    $get_opc = $opc_response['results'][0]['opc'];
                                }
                                if($get_opc!='')
                                {
                                    $childOpc[$profilePrice['sku']] = $get_opc;
                                    
                                }

                             
                                }
                                
                                 
                                 if(count($childOpc)>1) {
                                    $pProduct->setChildOpc(json_encode($childOpc));
                                    $pProduct->setOpc('child opc');
                                $pProduct->setProductStatus('uploaded');
                                $pProduct->setListingError('"valid"');
                                $pProduct->save();
                                 }
                                 else
                                 {
                                    $pProduct->setChildOpc('');
                                    $pProduct->setOpc('');
                                    $pProduct->save();
                                 }
                                 
                                
                            }
                            
                           
                            
                    


                    }
                   

                    

                    
                }
                if (!empty($success)) {
                    $message['success'] = "Batch ".$index.": ".implode(', ', $success)." Uploaded Successfully";
                }
                if (!empty($error)) {
                    $message['error'] = "Batch ".$index.": ".implode(', ', $error);
                }
            } else {
                $message['error'] = "Batch ".$index.": ".$message['error']." included Product(s) data not found.";
            }
        } catch (\Exception $e) {
            $message['error'] = $e->getMessage();
            $this->logger->addError($message['error'], ['path' => __METHOD__]);
        }
        return $resultJson->setData($message);
    }

    public function check($product,$accountId,$parent_id)
    {
                if($parent_id)
                    {
                        $profileId = $this->onbuy->getAssignedProfileId($parent_id, $accountId);
                    }
                    else
                    {
                        $profileId = $this->onbuy->getAssignedProfileId($product->getEntityId(), $accountId);
                    }
                
                        /*$pProduct = $this->profileProducts->create()->addFieldToFilter('account_id', $accountId)
                            ->addFieldToFilter('product_id', $product->getEntityId())->addFieldToFilter('account_id', $accountId)
                            ->addFieldToFilter('profile_id', $profileId)->getFirstItem();*/
                            $pProduct = $this->profileProducts->create()->addFieldToFilter('account_id', $accountId)
                            ->addFieldToFilter('product_id', $product->getEntityId())->addFieldToFilter('account_id', $accountId)
                            ->addFieldToFilter('profile_id', $profileId)->getFirstItem();
                        $prodDetails[$product->getSku()] = ['profile_id' => $profileId, 'product_id' => $product->getEntityId()];

                        $profile = $this->profileFactory->create()->load($profileId);
                        $profilePrice = $this->onbuy->getReqOptAttributes($product, $profile);
                        
                        $response = '';
                        if($product->getEan())
                        //if(isset($profilePrice['product_codes']))
                        {
                            $barcode_check = $product->getEan();
                            $response = $this->dataHelper->getRequest("products?site_id=2000&filter[query]=$barcode_check&filter[field]=product_code", true);

                        }
                        else
                        {
                            $barcode_check = $profilePrice['sku'];
                            $response = $this->dataHelper->getRequest("products?site_id=2000&filter[query]=$barcode_check&filter[field]=mpn", true);
                        }
                        $opc_response = json_decode($response,true);
                        
                        
                        
                       
                       
                       
                        $get_opc = '';
                        if(isset($opc_response['results'][0]['opc']))
                        {
                            $get_opc = $opc_response['results'][0]['opc'];
                        }
                        if($get_opc!='')
                        {
                            $product->setOpc($get_opc);
                        $product->setProductStatus('processing');
                        $product->setListingError('"valid"');
                        $product->save();
                        }
    }
}
