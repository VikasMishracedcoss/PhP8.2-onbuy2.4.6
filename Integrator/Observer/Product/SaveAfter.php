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
 * @package     Ced_Integrator
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license     http://cedcommerce.com/license-agreement.txt
 */
namespace Ced\Integrator\Observer\Product;

use Magento\Framework\Event\ObserverInterface;

class SaveAfter implements ObserverInterface
{
    /** @var \Ced\Integrator\Model\ProductChangeLog $productChange */
    protected $productChange;

    /** @var \Ced\Integrator\Helper\Logger $logger */
    public $logger;

    /**
     * ProductSaveAfter constructor.
     */
    public function __construct(
        \Ced\Integrator\Helper\Logger $logger,
        \Ced\Integrator\Model\ProductChangeLog $productChange
    ) {
        $this->logger = $logger;
        $this->productChange = $productChange;
    }

    /**
     * Catalog product save after event handler
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            $product = $observer->getEvent()->getProduct();
            $prodIds[] = $product->getId();
            $this->productChange->insertChangedProductIds($prodIds);
            return $observer;
        } catch (\Exception $e) {
            $this->logger->addError('Inside Category Change Product Observer Bulk', ['path' => __METHOD__, 'Error' => $e->getMessage()]);
            return $observer;
        }
    }
}
