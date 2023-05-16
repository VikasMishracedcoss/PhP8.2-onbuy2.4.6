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
 * @package   Ced_Integrator
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Integrator\Cron;

use Magento\Framework\Event\ManagerInterface as EventManager;

/**
 * Class Cleaner
 * @package Ced\Integrator\Cron
 */
class ProfileProductAssign
{
    /**
     * @var EventManager
     */
    private $_eventManager;

    /** @var \Ced\Integrator\Model\ResourceModel\ProductChangeLog\Collection $productChange */
    protected $productChange;

    /** @var \Ced\Integrator\Model\QueryCondition $queryCondition */
    protected $queryCondition;

    /** @var \Ced\Integrator\Helper\Logger  */
    public $logger;

    public function __construct(
        \Ced\Integrator\Model\ResourceModel\ProductChangeLog\CollectionFactory $productChangeCollection,
        \Ced\Integrator\Model\QueryCondition $queryCondition,
        \Ced\Integrator\Helper\Logger $logger,
        EventManager $eventManager
    ) {
        $this->queryCondition = $queryCondition;
        $this->productChange = $productChangeCollection;
        $this->_eventManager = $eventManager;
        $this->logger = $logger;
    }

    public function execute()
    {
        try {
            $productIds = $idsToSave = [];
            for ($i = 1; $i <= 5; $i++) {
                $productIds = $this->getChangeProductIds($i);
                if(count($productIds) > 0)
                    break;
            }
            /** @var \Ced\Integrator\Model\QueryCondition $queryCondition */
            $queryConditions = $this->queryCondition->getCollection();
            foreach ($queryConditions as $queryCondition) {
                $condProductIds = $queryCondition->getMatchingProductIds();
                $idsToAssign = array_intersect($productIds, $condProductIds);

                if(is_array($idsToAssign) && count($idsToAssign) > 0)
                    $idsToSave[$queryCondition->getModuleName()][] = [
                        'query_condition_id' => $queryCondition->getId(),
                        'product_ids' => $idsToAssign
                    ];
            }
            foreach ($idsToSave as $moduleName => $idsWithConditionId) {
                try {
                    $this->_eventManager->dispatch($moduleName . '_product_assign',
                        [
                            'product_ids_with_condition_id' => json_encode($idsWithConditionId)
                        ]
                    );
                } catch (\Exception $e) {
                    $this->logger->addError('Profile Product Assign Cron', ['path' => __METHOD__, 'exception' => $e->getMessage()]);
                    continue;
                }
            }
        } catch (\Exception $e) {
            $this->logger->addError('Profile Product Assign Cron', ['path' => __METHOD__, 'exception' => $e->getMessage()]);
            return false;
        }
    }

    public function getChangeProductIds($thresholdLimit = 5) {
        try {
            $prodChangeColl = $this->productChange->create()
                ->addFieldToFilter('action', ['in' =>
                    [
                        \Ced\Integrator\Model\ProductChangeLog::ACTION_TYPE_ASSIGN
                    ]
                ])
                ->addFieldToFilter('threshold_limit', ['lt' => $thresholdLimit])
                ->setPageSize(500)
                ->setCurPage(1);
            foreach ($prodChangeColl as $prodChange) {
                $prodChange->setThresholdLimit((int) $prodChange->getThresholdLimit() + 1);
                $prodChange->save();
            }
            return $prodChangeColl->getColumnValues('product_id');
        } catch (\Exception $e) {
            $this->logger->addError('Profile Product Assign Cron', ['path' => __METHOD__, 'exception' => $e->getMessage()]);
            return [];
        }
    }
}
