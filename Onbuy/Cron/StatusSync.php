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

namespace Ced\Onbuy\Cron;
use Magento\Catalog\Model\Product;
use Ced\Onbuy\Helper\Data;
use Magento\Backend\Model\Session;


    /**
     * Class StatusSync
     * @package Ced\Onbuy\Cron
     */
class StatusSync
{
    public $logger;
    public $orderHelper;
    /**
     * @var \Ced\Onbuy\Helper\MultiAccount
     */
    protected $multiAccountHelper;
    public $collectionFactory;
    protected $_coreRegistry;
    public $dataHelper;
    public $schedulerResource;
    public $schedulerCollection;
    public $profileCollection;
    public $prodCollection;
    public $adminSession;

    /**
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Ced\Onbuy\Helper\Order $orderHelper
     */
    public function __construct(
    Data $dataHelper,
    \Ced\Onbuy\Helper\Logger $logger,
    \Ced\Onbuy\Helper\Order $orderHelper,
    \Magento\Framework\Registry $coreRegistry,
    \Ced\Onbuy\Helper\Onbuy $onbuy,
    \Ced\Onbuy\Model\ResourceModel\Feeds\CollectionFactory $feeds,
    \Ced\Onbuy\Model\FeedsFactory $feedsfactory,
    \Ced\Onbuy\Model\ResourceModel\JobScheduler\CollectionFactory $schedulerCollection,
    \Ced\Onbuy\Model\ResourceModel\Profileproducts\CollectionFactory $pProducts,
    \Ced\Onbuy\Model\JobSchedulerFactory $schedulerResource,
    \Magento\Framework\ObjectManagerInterface $objectManager,
    \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $prodCollection,
    \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory,
    \Ced\Onbuy\Helper\MultiAccount $multiAccountHelper,
    Session $session
    )
    {
        $this->logger = $logger;
        $this->collectionFactory = $collectionFactory;
        $this->dataHelper = $dataHelper;
        $this->orderHelper = $orderHelper;
        $this->feeds = $feeds;
        $this->feedsfactory = $feedsfactory;
        $this->schedulerCollection = $schedulerCollection;
        $this->schedulerResource = $schedulerResource;
        $this->onbuy = $onbuy;
        $this->pProducts = $pProducts;
        $this->adminSession = $session;
        $this->_coreRegistry = $coreRegistry;
        $this->prodCollection = $prodCollection;
        $this->objectManager = $objectManager;
        $this->multiAccountHelper = $multiAccountHelper;
    }

    /**
     * @return \Ced\Onbuy\Helper\Order
     */
    public function execute()
    {
        try {
            $order = true;
            $synced = false;
            $scopeConfigManager = $this->objectManager
                ->create('Magento\Framework\App\Config\ScopeConfigInterface');
            $autoFetch = $scopeConfigManager->getValue('onbuy_config/onbuy_cron/feeds_sync_cron');

            if ($autoFetch) {
                $schedulerCollection = $this->schedulerCollection->create()
                    ->addFieldToFilter('cron_type', 'feed_sync')
                    ->addFieldToFilter('cron_status', 'to_sync')
                    ->setPageSize(1)
                    ->setCurPage(1);

                if ($schedulerCollection->getSize() > 0) {

                    foreach ($schedulerCollection as $schedulerColl) {

                        $statusArray = [];
                        $schedulerId = $schedulerColl->getId();
                        $schedulerData = $this->schedulerResource->create()->load($schedulerId);
                        try {
                            if (count($schedulerData->getData()) > 0) {
                                if ($schedulerData->getThreshold() == null || $schedulerData->getThreshold() <= 2) {
                                    $productIds = $schedulerData->getProductIds();
                                    $productIds = is_string($productIds) ? /*explode*/
                                        (/*",",*/
                                        json_decode($productIds, true)) : array();


                                    if ($this->_coreRegistry->registry('onbuy_account'))
                                        $this->_coreRegistry->unregister('onbuy_account');
                                    $this->dataHelper->updateAccountVariable();
                                    foreach ($productIds as $key => $id) {
                                        $feeds = $this->feedsfactory->create()->load($id);
                                        $accountId = $feeds->getAccountId();

                                         $synced = true;

                                            $response = $this->dataHelper->processQueue($feeds->getQueueId());

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
                                    if (!$synced) {
                                        $schedulerData->setCronStatus('synced');
                                    }
                                    $schedulerData->setError(json_encode($statusArray));
                                    $schedulerData->save();

                                } else {
                                    $schedulerData->setCronStatus('synced');
                                    $schedulerData->save();
                                }
                            }
                        } catch (\Exception $e) {
                            $this->logger->addError('In FeedsSync Cron: Exception', ['path' => __METHOD__, 'Response' => $e->getMessage()]);
                            $schedulerData->setCronStatus('synced');
                            $schedulerData->setError($e->getMessage());
                            $schedulerData->save();

                        }

                    }
                } else {
                    $this->scheduleSyncIds();
                }

            }
            $this->logger->addError('In FeedsSync Cron: Disable', ['path' => __METHOD__]);
            return $order;
        } catch (\Exception $e) {
            $this->logger->addError('In FeedsSync Cron: Exception', ['path' => __METHOD__, 'Response' => $e->getMessage()]);
        }
    }

    public function scheduleSyncIds()
    {
        $hasError = false;
        $prodCollection = $this->getAllAssignedProductCollectionToInvSync();
        if (count($prodCollection) > 0) {

            foreach ($prodCollection as $chunkIndex => $collectionIds) {
                $accountIdToSchedule = '';
                $scheduled = $this->createSchedulerForIdsWithActionToSync($collectionIds, $accountIdToSchedule);
                if (!$scheduled && !$hasError) {
                    $hasError = true;
                }
            }


            if (!$hasError) {
                $this->logger->addInfo('Schedule Sync Ids', array('path' => __METHOD__, 'Response' => 'Product Ids Scheduled for Status Sync.'));
            } else {
                $this->logger->addInfo('Schedule Sync Ids', array('path' => __METHOD__, 'Response' => 'Something Went Wrong while scheduling Product Ids Scheduled for Status Sync'));
            }
        } else {
            $this->logger->addInfo('Schedule Sync Ids', array('path' => __METHOD__, 'Response' => 'No Product Assigned in Active Profiles.'));
        }
        return $hasError;
    }

    public function getAllAssignedProductCollectionToInvSync()
    {
        $productIdsToSchedule = $accountChunks = array();
        $accounts = $this->multiAccountHelper->getAllAccounts(true);


            $collection = $this->feeds->create();

            $prodIds = $collection
                ->addFieldToFilter('status', 'pending')
                ->getColumnValues('id');


            $prodIdsChunks = array_chunk($prodIds, 10);
            $productIdsToSchedule = array_merge($productIdsToSchedule, $prodIdsChunks);
        return $productIdsToSchedule;
    }

    public function getAccountIndexesSession()
    {
        return $this->adminSession->getAccountIndexes();
    }

    public function createSchedulerForIdsWithActionToSync($collectionIds = array(), $accountId = null)
    {
        try {
            $prodIds = array_chunk($collectionIds, 4);
            foreach ($prodIds as $ids) {
                $idstring = json_encode(/*',',*/ $ids);
                /** @var \Ced\Onbuy\Model\JobScheduler $scheduler */
                $scheduler = $this->schedulerResource->create();
                $scheduler->setProductIds(/*json_encode*/ ($idstring));
                $scheduler->setCronStatus('to_sync');
                $scheduler->setCronType('feed_sync');
                $scheduler->save();
            }
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
