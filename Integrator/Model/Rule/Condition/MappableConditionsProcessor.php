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

namespace Ced\Integrator\Model\Rule\Condition;

use Magento\Framework\Exception\InputException;
use Magento\Framework\Api\SearchCriteria\CollectionProcessor\ConditionProcessor\CustomConditionProviderInterface;
use Ced\Integrator\Model\Rule\Condition\Combine as CombinedCondition;
use Ced\Integrator\Model\Rule\Condition\Product as SimpleCondition;

/**
 * Rebuilds catalog price rule conditions tree
 * so only those conditions that can be mapped to search criteria are left
 *
 * Those conditions that can't be mapped are deleted from tree
 * If deleted condition is part of combined condition with OR aggregation all this group will be removed
 */
class MappableConditionsProcessor
{
    /**
     * @var CustomConditionProviderInterface
     */
    private $customConditionProvider;

    /**
     * @var \Magento\Eav\Model\Config
     */
    private $eavConfig;

    /**
     * @param CustomConditionProviderInterface $customConditionProvider
     * @param \Magento\Eav\Model\Config $eavConfig
     */
    public function __construct(
        CustomConditionProviderInterface $customConditionProvider,
        \Magento\Eav\Model\Config $eavConfig
    ) {
        $this->customConditionProvider = $customConditionProvider;
        $this->eavConfig = $eavConfig;
    }

   /**
     * @param Product $originalConditions
     * @return bool
     */
    private function validateSimpleCondition(SimpleCondition $originalConditions): bool
    {
        return $this->canUseFieldForMapping($originalConditions->getAttribute());
    }
   
    /**
     * Checks if condition field is mappable
     *
     * @param string $fieldName
     * @return bool
     */
    private function canUseFieldForMapping(string $fieldName): bool
    {
        // We can map field to search criteria if we have custom processor for it
        if ($this->customConditionProvider->hasProcessorForField($fieldName)) {
            return true;
        }

        // Also we can map field to search criteria if it is an EAV attribute
        $attribute = $this->eavConfig->getAttribute(\Magento\Catalog\Model\Product::ENTITY, $fieldName);

        // We have this weird check for getBackendType() to verify that attribute really exists
        // because due to eavConfig behavior even if pass non existing attribute code we still receive AbstractAttribute
        // getAttributeId() is not sufficient too because some attributes don't have it - e.g. attribute_set_id
        if ($attribute && $attribute->getBackendType() !== null) {
            return true;
        }

        // In any other cases we can't map field to search criteria
        return false;
    }

     /**
     * @param Combine $originalConditions
     * @return Combine
     * @throws InputException
     */
    private function rebuildCombinedCondition(CombinedCondition $originalConditions): CombinedCondition
    {
        $invalidConditions = [];
        $validConditions = [];
    
        foreach ($originalConditions->getConditions() as $conditions) {
            if ($conditions->getType() === CombinedCondition::class) {
                $rebuildSubCondition = $this->rebuildCombinedCondition($conditions);

                if (count($rebuildSubCondition->getConditions()) > 0) {
                    $validConditions[] = $rebuildSubCondition;
                } else {
                    $invalidConditions[] = $rebuildSubCondition;
                }

                continue;
            }

            if ($conditions->getType() === SimpleCondition::class) {
                if ($this->validateSimpleCondition($conditions)) {
                    $validConditions[] = $conditions;
                } else {
                    $invalidConditions[] = $conditions;
                }

                continue;
            }

            throw new InputException(
                __('Undefined condition type "%1" passed in.', $conditions->getType())
            );
        }

        // if resulted condition group has left no mappable conditions - we can remove it at all
        if (count($invalidConditions) > 0 && strtolower($originalConditions->getAggregator()) === 'any') {
            $validConditions = [];
        }

        $rebuildCondition = clone $originalConditions;
        $rebuildCondition->setConditions($validConditions);

        return $rebuildCondition;
    }

    /**
     * @param Combine $conditions
     * @return Combine
     */
    public function rebuildConditionsTree(CombinedCondition $conditions): CombinedCondition
    {
        return $this->rebuildCombinedCondition($conditions);
    }

}
