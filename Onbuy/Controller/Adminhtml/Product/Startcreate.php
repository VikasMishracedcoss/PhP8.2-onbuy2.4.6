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
 * Class Startendlist
 * @package Ced\Onbuy\Controller\Adminhtml\Product
 */
class Startcreate extends Action
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
    public $accountsFactory;
    public $productstatus;
    protected $scopeConfig;

    /**
     * Startendlist constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param JsonFactory $resultJsonFactory
     * @param Data $dataHelper
     * @param Logger $logger
     * @param Onbuy $trademe
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Ced\Onbuy\Helper\MultiAccount $multiAccountHelper
     * @param Productstatus $productstatus
     * @param \Ced\Onbuy\Model\AccountsFactory $accountsFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        JsonFactory $resultJsonFactory,
        Data $dataHelper,
        Logger $logger,
        \Ced\Onbuy\Helper\Onbuy $onbuy,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Registry $coreRegistry,
        \Ced\Onbuy\Model\ResourceModel\Profileproducts\CollectionFactory $profileProducts,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Ced\Onbuy\Helper\MultiAccount $multiAccountHelper,
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
        $this->profileProducts = $profileProducts;
        $this->scopeConfig = $scopeConfig;
        $this->productstatus = $productstatus;
        $this->_coreRegistry = $coreRegistry;
        $this->objectManager = $objectManager;
        $this->multiAccountHelper = $multiAccountHelper;
        $this->accountsFactory = $accountsFactory;
        $this->profileFactory = $profileFactory;
    }

    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();
        $message = $requestData = [];
        $success = $error = [];
        $message['error'] = "";
        $message['success'] = "";
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
                        if ($product->getTypeId() == 'simple') {

                            $profileId = $this->onbuy->getAssignedProfileId($product->getEntityId(), $accountId);
                            $pProduct = $this->profileProducts->create()->addFieldToFilter('account_id', $accountId)
                                ->addFieldToFilter('product_id', $product->getEntityId())->addFieldToFilter('account_id', $accountId)
                                ->addFieldToFilter('profile_id', $profileId)->getFirstItem();
                            $prodDetails[$product->getSku()] = ['profile_id' => $profileId, 'account_id' => $accountId, 'product_id' => $product->getEntityId()];
                            $requestData['site_id'] = 2000;
                            $profile = $this->profileFactory->create()->load($profileId);
                            $profilePrice = $this->onbuy->getReqOptAttributes($product, $profile);
                          
                            if (!isset($profilePrice['price'])){
                                $error[] = $product->getSku() . " : Map price in profile .";
                                continue;
                            }
                            $price = $this->onbuy->getTrademePrice($product, $profile);
                            if (isset($price['error'])){
                                $error[] = $product->getSku() . " : Map price in Profile .";
                                continue;
                            }
                            $stock = $product->getData('quantity_and_stock_status');
                           $condition=$product->getData('onbuy_product_condition');
                           $c= isset($condition) ? $condition : 'new';
                            $requestData['listings'][] = [
                                'sku' => $product->getSku(),
                                 'price' => $price['price'],
                                'stock' => isset($stock['qty']) ? (int)$stock['qty'] : (int)$stock,
                                 'opc' => $pProduct->getOpc(), 
                                 'condition' => $c,
                                
                            ];
                            //print_r($requestData);die('hj');
                            if(isset($profilePrice['boost_marketing_commission'])){
                                 $boost=round($profilePrice['boost_marketing_commission'],2);
                                $t=['boost_marketing_commission'=>$boost?$boost:''];
                                foreach($requestData['listings'] as $k){
                                    $requestData['listings']= array_merge($t,$k);

                                   
                                }
                                

                            }
                        } else {
                            $error[] = $product->getSku() . " : Product already created from different seller";
                        }

                    }
                    if (!empty($requestData)){
                    
                        $response = $this->dataHelper->createListing(json_encode($requestData));
                        if (isset($response['success']) && $response['success']) {
                            foreach ($response['results'] as $i => $res) {
                                $pProduct = $this->profileProducts->create()->addFieldToFilter('account_id', $accountId)
                                    ->addFieldToFilter('product_id', $prodDetails[$res['sku']]['product_id'])->addFieldToFilter('account_id', $accountId)
                                    ->addFieldToFilter('profile_id', $prodDetails[$res['sku']]['profile_id'])->getFirstItem();
                                if (isset($res['success']) && $res['success']) {
                                    $pProduct->setProductStatus('processing')->save();

                                    $success[] = $res['sku'];

                                } else {
                                    if (isset($res['message']) && $res['message'] == "A listing for this product / condition already exists"){
                                        $pProduct->setProductStatus('processing')->save();
                                        $success[] = $res['sku'];
                                    } else {

                                        $err = isset($res['message']) ? $res['message'] : json_encode($res);
                                        $pProduct->setListingError($err)->save();
                                        $error[] = $res['sku'] . ': ' . $err;
                                    }
                                }

                            } 

                        } else {
                            $success = [];
                        }
                }
                }
                if (!empty($success)) {
                    $message['success'] = "Batch ".$index.": ".implode(', ', $success)." Listing Created Successfully";
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
}
