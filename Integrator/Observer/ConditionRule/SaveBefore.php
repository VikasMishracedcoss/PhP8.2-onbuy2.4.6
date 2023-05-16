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
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\App\RequestInterface;
use Ced\Integrator\Helper\File\Logger;
use Ced\Integrator\Model\QueryCondition;

class SaveBefore implements ObserverInterface
{
    /** @var QueryCondition $conditionRule */
    public $conditionRule;

    /** @var Logger $logger */
    public $logger;

    /** @var RequestInterface $_request */
    protected $_request;

    /**
     * @var Json
     * @since 100.2.0
     */
    protected $serializer;

    public function __construct(
        \Ced\Integrator\Model\QueryConditionFactory $conditionRuleModel,
        Json $serializer = null,
        RequestInterface $request,
        Logger $logger
    ) {
        $this->conditionRuleFactory = $conditionRuleModel;
        $this->logger = $logger;
        $this->_request = $request;
        $this->serializer = $serializer ?: \Magento\Framework\App\ObjectManager::getInstance()->get(
            Json::class
        );
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            $this->conditionRule = $this->conditionRuleFactory->create();
            $profileModel = $observer->getDataObject();
            if ($profileModel->getConditionsSerializedId()) {
                $this->conditionRule->load($profileModel->getConditionsSerializedId());
            }
            $rule = $profileModel->getRule();
            $profileModel->unsRule();
            /*if(isset($rule['conditions'])) {
                $this->conditionRule->setConditions($rule['conditions']);
            }
            $this->conditionRule->setProfile($profileModel);*/
            if (isset($rule['conditions'])) {
                $this->_request->setParam('is_query_used', true);
                $this->conditionRule->loadPost($rule);
                if($this->conditionRule->getConditions()) {
                    $this->conditionRule->setConditionsSerialized($this->serializer->serialize($this->conditionRule->getConditions()->asArray()));
                }
                $this->conditionRule->setModuleName(strtolower($this->_request->getModuleName()));
                $conditionSerializedId = $this->conditionRule->save();
                $profileModel->setConditionsSerializedId($conditionSerializedId->getId());
            }
        } catch (\Exception $e) {
            $this->logger->addError('Inside Profile Save Before', ['path' => __METHOD__, 'Error' => $e->getMessage()]);
        }
    }
}
