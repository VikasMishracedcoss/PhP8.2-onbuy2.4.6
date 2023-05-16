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

namespace Ced\Onbuy\Controller\Adminhtml\Feeds;

use Ced\Onbuy\Model\ResourceModel\Profileproducts\Collection;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Catalog\Model\Product;
use Magento\Ui\Component\MassAction\Filter;
use Ced\Onbuy\Model\ResourceModel\Feeds\CollectionFactory;

/**
 * Class Massdelete
 * @package Ced\Onbuy\Controller\Adminhtml\Account
 */
class Sync extends Action
{
    /**
     * @var CollectionFactory
     */
    public $accounts;

    /**
     * @var Filter
     */
    public $filter;
    /**
     * @var Filter
     */
    public $data;
    public $onbuy;
    public $productFactory;
    public $pProducts;
    public $profileProducts;
    public $multiAccountHelper;
    public $_coreRegistry;
    public $feedsFactory;
    const ADMIN_RESOURCE = 'Ced_Onbuy::Onbuy';

    /**
     * Massdelete constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param CollectionFactory $collectionFactory
     * @param Filter $filter
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Registry $coreRegistry,
        CollectionFactory $accounts,
        \Ced\Onbuy\Helper\MultiAccount $multiAccountHelper,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Ced\Onbuy\Model\ResourceModel\Profileproducts\CollectionFactory $profileProducts,
        \Ced\Onbuy\Model\ProfileproductsFactory $pProducts,
        \Ced\Onbuy\Model\FeedsFactory $feedsFactory,
        \Ced\Onbuy\Helper\Data $data,
        \Ced\Onbuy\Helper\Onbuy $onbuy,
        Filter $filter
    ) {
        parent::__construct($context);
        $this->accounts = $accounts;
        $this->data = $data;
        $this->onbuy = $onbuy;
        $this->productFactory = $productFactory;
        $this->pProducts = $pProducts;
        $this->profileProducts = $profileProducts;
        $this->multiAccountHelper = $multiAccountHelper;
        $this->_coreRegistry = $coreRegistry;
        $this->feedsFactory = $feedsFactory;
        $this->filter = $filter;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $pIds = $this->filter->getCollection($this->accounts->create());

        if ($this->_coreRegistry->registry('onbuy_account'))
            $this->_coreRegistry->unregister('onbuy_account');

        if (!empty($pIds)) {
            try {
                foreach ($pIds as $feedId) {
                
                    $feeds = $this->feedsFactory->create()->load($feedId->getId());
                    $accountId = $feeds->getAccountId();
                    $this->multiAccountHelper->getAccountRegistry($accountId);
                    $this->data->updateAccountVariable();

                    if (!empty($feeds->getQueueId())){
                        $queueId = $feeds->getQueueId();
                        $response =$this->data->processQueue($queueId);
//                        print_r($response);die;

                        if (isset($response['results'])) {
                            $index = 0;

                            $skus = explode(',', $feeds->getProductSkus());
                            if ($feeds->getFileType() == 'Product Create') {
                                $product = $this->productFactory->create()->loadByAttribute('sku', $skus[0]);
                                $profileId = $this->onbuy->getAssignedProfileId($product->getEntityId(), $accountId);
                                $prod = $this->profileProducts->create()->addFieldToFilter('product_id', $product->getEntityId())
                                    ->addFieldToFilter('account_id', $accountId)->addFieldToFilter('profile_id', $profileId)->getFirstItem();
                                $prodProfile = $this->pProducts->create()->load($prod->getId());
                                if (isset($response['results']['status']) && $response['results']['status'] == 'failed') {

                                    $feeds->setStatus('error');
                                    $feeds->setError($response['results']['error_message']);

                                    $prodProfile->setListingError(json_encode($response['results']['error_message']));
                                    $prodProfile->setProductStatus('invalid');
                                    } elseif (isset($response['results']['status']) && $response['results']['status'] == 'success') {

                                    if ($product->getTypeId() == 'configurable'){
                                        $childs = $product->getTypeInstance()->getUsedProducts($product);
                                        $childOpc = [];
                                        foreach ($childs as $child) {
//                                            print_r();die;


                                            $childOpc[$child['sku']] = $response['results']['variant_opcs'][$index];
                                            $index++;

                                        }
                                        $prodProfile->setChildOpc(json_encode($childOpc));

                                    }


                                    $feeds->setStatus('success');


                                    $prodProfile->setListingError("valid");
                                    $prodProfile->setProductStatus('uploaded');
                                    $prodProfile->setOpc($response['results']['opc']);
                                } elseif (isset($response['results']['status'])) {
                                    $feeds->setStatus($response['results']['status']);
                                }

                                $prodProfile->save();
                                $feeds->save();
                            } elseif ($feeds->getFileType() == 'Product Update') {


                                if ($feeds->getParent()) {
                                    $product = $this->productFactory->create()->loadByAttribute('sku', $feeds->getParent());
                                } else {
                                    $product = $this->productFactory->create()->loadByAttribute('sku', $skus[0]);
                                }

                                $profileId = $this->onbuy->getAssignedProfileId($product->getEntityId(), $accountId);
                                $prod = $this->profileProducts->create()->addFieldToFilter('product_id', $product->getEntityId())
                                    ->addFieldToFilter('account_id', $accountId)->addFieldToFilter('profile_id', $profileId)->getFirstItem();
                                $prodProfile = $this->pProducts->create()->load($prod->getId());
                                if (isset($response['results']['status']) && $response['results']['status'] == 'failed') {

                                    $feeds->setStatus('error');
                                    $feeds->setError($response['results']['error_message']);
                                    $error = json_decode($prodProfile->getListingError(), true);
                                    if (!is_array($error)){
                                        $err = $error;
                                        $error = [];
                                        $error[] = $err;
                                    }


                                        $error[$skus[0]] = $response['results']['error_message'];


                                    $prodProfile->setListingError(json_encode($error));
                                    $prodProfile->setProductStatus('invalid');
                                } elseif (isset($response['results']['status']) && $response['results']['status'] == 'success') {

                                    if ($product->getTypeId() == 'configurable'){

                                        $error = json_decode($prodProfile->getListingError(), true);
                                        if (!is_array($error)){
                                            $error = [];
                                            $error[$skus[0]] = "valid";
                                        }
                                        $prodProfile->setListingError($error);

                                    }


                                    $feeds->setStatus('success');


                                    $prodProfile->setListingError("valid");
                                } elseif (isset($response['results']['status'])) {
                                    $feeds->setStatus($response['results']['status']);
                                }

                                $prodProfile->save();
                                $feeds->save();



                            }
                        }


                    }

                }
                $this->messageManager->addSuccessMessage(__('Total of %1 record(s) have been enabled.', count($pIds)));
            } catch (\Exception $e) {
                $this->_objectManager->create('Ced\Onbuy\Helper\Logger')->addError('In Mass Enable Profile: ' . $e->getMessage(), ['path' => __METHOD__]);
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        }

        $this->_redirect('*/*/index');
    }
}
