<?php
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category    Ced
 * @package     Ced_Onbuy
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CEDCOMMERCE (http://cedcommerce.com/)
 * @license     http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Onbuy\Cron;

use Magento\Eav\Model\ResourceModel\Attribute\Collection;

class UpdateInventory
{
    /**
     * Logger
     * @var \Psr\Log\LoggerInterface
     */
    public $logger;

    /**
     * OM
     * @var \Magento\Framework\ObjectManagerInterface
     */
    public $objectManager;

    /**
     * Config Manager
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    public $scopeConfigManager;

    /**
     * Config Manager
     * @var \Ced\Onbuy\Helper\Data
     */
    public $helper;

    /**
     * Config Manager
     * @var \Ced\Onbuy\Helper\Onbuy
     */
    public $trademeHelper;

    /**
     * DirectoryList
     * @var \Magento\Framework\Filesystem\DirectoryList
     */
    public $directoryList;

    /**
     * @var
     */
    public $helperData;
    /**
     * @var \Ced\Onbuy\Model\Productchange
     */
    public $productchange;

    /**
     * @var \Ced\Onbuy\Helper\MultiAccount
     */
    protected $multiAccountHelper;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    public $token;

    public $schedulerCollection;

    public $schedulerResource;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Attribute\CollectionFactory
     */
    public $productCollectionFactory;

    /**
     * UploadProducts constructor.
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        \Ced\Onbuy\Helper\Logger $logger,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Filesystem\DirectoryList $directoryList,
        \Ced\Onbuy\Model\ResourceModel\JobScheduler\CollectionFactory $schedulerCollection,
        \Ced\Onbuy\Model\JobScheduler $schedulerResource,
        \Ced\Onbuy\Helper\Data $helperData,
        \Ced\Onbuy\Helper\Onbuy $onbuy,
        \Ced\Onbuy\Model\Productchange $productchange,
        \Ced\Onbuy\Helper\MultiAccount $multiAccountHelper,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Framework\Registry $registry,
        \Ced\Onbuy\Model\ProfileFactory $profileFactory
    )
    {
        $this->scopeConfigManager = $objectManager->create('Magento\Framework\App\Config\ScopeConfigInterface');
        $this->objectManager = $objectManager;
        $this->helper = $this->objectManager->get('Ced\Onbuy\Helper\Data');
        $this->logger = $logger;
        $this->onbuy = $onbuy;
        $this->directoryList = $directoryList;
        $this->helperData = $helperData;
        $this->productchange = $productchange;
        $this->multiAccountHelper = $multiAccountHelper;
        $this->_coreRegistry = $registry;
        $this->schedulerCollection = $schedulerCollection;
        $this->schedulerResource = $schedulerResource;
        $this->profileFactory = $profileFactory;
        $this->productCollectionFactory = $productCollectionFactory;
    }

    /**
     * Execute
     * @return bool
     */
    public function execute()
    {
        $scopeConfigManager = $this->objectManager
            ->create('Magento\Framework\App\Config\ScopeConfigInterface');
        $autoSync = $scopeConfigManager->getValue('onbuy_config/onbuy_cron/inventory_cron');
        if ($autoSync) {
            $accountIds = $this->multiAccountHelper->getAllAccounts(true);
            foreach ($accountIds as $accountId) {
                $id = [];
                $collection = $this->productchange->getCollection();
               
                $type = \Ced\Onbuy\Model\Productchange::CRON_TYPE_INVENTORY;
                $collection
                /* ->addFieldToFilter('cron_type', $type)
                    ->addFieldToFilter('threshold_limit', array('lt' => 2))*/
                    ->addFieldToFilter('account_id', array('eq' => $accountId->getId()));
                if (count($collection) > 0) {
                    $prodId = [];
                    foreach ($collection->getData() as $dataa) {
                        $prodId[] = $dataa['product_id'];
                    }
                    if ($this->_coreRegistry->registry('onbuy_account'))
                        $this->_coreRegistry->unregister('onbuy_account');
                    $this->multiAccountHelper->getAccountRegistry($accountId->getId());
                    $this->helperData->updateAccountVariable();

                    $id[$accountId->getId()] = $prodId;
                    try {
                        if (isset($id)) {
                            foreach ($prodId as $pid) {

                                $product = $this->objectManager->get('Magento\Catalog\Model\Product')->load($pid);
                                // print_r($product->getData('quantity_and_stock_status'));
                                // die(__FILE__);
                            $profileId = $this->onbuy->getAssignedProfileId($product->getEntityId(), $accountId->getId());

                            $prodDetails[$product->getSku()] = ['profile_id' => $profileId, 'product_id' => $product->getEntityId()];

                                $profile = $this->profileFactory->create()->load($profileId);
                                $profilePrice = $this->onbuy->getReqOptAttributes($product, $profile);
                                if (!isset($profilePrice['price'])){
                                    $this->logger->addInfo($product->getSku() . " : Map price in profile .", ['path' => __METHOD__]);
                                    continue;
                                }
                                 $price = $this->onbuy->getTrademePrice($product, $profile);
                                if (isset($price['error'])){
                                    $error[] = $product->getSku() . " : Map price in Profile .";
                                    continue;
                                }
                            $stock = $product->getData('quantity_and_stock_status');
                            $requestData['listings'][] = ['sku' => $product->getSku(), 'price' => $price['price'],
                                'stock' => (int)$stock['qty']/*'jjj'*/
                            ];
// print_r($requestData);
// die(__FILE__);
                            $requestData['site_id'] = 2000;
                            $response = $this->helper->productSync(json_encode($requestData));

                            $data = $this->productchange->load($accountId->getId());
                            if (isset($response['success'])) {
                                foreach ($id as $accountsId => $deleteIds) {
                                    $data->deleteFromProductChange($deleteIds, $type, $accountId->getId());
                                }
                                
                                /*foreach ($collection as $value) {
                                    if ($value->getProductId() == $pid){
                                        $value->setThresholdLimit((int)$value->getThresholdLimit() + 1);
                                        $value->save();
                                    }

                                }*/

                            } else {
                                /*foreach ($id as $accountsId => $deleteIds) {
                                    $data->deleteFromProductChange($deleteIds, $type, $accountId->getId());
                                }*/
                            }
                        }


                        } else {
                            $this->logger->addInfo("In Cron Included Product(s) data not found.", ['path' => __METHOD__]);

                        }

                    } catch (\Exception $e) {
                        $this->logger->addError($e->getMessage(), ['path' => __METHOD__]);
                    }
                }
                else
                {
                    $this->logger->addInfo("No Record Found In Change Table.", ['path' => __METHOD__]);
                }

            }
        }
        else
        {
            $this->logger->addError('In UpdateInventory Cron: Disable', ['path' => __METHOD__]);
        }
        

    }
}
