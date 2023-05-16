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
class Startupdate extends Action
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
        \Ced\Onbuy\Model\Source\Productstatus $productstatus,
        \Ced\Onbuy\Model\ResourceModel\Profileproducts\CollectionFactory $profileProducts,
        \Ced\Onbuy\Model\FeedsFactory $feeds,
        \Ced\Onbuy\Model\ProfileFactory $profileFactory,
        \Ced\Onbuy\Model\AccountsFactory $accountsFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->dataHelper = $dataHelper;
        $this->logger = $logger;
        $this->onbuy = $onbuy;
        $this->profileFactory = $profileFactory;
        $this->feeds = $feeds;
        $this->productstatus = $productstatus;
        $this->profileProducts = $profileProducts;
        $this->_coreRegistry = $coreRegistry;
        $this->objectManager = $objectManager;
        $this->multiAccountHelper = $multiAccountHelper;
        $this->accountsFactory = $accountsFactory;
    }

    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();
        $message = [];
        $message['error'] = "";
        $message['success'] = "";
        $error = [];
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
                    $requestData = $productData = $success = $error = [];
                    if (!is_array($prodIds)) {
                        $prodIds[] = $prodIds;
                    }
                    if ($this->_coreRegistry->registry('onbuy_account'))
                        $this->_coreRegistry->unregister('onbuy_account');
                    $this->multiAccountHelper->getAccountRegistry($accountId);
                    $this->dataHelper->updateAccountVariable();
                    foreach ($prodIds as $id) {
                        $product = $this->objectManager->create('Magento\Catalog\Model\Product')->load($id);
                        $profileId = $this->onbuy->getAssignedProfileId($product->getEntityId(), $accountId);
                        $profile = $this->profileFactory->create()->load($profileId);

                        $productData[$product->getSku()] = ['profile_id' => $profileId, 'product_id' => $product->getEntityId(),
                            'account_id' => $accountId];
                        $pProduct = $this->profileProducts->create()->addFieldToFilter('account_id', $accountId)
                            ->addFieldToFilter('product_id', $product->getEntityId())->addFieldToFilter('account_id', $accountId)
                            ->addFieldToFilter('profile_id', $profileId)->getFirstItem();

                        if ($product->getTypeId() == 'simple'){

                            $data = $this->onbuy->prepareData($product, $pProduct);
                            if (isset($data['error'])) {

                                $pProduct->setProductStatus('invalid');
                                $pProduct->setListingError(json_encode($data['error']));
                                $pProduct->save();

                                $error[] .= $product->getSku() . ': ' . $data['error'];
                            } else {
                                $requestData[] = $data;
                            }
                        }
                        elseif ($product->getTypeId() == 'configurable'){

                            $childOps = json_decode($pProduct->getChildOpc(), true);
                            $childs = $product->getTypeInstance()->getUsedProducts($product);
                            foreach ($childs as $child) {
                                $productData[$child->getSku()] = ['parent' => $product->getSku()];

                                $childProd = $this->objectManager->create('Magento\Catalog\Model\Product')->load($child->getEntityId());
                                $data = $this->onbuy->createSimpleProductData($childProd, $profile, 'child',$null= null,'update');
                                
                                $data['opc'] = $childOps[$childProd->getSku()];
                                if (isset($data['error'])) {
                                    $errs = json_decode($pProduct->getListingError(), true);
                                    if (!is_array($errs)){
                                        $err = $errs;
                                        $errs = [];
                                        $errs[] = $err;
                                    }
                                    $errs[$child->getSku()] = $data['error'];

                                    $pProduct->setListingError(json_encode($errs));

                                    $pProduct->setProductStatus('invalid');
                                    $err = $child->getSku(). " : ". $data['error'];
                                    $pProduct->save();

                                    $error[] .= $product->getSku() . ': ' . $err;
                                } else {
                                    $requestData[] = $data;
                            $success[] = $product->getSku();
                                }

                            }
                        }


                    }

                    $finalProductData['site_id'] = 2000;
                    $finalProductData['products'] = $requestData;

                    if (!empty($requestData)) {

                        $response = $this->dataHelper->productUpload(json_encode($finalProductData), 'update');
//                        print_r($response);die;
                        if (isset($response['products'])) {
                            $success = [];
                            foreach ($response['products'] as $i => $res) {
                                $sku = $requestData[$i]['uid'];
                                $feedsModel = $this->feeds->create();
                                $feedsModel->setFileData(json_encode($finalProductData));
                                $feedsModel->setCreatedAt(date("Y-m-d H:i:s"));
                                $feedsModel->setProductSkus($sku);
                                $feedsModel->setFileType('Product Update');
                                $feedsModel->setFeedData(json_encode($finalProductData));
                                $feedsModel->setAccountId($accountId);
                                $pProduct = '';
                                if(isset($productData[$sku]['parent'])){
                                    $parent = $productData[$sku]['parent'];
                                    $parentId = $productData[$parent]['product_id'];
                                    $pProduct = $this->profileProducts->create()
                                        ->addFieldToFilter('account_id', $accountId)
                                        ->addFieldToFilter('product_id', $parentId)
                                        ->addFieldToFilter('profile_id', $productData[$parent]['profile_id'])->getFirstItem();
                                    $feedsModel->setParent($productData[$sku]['parent']);
                                } else {
                                    $pProduct = $this->profileProducts->create()
                                        ->addFieldToFilter('account_id', $accountId)
                                        ->addFieldToFilter('product_id', $productData[$sku]['product_id'])
                                        ->addFieldToFilter('profile_id', $productData[$sku]['profile_id'])->getFirstItem();
                                }

                                if (isset($res['success']) && $res['success']){
                                    $feedsModel->setQueueId($res['queue_id']);
                                    $feedsModel->setStatus('pending');

                                    $pProduct->setProductStatus('processing');
                                    $pProduct->setListingError('"valid"');
                                    $success[] = $sku;



                                } else {
                                    $pProduct->setProductStatus('invalid');
                                    $err = $sku . " : " . $res['message'];
                                    $pProduct->setListingError(json_encode($err));

                                    $feedsModel->setStatus('error');
                                    $error[] .= $sku . ": ". $res['message'];
                                }

                                $feedsModel->save();
                                $pProduct->save();

                            }


                        } elseif (isset($response['error']['message'])) {

                            $feedsModel = $this->feeds->create();
                            $feedsModel->setAccountId($accountId);

                            $feedsModel->setFileData(json_encode($finalProductData));
                            $feedsModel->setCreatedAt(date("Y-m-d H:i:s"));
                            $feedsModel->setProductSkus(implode(',', $success));
                            $feedsModel->setFileType('Product Create');
                            $feedsModel->setFeedData(json_encode($finalProductData));
                            $feedsModel->setStatus('error')->save();
                            $error[] .= $response['error']['message'];
                            $success = [];

                        }
                    }
                }
                if (!empty($success)) {
                    $message['success'] = "Batch ".$index.": ".implode(', ', $success)." Updated Successfully";
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
