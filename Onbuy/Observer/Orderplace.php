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
 * @category    Ced
 * @package     Ced_Onbuy
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license     http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Onbuy\Observer;

use Magento\Framework\Event\ObserverInterface;

class Orderplace implements ObserverInterface
{
    /**
     * Request
     * @var  \Magento\Framework\App\RequestInterface
     */
    public $request;

    /**
     * Object Manager
     * @var \Magento\Framework\ObjectManagerInterface
     */
    public $objectManager;

    /**
     * Registry
     * @var \Magento\Framework\Registry
     */
    public $registry;

    /**
     * Onbuy Logger
     * @var \Ced\Onbuy\Helper\Logger
     */
    public $onbuyLogger;
    public $multiaccounthelper;

    /**
     * ProductSaveBefore constructor.
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\RequestInterface $request,
        \Ced\Onbuy\Helper\MultiAccount $multiaccounthelper

    ) {
        $this->request = $request;
        $this->registry  = $registry;
        $this->objectManager = $objectManager;
        $this->_logger =  $this->objectManager->create('\Ced\Onbuy\Helper\Logger');
        $this->multiaccounthelper = $multiaccounthelper;
    }

    public function _construct(
        \Magento\Framework\Message\ManagerInterface $messageManager

    )
    {
        $this->messageManager = $messageManager;

    }

    /**
     * Product SKU Change event handler
     * @param \Magento\Framework\Event\Observer $observer
     * @return \Magento\Framework\Event\Observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $accountId = $this->multiaccounthelper->getAllaccounts();
        $items = $observer->getQuote()->getAllItems();
        foreach ($items as $item){
            $productId =  $item->getData('product_id');
            $oldValue = '';
            $stockItemRepository = $this->objectManager->create('\Magento\CatalogInventory\Api\StockStateInterface');
            $stock = $stockItemRepository->getStockQty($productId);
            $newValue = 0;
            if($stock  > 0)
                $newValue = $stock;
            foreach ($accountId->getData() as $id)
            {
                $accId = $id['id'];
                $type = \Ced\Onbuy\Model\Productchange::CRON_TYPE_INVENTORY;
                $model = $this->objectManager->create('Ced\Onbuy\Model\Productchange');
                $model->setProductChange($productId, $oldValue, $newValue,$type,$accId);
            }
            
        }
    }


}
