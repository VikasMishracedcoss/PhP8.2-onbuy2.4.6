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
class Startdelete extends Action
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
        \Ced\Onbuy\Model\ResourceModel\Profileproducts\CollectionFactory $profileProducts,
        \Ced\Onbuy\Model\ResourceModel\Feeds\CollectionFactory $feeds,
        \Ced\Onbuy\Model\Source\Productstatus $productstatus,
        \Ced\Onbuy\Model\AccountsFactory $accountsFactory
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
                    $prodDetails = $parent = [];
                    if (!is_array($prodIds)) {
                        $prodIds[] = $prodIds;
                    }
                    if ($this->_coreRegistry->registry('onbuy_account'))
                        $this->_coreRegistry->unregister('onbuy_account');
                    $this->multiAccountHelper->getAccountRegistry($accountId);
                    $this->dataHelper->updateAccountVariable();
                    $skus = [];

                    foreach ($prodIds as $id) {
                        $product = $this->objectManager->create('Magento\Catalog\Model\Product')->load($id);

                        $profileId = $this->onbuy->getAssignedProfileId($product->getEntityId(), $accountId);

                        if ($product->getTypeId() == 'simple')
                        $skus[] = $product->getSku();
                        elseif ($product->getTypeId() == 'configurable') {
                            $childs = $product->getTypeInstance()->getUsedProducts($product);
                            foreach ($childs as $child) {
                                $skus[] = $child->getSku();
                                $prodDetails[$child->getSku()] = ['parent' => $product->getSku()];
                            }
                        }
                        $prodDetails[$product->getSku()] = ['profile_id' => $profileId, 'product_id' => $product->getEntityId()];



                        $error[] = $product->getSku();

                    }
                    $requestData['site_id'] = 2000;
                    $requestData['skus'] = $skus;
                    $response = $this->dataHelper->withdrawListing(json_encode($requestData));

                    if (isset($response['success']) && $response['success']) {
                        $error = [];
                        foreach ($response['results'] as $i => $res){

                            if (isset($prodDetails[$i]['parent'])){
                                $parent = $prodDetails[$i]['parent'];
                                $productId = $prodDetails[$parent]['product_id'];
                                $profileId = $prodDetails[$parent]['profile_id'];
                            } else{

                                $productId = $prodDetails[$i]['product_id'];
                                $profileId = $prodDetails[$i]['profile_id'];
                            }
                            $pProduct = $this->profileProducts->create()->addFieldToFilter('account_id', $accountId)
                                ->addFieldToFilter('product_id', $productId)->addFieldToFilter('account_id', $accountId)
                                ->addFieldToFilter('profile_id', $profileId)->getFirstItem();
                            if (isset($res['error'])) {
                                $errs = json_decode($pProduct->getListingError(), true);
                                if (!is_array($errs)){
                                    $err = $errs;
                                    $errs = [];
                                    $errs[] = $err;
                                }
                                $errs[$i] = $res['error'];

                                $pProduct->setListingError($errs)->save();
                                $error[] = /*$res['sku']*/$i. ': ' . $res['error'];

                            } elseif (isset($res['status']) && $res['status'] == 'ok') {
                                $success[] = $i;
                                $pProduct->setOpc('');
                                
                                $pProduct->setProductStatus('ended')->save();


                            }

                        }

                    } else {
                        $success = [];
                    }
                }
                if (!empty($success)) {
                    $message['success'] = "Batch ".$index.": ".implode(', ', $success)." Deleted Successfully";

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
