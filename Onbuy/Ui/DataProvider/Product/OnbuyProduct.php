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
 * @package   Ced_Onbuy
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Onbuy\Ui\DataProvider\Product;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\Api\FilterBuilder;

/**
 * Class OnbuyProduct
 * @package Ced\Onbuy\Ui\DataProvider\Product
 */
class OnbuyProduct extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var
     */
    public $collection;

    public $profileIdAccAttr;

    public $store;

    /**
     * @var array
     */
    public $addFieldStrategies;

    /**
     * @var array
     */
    public $addFilterStrategies;
    /**
     * @var FilterBuilder
     */
    public $filterBuilder;
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    public $objectManager;

    /**
     * @var \Magento\Framework\Registry
     */
    public $_coreRegistry;

    /**
     * @var \Ced\Onbuy\Helper\MultiAccount
     */
    protected $multiAccountHelper;

    /**
     * @var \Ced\Onbuy\Helper\Data
     */
    protected $dataHelper;

    protected $profileProducts;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        FilterBuilder $filterBuilder,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Ced\Onbuy\Model\ResourceModel\Profileproducts\CollectionFactory $profileProducts,
        \Ced\Onbuy\Helper\MultiAccount $multiAccountHelper,
        \Ced\Onbuy\Helper\Data $dataHelper,
        \Magento\Framework\Registry $_coreRegistry,
        array $addFieldStrategies = [],
        array $addFilterStrategies = [],
        array $meta = [],
        array $data = []
    ) {
        try {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
            $this->dataHelper = $dataHelper;
            $this->profileProducts = $profileProducts;
            $accountId = $this->dataHelper->getAccountSession();
            $this->multiAccountHelper = $multiAccountHelper;
            $account = $this->multiAccountHelper->getAccountRegistry($accountId);
            $this->_coreRegistry = $_coreRegistry;
            $accountId = 0;
            if ($account->getId()) {
                $accountId = $account->getId();
            }
            $prodStatusAccAttr = $this->multiAccountHelper->getProdStatusAttrForAcc($accountId);
            $prodListingAccAttr = $this->multiAccountHelper->getProdListingIdAttrForAcc($accountId);
            $listingErrorAccAttr = $this->multiAccountHelper->getProdListingErrorAttrForAcc($accountId);
            $this->profileIdAccAttr = 'onbuy_profile_id';
            $store = $account->getAccountStore();
           
            $this->filterBuilder = $filterBuilder;
            $this->objectManager = $objectManager;

            $pids = $this->objectManager->create('Ced\Onbuy\Model\Profile')->getCollection()->addFieldToFilter('profile_status', 1)->getColumnValues('id');

            $dumy_collection = $collectionFactory->create();
            $dumy_collection->joinField('category_id', 'catalog_category_product', 'category_id', 'product_id = entity_id', null);

            $pProducts = $this->profileProducts->create()->addFieldToFilter('account_id', $accountId)
                ->addFieldToFilter('profile_id', ['in' => $pids])->addFieldToSelect('product_id')->getData();

            $this->collection = $collectionFactory->create();
            $this->collection->addFieldToSelect(array($listingErrorAccAttr, /*$itemIdAccAttr,*/ $this->profileIdAccAttr, $prodStatusAccAttr));
            $this->collection->joinField('qty', 'cataloginventory_stock_item', 'qty', 'product_id = entity_id', '{{table}}.stock_id=1', null)->setStoreId($store);
           // $this->collection->addAttributeToFilter('visibility', ['neq' => 1]);
            $this->collection->addAttributeToFilter('entity_id', ['in' => $pProducts]);
            $this->addField($prodStatusAccAttr);
           
            $this->addField($listingErrorAccAttr);
            $this->collection->joinField('onbuy_profile_id', 'onbuy_profile_products', 'profile_id', 'product_id = entity_id', '{{table}}.account_id='. $accountId, null);
            $this->collection->joinField('onbuy_product_status', 'onbuy_profile_products', 'product_status', 'product_id = entity_id', '{{table}}.account_id='. $accountId, null);
            $this->collection->joinField('onbuy_listing_error', 'onbuy_profile_products', 'listing_error', 'product_id = entity_id', '{{table}}.account_id='. $accountId, null);
            $this->collection->joinField('opc', 'onbuy_profile_products', 'opc', 'product_id = entity_id', '{{table}}.account_id='. $accountId, null);

            //$this->collection->joinAttribute('onbuy_listing_id', "catalog_product/$itemIdAccAttr", 'entity_id', null, 'left');
            $this->collection->joinAttribute('onbuy_listing_error', "catalog_product/$listingErrorAccAttr", 'entity_id', null, 'left');
            $this->collection->joinAttribute('onbuy_product_status', "catalog_product/$prodStatusAccAttr", 'entity_id', null, 'left');
            $this->collection->joinAttribute('onbuy_profile_id', "catalog_product/$this->profileIdAccAttr", 'entity_id', null, 'left');
            $this->collection->joinAttribute('onbuy_listing_id', "catalog_product/$prodListingAccAttr", 'entity_id', null, 'left');

            $this->addFilter($this->filterBuilder->setField($this->profileIdAccAttr)->setConditionType('notnull')
                ->setValue('true')
                ->create());
            $this->addFilter($this->filterBuilder->setField($this->profileIdAccAttr)->setConditionType('in')
                ->setValue($pids)
                ->create());
            $this->addFilter($this->filterBuilder->setField($this->profileIdAccAttr)->setConditionType('neq')
                ->setValue(0)
                ->create());
            $this->addFilter($this->filterBuilder->setField('account_store')->setConditionType('eq')
                ->setValue($store)
                ->create());
            $this->addFilter($this->filterBuilder->setField('type_id')->setConditionType('in')
                ->setValue(['simple', 'configurable'])
                ->create());
            $this->addFilter($this->filterBuilder->setField('visibility')->setConditionType('neq')
                ->setValue(1)
                ->create());
            $this->addFieldStrategies = $addFieldStrategies;
            $this->addFilterStrategies = $addFilterStrategies;
        } catch (\Exception $e) {
           //print_r($e->getMessage()); die(__DIR__);
        }
    }

    /**
     * @return array
     */
    public function getData()
    {
        if (!$this->getCollection()->isLoaded()) {
            $this->getCollection()->load();
        }
        $items = $this->getCollection()->toArray();
        // echo '<pre>';
        // print_r($items);
        // die(__FILE__);
        if ($this->profileIdAccAttr == '') {
            return [ 'totalRecords' => 0, 'items' => []];
        } else {

            return [
                'totalRecords' => $this->getCollection()->getSize(),
                'items' => array_values($items),
            ];
        }
    }

    public function pr()
    {
        if (!$this->getCollection()->isLoaded()) {
            $this->getCollection()->load();
        }
        $items = $this->getCollection()->toArray();
        // echo '<pre>';
        // print_r($items);
        // die(__FILE__);
        if ($this->profileIdAccAttr == '') {
            return [ 'totalRecords' => 0, 'items' => []];
        } else {

            return [
                'totalRecords' => $this->getCollection()->getSize(),
                'items' => array_values($items),
            ];
        }
    }
    /**
     * @param \Magento\Framework\Api\Filter $filter
     */
    public function addFilter(\Magento\Framework\Api\Filter $filter)
    {
        if (isset($this->addFilterStrategies[$filter->getField()])) {
            $this->addFilterStrategies[$filter->getField()]
                ->addFilter(
                    $this->getCollection(),
                    $filter->getField(),
                    [$filter->getConditionType() => $filter->getValue()]
                );
        } else {
            parent::addFilter($filter);
        }
    }

    /**
     * @param array|string $field
     * @param null $alias
     */
    public function addField($field, $alias = null)
    {
        if (isset($this->addFieldStrategies[$field])) {
            $this->addFieldStrategies[$field]->addField($this->getCollection(), $field, $alias);
        } else {
            parent::addField($field, $alias);
        }
    }
}