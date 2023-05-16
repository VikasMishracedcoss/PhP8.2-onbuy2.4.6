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
 * Class Startrelist
 * @package Ced\Onbuy\Controller\Adminhtml\Product
 */
class Startrelist extends Action
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
        \Ced\Onbuy\Helper\Onbuy $trademe,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Ced\Onbuy\Helper\MultiAccount $multiAccountHelper,
        \Ced\Onbuy\Model\Source\Productstatus $productstatus,
        \Ced\Onbuy\Model\AccountsFactory $accountsFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->dataHelper = $dataHelper;
        $this->logger = $logger;
        $this->trademe = $trademe;
        $this->scopeConfig = $scopeConfig;
        $this->productstatus = $productstatus;
        $this->_coreRegistry = $coreRegistry;
        $this->objectManager = $objectManager;
        $this->multiAccountHelper = $multiAccountHelper;
        $this->accountsFactory = $accountsFactory;
    }

    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();
        $message = [];
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
                    if (!is_array($prodIds)) {
                        $prodIds[] = $prodIds;
                    }
                    if ($this->_coreRegistry->registry('trademe_account'))
                        $this->_coreRegistry->unregister('trademe_account');
                    $this->multiAccountHelper->getAccountRegistry($accountId);
                    $this->dataHelper->updateAccountVariable();

                    foreach ($prodIds as $id) {
                        
                        $product = $this->objectManager->create('Magento\Catalog\Model\Product')->load($id);
                        $listingIdAttr = $this->multiAccountHelper->getProdListingIdAttrForAcc($accountId);
                        $listingId = $product->getData($listingIdAttr);
                        $requestData = array('ListingId' => $listingId, 'ReturnListingDetails' => false);
                        $response = $this->dataHelper->productRelist($requestData);
                        if (isset($response['Success']) && $response['Success'] == 1) {
                            $this->trademe->saveResponseOnProduct($response, $product);
                            $message['success'] .= $product->getSku() . ' ,';
                            $success[] = $product->getSku();

                        } else {
                            $prodError = $this->multiAccountHelper->getProdListingErrorAttrForAcc($accountId);
                            $product->setData($prodError, json_encode($response));
                            $product->getResource()->saveAttribute($product, $prodError);
                            $message['error'] .= "Error From Onbuy While Relist Product SKU: ".$product->getSku()." ".json_encode($response);
                            $error[] = $product->getSku();
                        }

                    }
                }
                if (!empty($message['success'])) {
                    $message['success'] = "Batch ".$index.": ".implode(', ', $success)." Relisted Successfully";
                }
                if (!empty($message['error'])) {
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
