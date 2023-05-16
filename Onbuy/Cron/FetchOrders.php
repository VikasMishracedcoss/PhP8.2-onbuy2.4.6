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


/**
 * Class SyncProducts
 * @package Ced\Onbuy\Cron
 */
class FetchOrders
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


    /**
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Ced\Onbuy\Helper\Order $orderHelper
     */
    public function __construct(
        Data $dataHelper,
        \Ced\Onbuy\Helper\Logger $logger,
        \Ced\Onbuy\Helper\Order $orderHelper,
        \Magento\Framework\Registry $coreRegistry,
        \Ced\Onbuy\Helper\Onbuy $trademe,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory,
        \Ced\Onbuy\Helper\MultiAccount $multiAccountHelper
    )
    {
        $this->logger = $logger;
        $this->collectionFactory = $collectionFactory;
        $this->dataHelper = $dataHelper;
        $this->orderHelper = $orderHelper;
        $this->trademe = $trademe;
        $this->_coreRegistry = $coreRegistry;
        $this->objectManager = $objectManager;
        $this->multiAccountHelper = $multiAccountHelper;
    }

    /**
     * @return \Ced\Onbuy\Helper\Order
     */
    public function execute()
    {
        try {
            $resultData = true;
            $scopeConfigManager = $this->objectManager
                ->create('Magento\Framework\App\Config\ScopeConfigInterface');
            $autoFetch = $scopeConfigManager->getValue('onbuy_config/onbuy_cron/order_cron');
            if ($autoFetch) {
                $acccounts = $this->multiAccountHelper->getAllAccounts(true);
                $acccountIds = $acccounts->getColumnValues('id');

                $resultData = $this->orderHelper->fetchOrders($acccountIds);
                if (isset($resultData['error'])) {
                    $this->logger->addError('In FetchOrder Cron: error', ['path' => __METHOD__, 'Response' => json_encode($resultData)]);
                } else {
                    $this->logger->addError('In FetchOrder Cron: success', ['path' => __METHOD__]);
                }

                }

            $this->logger->addError('In FetchOrders Cron: Disable', ['path' => __METHOD__]);
            return $resultData;
        } catch (\Exception $e) {
            $this->logger->addError('In FetchOrders Cron: Exception', ['path' => __METHOD__, 'Response' => $e->getMessage()]);
        }
    }
}
