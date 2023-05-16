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

namespace Ced\Integrator\Model;

use Ced\Integrator\Helper\Logger;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Ced\Integrator\Model\ResourceModel\Product\ConditionsToCollectionApplier;
use Ced\Integrator\Model\Rule\Condition\CombineFactory;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Rule\Model\Condition\Combine;

class QueryCondition extends \Magento\Framework\Model\AbstractModel
{
    const TABLE_NAME = 'ced_integrator_query_conditions';
    const ID_FIELD_NAME = 'id';

    /** @var $profile */
    public $profile;

    /**
     * Store rule form instance
     *
     * @var \Magento\Framework\Data\Form
     */
    protected $_form;

    /**
     * Store matched product Ids
     *
     * @var array
     */
    protected $_productIds;

    /**
     * @var ConditionsToCollectionApplier
     */
    private $conditionsToCollectionApplier;

    /**
     * @var CollectionFactory
     */
    protected $_productCollectionFactory;

    /**
     * Store rule combine conditions model
     *
     * @var Combine
     */
    protected $_conditions;

    /**
     * @var Json
     * @since 100.2.0
     */
    protected $serializer;

    /**
     * Form factory
     *
     * @var \Magento\Framework\Data\FormFactory
     */
    protected $_formFactory;

    /** @var Logger */
    public $logger;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        CollectionFactory $productCollectionFactory,
        CombineFactory $combineFactory,
        FormFactory $formFactory,
        Logger $logger,        
        ConditionsToCollectionApplier $conditionsToCollectionApplier = null,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        Json $serializer = null,
        array $data = []
    ) {
        $this->logger = $logger;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_combineFactory = $combineFactory;
        $this->_formFactory = $formFactory;
        $this->conditionsToCollectionApplier = $conditionsToCollectionApplier
            ?? \Magento\Framework\App\ObjectManager::getInstance()->get(ConditionsToCollectionApplier::class);
        $this->serializer = $serializer ?: \Magento\Framework\App\ObjectManager::getInstance()->get(
            \Magento\Framework\Serialize\Serializer\Json::class
        );
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->_init(\Ced\Integrator\Model\ResourceModel\QueryCondition::class);
    }

    /**
     * Get array of product ids which are matched by rule
     *
     * @return array
     */
    public function getMatchingProductIds()
    {
        if ($this->_productIds === null) {
            $this->_productIds = [];

            /** @var $productCollection \Magento\Catalog\Model\ResourceModel\Product\Collection */
            $productCollection = $this->_productCollectionFactory->create();

            if ($this->canPreMapProducts()) {
                $productCollection = $this->conditionsToCollectionApplier
                    ->applyConditionsToCollection($this->getConditions(), $productCollection);
                $this->_productIds = $productCollection->getAllIds();
            }

            /*$this->_resourceIterator->walk(
                $productCollection->getSelect(),
                [[$this, 'callbackValidateProduct']],
                [
                    'attributes' => $this->getCollectedAttributes(),
                    'product' => $this->_productFactory->create()
                ]
            );*/
        }

        return $this->_productIds;
    }

    /**
     * Retrieve rule combine conditions model
     *
     * @return \Magento\Rule\Model\Condition\Combine
     */
    public function getConditions()
    {
        if (empty($this->_conditions)) {
            $this->_resetConditions();
        }

        // Load rule conditions if it is applicable
        if ($this->hasConditionsSerialized()) {
            $conditions = $this->getConditionsSerialized();
            if (!empty($conditions)) {
                $conditions = $this->serializer->unserialize($conditions);
                if (is_array($conditions) && !empty($conditions)) {
                    $this->_conditions->loadArray($conditions);
                }
            }
            $this->unsConditionsSerialized();
        }

        return $this->_conditions;
    }

    /**
     * Check if we can use mapping for rule conditions
     *
     * @return bool
     */
    private function canPreMapProducts()
    {
        $conditions = $this->getConditions();

        // No need to map products if there is no conditions in rule
        if (!$conditions || !$conditions->getConditions()) {
            return false;
        }

        return true;
    }

    /**
     * Reset rule combine conditions
     *
     * @param null|\Magento\Rule\Model\Condition\Combine $conditions
     * @return $this
     */
    protected function _resetConditions($conditions = null)
    {
        if (null === $conditions) {
            $conditions = $this->getConditionsInstance();
        }
        $conditions->setRule($this)->setId('1')->setPrefix('conditions');
        $this->setConditions($conditions);

        return $this;
    }

    /**
     * Getter for rule conditions collection
     *
     * @return \Magento\Rule\Model\Condition\Combine
     */
    public function getConditionsInstance()
    {
        return $this->_combineFactory->create();
    }

    /**
     * Set rule combine conditions model
     *
     * @param Combine $conditions
     * @return $this
     */
    public function setConditions($conditions)
    {
        $this->_conditions = $conditions;
        return $this;
    }

    /**
     * Initialize rule model data from array
     *
     * @param array $data
     * @return $this
     */
    public function loadPost(array $data)
    {
        $arr = $this->_convertFlatToRecursive($data);
        if (isset($arr['conditions'])) {
            $this->getConditions()->setConditions([])->loadArray($arr['conditions'][1]);
        }

        return $this;
    }

    /**
     * Rule form getter
     *
     * @return \Magento\Framework\Data\Form
     */
    public function getForm()
    {
        if (!$this->_form) {
            $this->_form = $this->_formFactory->create();
        }
        return $this->_form;
    }

    /**
     * Set specified data to current rule.
     * Set conditions and actions recursively.
     * Convert dates into \DateTime.
     *
     * @param array $data
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function _convertFlatToRecursive(array $datas)
    {
        $arr = [];
        foreach ($datas as $key => $value) {
            if (($key === 'conditions' || $key === 'actions') && is_array($value)) {
                foreach ($value as $id => $datas) {
                    $path = explode('--', $id);
                    $node = & $arr;
                    for ($i = 0, $l = count($path); $i < $l; $i++) {
                        if (!isset($node[$key][$path[$i]])) {
                            $node[$key][$path[$i]] = [];
                        }
                        $node = & $node[$key][$path[$i]];
                    }
                    foreach ($datas as $k => $v) {
                        $node[$k] = $v;
                    }
                }
            }
        }

        return $arr;
    }
}
