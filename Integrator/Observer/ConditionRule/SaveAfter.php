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

class SaveAfter implements ObserverInterface
{
    /** @var RequestInterface $_request */
    protected $_request;

    /** @var QueryCondition $conditionRule */
    public $conditionRule;

    /** @var Logger $logger */
    public $logger;

    /**
     * @var Json
     * @since 100.2.0
     */
    protected $serializer;

    public function __construct(
        \Ced\Integrator\Model\QueryConditionFactory $conditionRuleModel,
        RequestInterface $request,
        Json $serializer = null,
        Logger $logger
    ) {
        $this->conditionRuleFactory = $conditionRuleModel;
        $this->_request = $request;
        $this->logger = $logger;
        $this->serializer = $serializer ?: \Magento\Framework\App\ObjectManager::getInstance()->get(
            Json::class
        );
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            $this->conditionRule = $this->conditionRuleFactory->create();
            $profileModel = $observer->getDataObject();
            if($this->_request->getParam('is_query_used') || $profileModel->getIsQueryUsed()) {
                if ($profileModel->getConditionsSerializedId())
                    $this->conditionRule->load($profileModel->getConditionsSerializedId());
                //$this->conditionRule->setProfile($profileModel);
                $productIds = $this->conditionRule->getMatchingProductIds();
                $profileModel->setProfileProductIds($productIds);
                $this->_request->setParam('in_profile_products', implode(',', $productIds));
            }
        } catch (\Exception $e) {
            $this->logger->addError('Inside Profile Save After', ['path' => __METHOD__, 'Error' => $e->getMessage()]);
        }
    }
}
