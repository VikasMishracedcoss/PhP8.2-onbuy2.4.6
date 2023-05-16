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
use Magento\CatalogRule\Model\ResourceModel\Product\ConditionsToCollectionApplier;
use Magento\CatalogRule\Model\Rule\Condition\CombineFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Serialize\Serializer\Json;

class ProductChangeLog extends \Magento\Framework\Model\AbstractModel
{
    const TABLE_NAME = 'ced_integrator_product_change_log';
    const ID_FIELD_NAME = 'id';

    const ACTION_TYPE_ASSIGN = 'assign';

    /**
     * @var \Magento\Framework\App\ResourceConnection $resource
     */
    protected $resource;

    /** @var \Magento\Framework\DB\Adapter\AdapterInterface $connection */
    protected $connection;

    /** @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $_productCollectionFactory */
    protected $_productCollectionFactory;

    /** @var \Ced\Integrator\Model\ResourceModel\ProductChangeLog\Collection $productChange */
    protected $productChange;

    /** @var \Ced\Integrator\Helper\Logger $logger */
    public $logger;
    public $productDataHelper;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Ced\Integrator\Helper\Logger $logger,
        \Ced\Integrator\Model\ResourceModel\ProductChangeLog\Collection $productChangeCollection,
        ResourceConnection $resourceConnection,
        \Ced\Integrator\Helper\ProductData $productDataHelper,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->resource = $resourceConnection;
        $this->logger = $logger;
        $this->productChange = $productChangeCollection;
        $this->connection = $resourceConnection->getConnection();
        $this->productDataHelper = $productDataHelper;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        //$this->_init(\Ced\Integrator\Model\ResourceModel\QueryCondition::class);
    }

    /**
     * @return  void
     */
    public function _construct()
    {
        $this->_init(\Ced\Integrator\Model\ResourceModel\ProductChangeLog::class);
    }

    public function insertChangedProductIds($prodIds = [], $type = \Ced\Integrator\Model\ProductChangeLog::ACTION_TYPE_ASSIGN)
    {
        try {
            $productChangeData = [];
            $alreadyProductChangeIds = $this->productChange
                ->addFieldToFilter('product_id', ['in' => $prodIds])
                ->getColumnValues('product_id');
            foreach ($prodIds as $prodId) {
                if(!in_array($prodId, $alreadyProductChangeIds))
                    $productChangeData[] = [
                        'product_id' => $prodId,
                        'action' => $type,
                    ];
            }
            if(is_array($productChangeData) && count($productChangeData) > 0) {
                $tableName = \Ced\Integrator\Model\ProductChangeLog::TABLE_NAME;
                $this->insertMultiple($tableName, $productChangeData);
            }
            return true;
        } catch (\Exception $e) {
            $this->logger->addError('Inside Category Change Product Observer Bulk', ['path' => __METHOD__, 'Error' => $e->getMessage()]);
            return false;
        }
    }

    public function insertMultiple($table, $data)
    {
        try {
            $this->productDataHelper->insertMultipleRows($data);
            //$tableName = $this->resource->getTableName($table);
            //return $this->connection->insertMultiple($tableName, $data);
        } catch (\Exception $e) {
            $this->logger->addError('Inside Category Change Product Observer Bulk', ['path' => __METHOD__, 'Error' => $e->getMessage()]);
        }
    }
}
