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
class Startsync extends Action
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
    public $profileProducts;
    public $feeds;
    public $onbuy;
    protected $objectManager;
    public $profileFactory;
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
                    $a=$this->multiAccountHelper->getAccountRegistry($accountId);
                    $this->dataHelper->updateAccountVariable();

                    foreach ($prodIds as $id) {
                        $store = $a->getAccountStore();
                        $product = $this->objectManager->create('Magento\Catalog\Model\Product')->setStoreId($store)->load($id);

                        $profileId = $this->onbuy->getAssignedProfileId($product->getEntityId(), $accountId);
                        /*$pProduct = $this->profileProducts->create()->addFieldToFilter('account_id', $accountId)
                            ->addFieldToFilter('product_id', $product->getEntityId())->addFieldToFilter('account_id', $accountId)
                            ->addFieldToFilter('profile_id', $profileId)->getFirstItem();*/
                        $prodDetails[$product->getSku()] = ['profile_id' => $profileId, 'product_id' => $product->getEntityId()];

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
                        if ($product->getTypeId() == 'configurable') {
                            $childs = $product->getTypeInstance()->getUsedProducts($product);
                            foreach ($childs as $child) {
                                $price = $this->onbuy->getTrademePrice($child, $profile);
                                if (isset($price['error'])){
                                    $error[] = $child->getSku() . " : Map price in Profile .";
                                    continue;
                                }
                                $child_product = $this->objectManager->create('Magento\Catalog\Model\Product')->load($child->getId());
                                $stock = $child_product->getData('quantity_and_stock_status');
                                $requestData['listings'][] = [
                                    'sku' => $child->getSku(), 
                                    'price' => $price['price'],
                                    'stock' => isset($stock['qty']) ? (int)$stock['qty'] : (int)$stock
                                ];
                            }

                        } else {


                            $requestData['listings'][] = [
                                'sku' => $product->getSku(), 
                                'price' => $price['price'],
                                'stock' => isset($stock['qty']) ? (int)$stock['qty'] : (int)$stock,
                                'sale_price'=>'50',
                                'sale_start_date'=>'2021-11-20 18:55:23',
                                'sale_end_date'=>'2021-11-20 19:55:23'
                            ];
                        }


                    }
                    $requestData['site_id'] = 2000;
                   
                    $response = $this->dataHelper->productSync(json_encode($requestData));

                    if (isset($response['success'])) {
                        foreach ($response['results'] as $i => $res){
                            if (isset($res['error'])) {

                                $pProduct = $this->profileProducts->create()->addFieldToFilter('account_id', $accountId)
                                    ->addFieldToFilter('product_id', $prodDetails[$res['sku']]['product_id'])->addFieldToFilter('account_id', $accountId)
                                    ->addFieldToFilter('profile_id', $prodDetails[$res['sku']]['profile_id'])->getFirstItem();
                                $pProduct->setListingError($res['error'])->save();
                                $error[] = $res['sku']. ': ' . $res['error'];

                            } else{
                                $success[] = $res['sku'];
                                $pProduct = $this->profileProducts->create()->addFieldToFilter('account_id', $accountId)
                                    ->addFieldToFilter('product_id', $prodDetails[$res['sku']]['product_id'])->addFieldToFilter('account_id', $accountId)
                                    ->addFieldToFilter('profile_id', $prodDetails[$res['sku']]['profile_id'])->getFirstItem();
                                $pProduct->setProductStatus('processing')->save();
                            }

                        }

                    } else {
                        $success = [];
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
}
