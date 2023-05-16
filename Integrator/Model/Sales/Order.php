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
 * @category  Ced
 * @package   Ced_Integrator
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE (http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Integrator\Model\Sales;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Sales\Api\OrderRepositoryInterfaceFactory;

class Order extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @var FilterBuilder
     */
    private $filterBuilder;
    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;
    /**
     * @var OrderRepositoryInterfaceFactory
     */
    private $orderRepositoryInterfaceFactory;

    /**
     * Sales constructor.
     * @param OrderRepositoryInterfaceFactory $orderRepositoryInterfaceFactory
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param FilterBuilder $filterBuilder
     */
    public function __construct(
        OrderRepositoryInterfaceFactory $orderRepositoryInterfaceFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        FilterBuilder $filterBuilder
    ) {
        $this->orderRepositoryInterfaceFactory = $orderRepositoryInterfaceFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
    }

    /**
     * with added Marketplace constant in format( CED-{MARKETPLACE}-{ACCOUNT_ID}-{id}
     * @param $marketplaceOrderId
     * @return bool
     */
    public function getList($marketplaceOrderId)
    {
        $filters[] = $this->filterBuilder->setField('ced_marketplace_order_id')
            ->setValue($marketplaceOrderId)
            ->create();

        $searchCriteria = $this->searchCriteriaBuilder->addFilters($filters)->create();
        $salesOrderCollection = $this->orderRepositoryInterfaceFactory->create()->getList($searchCriteria);
        if ($salesOrderCollection->count()) {
            $salesOrder = $salesOrderCollection->getFirstItem();
            if ($salesOrder && $salesOrder->getId()) {
                return $salesOrder->getIncrementId();
            }
        }

        return false;
    }
}
