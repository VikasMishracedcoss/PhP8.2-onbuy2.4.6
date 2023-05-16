<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_RemoveCartRule
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Ced\Onbuy\Plugin;

use Magento\Framework\Session\SessionManager;

class RulesApplier
{
    /**
     * @var \Magento\SalesRule\Model\ResourceModel\Rule\Collection
     */
    private $rules;

    /**
     * @param \Magento\SalesRule\Model\ResourceModel\Rule\Collection $rules
     */
    public function __construct(
        \Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory $rulesFactory
    ) {
        $this->ruleCollection = $rulesFactory;
    }

    public function aroundApplyRules(
        \Magento\SalesRule\Model\RulesApplier $subject,
        \Closure $proceed,
        $item,
        $rules,
        $skipValidation,
        $couponCode
    ) {
        $rules = $this->ruleCollection->create()->addFieldToFilter("rule_id", ["eq"=>0]);
        $result = $proceed($item, $rules, $skipValidation, $couponCode);
        return $result;
    }
}