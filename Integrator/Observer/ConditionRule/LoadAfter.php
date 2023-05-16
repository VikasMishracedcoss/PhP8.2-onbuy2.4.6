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
 * @package     Ced_Integrator
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright © 2019 CedCommerce. All rights reserved.
 * @license     EULA http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Integrator\Observer\ConditionRule;

use Magento\Framework\Event\ObserverInterface;
use Ced\Integrator\Helper\File\Logger;
use Ced\Integrator\Model\QueryCondition;

class LoadAfter implements ObserverInterface
{
    /** @var QueryCondition $conditionRule */
    public $conditionRule;

    /** @var Logger $logger */
    public $logger;

    public function __construct(
        \Ced\Integrator\Model\QueryCondition $conditionRuleModel,
        Logger $logger
    ) {
        $this->conditionRule = $conditionRuleModel;
        $this->logger = $logger;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            $profileModel = $observer->getDataObject();
            if($profileModel->getConditionsSerializedId())
                $this->conditionRule->load($profileModel->getConditionsSerializedId());
            //$this->conditionRule->setProfile($profileModel);
            $conditions = $this->conditionRule->getConditions();
            $profileModel->setConditions($conditions);
        } catch (\Exception $e) {
            $this->logger->addError('Inside Profile Load After', ['path' => __METHOD__, 'Error' => $e->getMessage()]);
        }
    }
}
