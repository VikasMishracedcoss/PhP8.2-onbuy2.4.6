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

use Magento\Backend\App\Action;
use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class Massendlist
 * @package Ced\Onbuy\Controller\Adminhtml\Product
 */
class Masscreate extends Action
{
    /**
     * @var PageFactory
     */
    public $resultPageFactory;
    /**
     * @var CollectionFactory
     */
    public $catalogCollection;
    /**
     * @var Filter
     */
    public $filter;


    const ADMIN_RESOURCE = 'Ced_Onbuy::Onbuy';

    /**
     * @var \Ced\Onbuy\Helper\MultiAccount
     */
    protected $multiAccountHelper;
    protected $objectManager;
    protected $profileProducts;

    /**
     * Massinvpricesync constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param CollectionFactory $collectionFactory
     * @param Filter $filter
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        CollectionFactory $collectionFactory,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Ced\Onbuy\Model\ResourceModel\Profileproducts\CollectionFactory $profileProducts,
        \Ced\Onbuy\Helper\MultiAccount $multiAccountHelper,
        Filter $filter
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->catalogCollection = $collectionFactory;
        $this->objectManager = $objectManager;
        $this->profileProducts = $profileProducts;
        $this->filter = $filter;
        $this->multiAccountHelper = $multiAccountHelper;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $productIdsToWithdraw = $configSimpleIds = [];
        $accountId = $this->_session->getAccountId();
        $prodStatusAccAttr = $this->multiAccountHelper->getProdStatusAttrForAcc($accountId);
        $prods = $this->profileProducts->create()->addFieldToFilter('account_id', $accountId)->addFieldToFilter('opc', ['neq' => null])->addFieldToSelect('product_id')->getData();

        $ids = $this->filter->getCollection($this->catalogCollection->create()->addAttributeToFilter('entity_id', ['in' => $prods]))->getAllIds();
        if (!empty($ids)) {
            $simpleCollection = $this->catalogCollection->create()
                ->addAttributeToSelect('entity_id')
                ->addAttributeToFilter('entity_id', $ids);
            $productids = array_column($simpleCollection->getData(), 'entity_id');

            $scopeConfigManager = $this->objectManager
                ->create('Magento\Framework\App\Config\ScopeConfigInterface');
            $chunkSize = $scopeConfigManager->getValue('onbuy_config/product_upload/chunk_size');

            if ($chunkSize == null)
                $chunkSize = 50;

            $productids = (array_chunk($productids, $chunkSize));
            foreach ($productids as $prodChunkKey => $prodids) {
                $productids[$prodChunkKey] = array($accountId => $prodids);
            }
            $productIdsToWithdraw = array_merge($productIdsToWithdraw, $productids);
            $this->_session->setUploadChunks($productIdsToWithdraw);
            $resultPage = $this->resultPageFactory->create();
            $resultPage->setActiveMenu('Ced_Onbuy::product');
            $resultPage->getConfig()->getTitle()->prepend(__('Create Listing On OnBuy'));
            return $resultPage;
        } else {
            $this->messageManager->addErrorMessage(__('No product available to Create Listing and If OPC Code does not exists, Kindly re upload the product.'));
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setUrl($this->_redirect->getRefererUrl());
            return $resultRedirect;
        }
    }

    public function getSimpleProductIds($configProductIds)
    {
        $simpleIds = array();
        foreach ($configProductIds as $configProductId) {
            $product = $this->catalogCollection->create()
                ->addAttributeToFilter('entity_id', $configProductId)
                ->getFirstItem();
            if ($product == NULL) {
                continue;
            }
            if ($product->getTypeId() == 'configurable') {

                $productType = $product->getTypeInstance();
                $products = $productType->getUsedProducts($product);
                foreach ($products as $chProduct) {
                    $simpleIds[] = $chProduct->getId();
                }
            }
        }
       
        return $simpleIds;
    }
}
