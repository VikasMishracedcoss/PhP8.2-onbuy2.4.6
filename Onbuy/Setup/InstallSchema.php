<?php


namespace Ced\Onbuy\Setup;


use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\DB\Adapter\AdapterInterface;

/**
 * Class InstallSchema
 * @package Ced\Onbuy\Setup
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        $tableName = $installer->getTable('onbuy_orders');
        if ($setup->getConnection()->isTableExists($tableName) != true) {
            /**
             * Create table 'onbuy_orders'
             */
            $table = $installer->getConnection()->newTable($tableName)
                ->addColumn(
                    'id',
                    Table::TYPE_INTEGER,
                    null,
                    array(
                        'identity' => true,
                        'unsigned' => true,
                        'nullable' => false,
                        'primary' => true,
                        'auto_increment' => true,
                    ),
                    'Id'
                )
                ->addColumn(
                    'merchant_order_id',
                    Table::TYPE_TEXT,
                    50,
                    array(
                        'nullable' => false,
                    ),
                    'Merchant Order Id'
                )
                ->addColumn(
                    'onbuy_order_id',
                    Table::TYPE_TEXT,
                    50,
                    array(
                        'nullable' => false,
                    ),
                    'Onbuy Order Id'
                )
                ->addColumn(
                    'deliver_by',
                    Table::TYPE_TEXT,
                    50,
                    array(
                        'nullable' => false,
                    ),
                    'Deliver By'
                )
                ->addColumn(
                    'magento_increment_id',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => true, 'default' => '', 'unsigned' => true],
                    'Magento Order Id'
                )
                ->addColumn(
                    'order_place_date',
                    Table::TYPE_DATE,
                    50,
                    null,
                    ['nullable' => false],
                    'Order Place Date'
                )
                ->addColumn(
                    'status',
                    Table::TYPE_TEXT,
                    50,
                    array(
                        'nullable' => true,
                    ),
                    'Status'
                )
                ->addColumn(
                    'order_data',
                    Table::TYPE_TEXT,
                    null,
                    array(
                        'nullable' => true,
                    ),
                    'Order Data'
                )
                ->addColumn(
                    'shipment_data',
                    Table::TYPE_TEXT,
                    null,
                    array(
                        'nullable' => true,
                    ),
                    'Shipment Data'
                )
                ->addColumn(
                    'failed_order_reason',
                    Table::TYPE_TEXT,
                    null,
                    array(
                        'nullable' => true,
                    ),
                    'Failed Order Reason'
                )
                ->addColumn(
                    'reference_order_id',
                    Table::TYPE_TEXT,
                    null,
                    array(
                        'nullable' => true,
                    ),
                    'reference_order_id'
                )->addColumn(
                    'customer_cancelled',
                    Table::TYPE_SMALLINT,
                    1,
                    array(
                        'nullable' => true,
                    ),
                    'customer_cancelled'
                )
                ->addColumn(
                    'account_id',
                    Table::TYPE_INTEGER,
                    null,
                    array(
                        'nullable' => true,
                    ),
                    'Account Id'
                );
            $installer->getConnection()->createTable($table);
        }

        $tableName = $setup->getTable('onbuy_product_change');
        if ($setup->getConnection()->isTableExists($tableName) != true) {
            /**
             * Create table 'onbuy_product_change'
             */
            $table = $setup->getConnection()->newTable($tableName)
                ->addColumn(
                    'id',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'identity' => true,
                        'unsigned' => true,
                        'nullable' => false,
                        'primary' => true
                    ],
                    'ID'
                )
                ->addColumn(
                    'product_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => false],
                    'Profile Status'
                )
                ->addColumn(
                    'last_update',
                    Table::TYPE_TEXT,
                    50,
                    ['nullable' => false,'default' => ''],
                    'Last Update in Product on onbuy'
                )
                ->addColumn(
                    'old_value',
                    Table::TYPE_TEXT,
                    50,
                    ['nullable' => true, 'default' => ''],
                    'Old Value'
                )
                ->addColumn(
                    'new_value',
                    Table::TYPE_TEXT,
                    50,
                    ['nullable' => true, 'default' => ''],
                    'New Value'
                )
                ->addColumn(
                    'action',
                    Table::TYPE_TEXT,
                    50,
                    ['nullable' => true, 'default' => ''],
                    'Action'
                )
                ->addColumn(
                    'cron_type',
                    Table::TYPE_TEXT,
                    50,
                    ['nullable' => true, 'default' => ''],
                    'Cron type'
                )
                ->addColumn(
                    'account_id',
                    Table::TYPE_INTEGER,
                    50,
                    ['nullable' => true, 'default' => 0],
                    'Account Id'
                )
                ->addColumn(
                    'threshold_limit',
                    Table::TYPE_INTEGER,
                    50,
                    ['nullable' => true, 'default' => 0],
                    'Threshold Limit'
                )
                ->setComment('Onbuy Product Change')->setOption('type', 'InnoDB')->setOption('charset', 'utf8');

            $setup->getConnection()->createTable($table);
        }

        $tableName = $installer->getTable('onbuy_feeds');
        if ($setup->getConnection()->isTableExists($tableName) != true) {
            /**
             * Create table 'onbuy_feeds'
             */
            $table = $installer->getConnection()->newTable($tableName)
                ->addColumn(
                    'id',
                    Table::TYPE_INTEGER,
                    null,
                    array(
                        'identity' => true,
                        'unsigned' => true,
                        'nullable' => false,
                        'primary' => true,
                        'auto_increment' => true,
                    ),
                    'Id'
                )
                ->addColumn(
                    'queue_id',
                    Table::TYPE_TEXT,
                    100,
                    array(
                        'nullable' => false,
                    ),
                    'Queue Id'
                )
                ->addColumn(
                    'account_id',
                    Table::TYPE_TEXT,
                    100,
                    array(
                        'nullable' => false,
                    ),
                    'Account Id'
                )
                ->addColumn(
                    'product_skus',
                    Table::TYPE_TEXT,
                    100,
                    array(
                        'nullable' => false,
                    ),
                    'Product Ids'
                )
                ->addColumn(
                    'created_at',
                    Table::TYPE_DATETIME,
                    null,
                    array(
                        'nullable' => true,
                    ),
                    'Created at'
                )
                ->addColumn(
                    'updated_at',
                    Table::TYPE_DATETIME,
                    50,
                    array(
                        'nullable' => false,
                    ),
                    'Updated at'
                )
                ->addColumn(
                    'onbuy_file_id',
                    Table::TYPE_TEXT,
                    100,
                    array(
                        'nullable' => false,
                    ),
                    'Onbuy file Id'
                )
                ->addColumn(
                    'file_data',
                    Table::TYPE_TEXT,
                    null,
                    array(
                        'nullable' => false,
                    ),
                    'File data'
                )
                ->addColumn(
                    'file_name',
                    Table::TYPE_TEXT,
                    null,
                    array(
                        'nullable' => false,
                    ),
                    'File Name'
                )
                ->addColumn(
                    'file_type',
                    Table::TYPE_TEXT,
                    null,
                    array(
                        'nullable' => false,
                    ),
                    'File type'
                )
                ->addColumn(
                    'status',
                    Table::TYPE_TEXT,
                    null,
                    array(
                        'nullable' => false,
                    ),
                    'status'
                )->addColumn(
                    'error',
                    Table::TYPE_TEXT,
                    null,
                    array(
                        'nullable' => false,
                    ),
                    'error'
                )->addColumn(
                    'parent',
                    Table::TYPE_TEXT,
                    null,
                    array(
                        'nullable' => true,
                    ),
                    'error'
                )->addColumn(
                    'response',
                    Table::TYPE_TEXT,
                    null,
                    array(
                        'nullable' => false,
                    ),
                    'response'
                );
            $installer->getConnection()->createTable($table);
        }

        $tableName = $setup->getTable('onbuy_profile');
        if ($setup->getConnection()->isTableExists($tableName) != true) {
            /**
             * Create table 'onbuy_profile'
             */
            $table = $setup->getConnection()->newTable($tableName)
                ->addColumn(
                    'id',
                    Table::TYPE_INTEGER,
                    null,
                    array(
                        'identity' => true,
                        'unsigned' => true,
                        'nullable' => false,
                        'primary' => true,
                        'auto_increment' => true,
                    ),
                    'Id'
                )
                ->addColumn(
                    'profile_code',
                    Table::TYPE_TEXT,
                    50,
                    array(
                        'nullable' => false,
                    ),
                    'Profile Code'
                )
                ->addColumn(
                    'profile_status',
                    Table::TYPE_BOOLEAN,
                    null,
                    array(
                        'nullable' => false,
                    ),
                    'Product Status'
                )
                ->addColumn(
                    'profile_name',
                    Table::TYPE_TEXT,
                    50,
                    array(
                        'nullable' => false,
                    ),
                    'Product Name'
                )
                ->addColumn(
                    'profile_category',
                    Table::TYPE_TEXT,
                    null,
                    array(
                        'nullable' => false,
                    ),
                    'Product Category'
                )
                ->addColumn(
                    'profile_cat_search',
                    Table::TYPE_TEXT,
                    null,
                    array(
                        'nullable' => false,
                    ),
                    'Profile Category Search'
                )
                ->addColumn(
                    'opt_req_attribute',
                    Table::TYPE_TEXT,
                    null,
                    array(
                        'nullable' => false,
                    ),
                    'Required-Optional Attribute Mapping'
                )
                ->addColumn(
                    'cat_depend_attribute',
                    Table::TYPE_TEXT,
                    null,
                    array(
                        'nullable' => false,
                    ),
                    'Category Dependent Attribute Mapping'
                )
                ->addColumn(
                    'account_id',
                    Table::TYPE_INTEGER,
                    50,
                    array(
                        'nullable' => true,
                    ),
                    'Account Id'
                );

            $setup->getConnection()->createTable($table);
        }

        if (!$setup->getConnection()->isTableExists($setup->getTable('onbuy_invetorycron'))) {
            $table = $setup->getConnection()
                ->newTable($setup->getTable('onbuy_invetorycron'))
                ->addColumn(
                    'id',
                    Table::TYPE_INTEGER,
                    null,
                    array(
                        'identity' => true,
                        'unsigned' => true,
                        'nullable' => false,
                        'primary' => true,
                        'auto_increment' => true,
                    ),
                    'Id'
                )
                ->addColumn(
                    'product_ids',
                    Table::TYPE_TEXT,
                    null, array(
                    'nullable' => false,
                ), 'Product Ids'
                )
                ->addColumn(
                    'start_time',
                    Table::TYPE_DATETIME,
                    null,
                    array(
                        'nullable' => true,
                    ),
                    'Start Time'
                )
                ->addColumn(
                    'finish_time',
                    Table::TYPE_DATETIME,
                    null,
                    array(
                        'nullable' => true,
                    ),
                    'Finish Time'
                )
                ->addColumn(
                    'cron_status',
                    Table::TYPE_TEXT,
                    25, array(
                    'nullable' => true,
                ), 'Cron Status'
                )
                ->addColumn(
                    'message',
                    Table::TYPE_TEXT,
                    500, array(
                    'nullable' => true,
                ), 'Error'
                );
            $setup->getConnection()->createTable($table);
            $setup->endSetup();
        }


        $tableName = $installer->getTable('onbuy_profile_products');
        if ($setup->getConnection()->isTableExists($tableName) != true) {
            $table = $installer->getConnection()
                ->newTable($tableName)
                ->addColumn(
                    'id',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'identity' => true,
                        'unsigned' => true,
                        'nullable' => false,
                        'primary' => true
                    ],
                    'ID'
                )
                ->addColumn(
                    'product_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => false],
                    'Product Id'
                )
                ->addColumn(
                    'profile_id',
                    Table::TYPE_INTEGER,
                    10,
                    ['unsigned' => true, 'nullable' => false],
                    'Profile Id'
                )
                ->addColumn(
                    'account_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => false],
                    'account Id'
                )
                /*->addColumn(
                    \Ced\EbayMultiAccount\Model\Profile\Product::COLUMN_ITEM_ID,
                    Table::TYPE_BIGINT,
                    null,
                    ['unsigned' => true, 'nullable' => true],
                    'Item Id'
                )*/
                ->addColumn(
                    'product_status',
                    Table::TYPE_TEXT,
                    null,
                    ['unsigned' => true, 'nullable' => true],
                    'Product Status'
                )
                ->addColumn(
                    'listing_error',
                    Table::TYPE_TEXT,
                    '2M',
                    ['unsigned' => true, 'nullable' => true],
                    'Listing Error'
                )
                ->addColumn(
                    'queue_id',
                    Table::TYPE_TEXT,
                    '2M',
                    ['unsigned' => true, 'nullable' => true],
                    'queue_id'
                )
                ->addColumn(
                    'opc',
                    Table::TYPE_TEXT,
                    '2M',
                    ['unsigned' => true, 'nullable' => true],
                    'OPC'
                )
                ->addColumn(
                    'child_opc',
                    Table::TYPE_TEXT,
                    '2M',
                    ['unsigned' => true, 'nullable' => true],
                    'Child OPC'
                )
                 ->addColumn(
                    'last_update',
                    Table::TYPE_TEXT,
                    '2M',
                    ['unsigned' => true, 'nullable' => true],
                    'last update'
                )
                  ->addColumn(
                    'last_opration',
                    Table::TYPE_TEXT,
                    '2M',
                    ['unsigned' => true, 'nullable' => true],
                    'last Opration'
                )
                ->addForeignKey(
                    $installer->getFkName(
                        'onbuy_profile_product',
                        'product_id',
                        'catalog_product_entity',
                        'entity_id'
                    ),
                    'product_id',
                    $installer->getTable('catalog_product_entity'),
                    'entity_id',
                    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
                )
                ->addForeignKey(
                    $installer->getFkName(
                        'onbuy_profile_product',
                        'profile_id',
                        'onbuy_profile',
                        'id'
                    ),
                    'profile_id',
                    $installer->getTable('onbuy_profile'),
                    'id',
                    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
                )->addIndex(
                    $installer->getIdxName(
                        $tableName,
                        [
                            'profile_id',
                            'product_id'
                        ],
                        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                    ),
                    [
                        'profile_id',
                        'product_id'
                    ],
                    ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
                )->addIndex(
                    $installer->getIdxName(
                        $tableName,
                        [
                            'profile_id',
                            'product_id'
                        ],
                        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                    ),
                    [
                        'profile_id',
                        'product_id'
                    ],
                    ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
                )
                ->setComment('Onbuy Profile Product');
            $installer->getConnection()->createTable($table);
        }


        if (!$setup->getConnection()->isTableExists($setup->getTable('onbuy_accounts'))) {
            $setup->startSetup();
            $table = $setup->getConnection()->newTable($setup->getTable('onbuy_accounts'))
                ->addColumn(
                    'id',
                    Table::TYPE_INTEGER,
                    null,
                    array(
                        'identity' => true,
                        'unsigned' => true,
                        'nullable' => false,
                        'primary' => true,
                        'auto_increment' => true,
                    ),
                    'Id'
                )
                ->addColumn(
                    'account_code',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => false, 'default' => ''],
                    'Account Code'
                )
                ->addColumn(
                    'account_env',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => true, 'default' => ''],
                    'Account Environment'
                )
                ->addColumn(
                    'account_store',
                    Table::TYPE_TEXT,
                    50,
                    ['nullable' => true, 'default' => ''],
                    'Account Store'
                )
                ->addColumn(
                    'consumer_key',
                    Table::TYPE_TEXT,
                    50,
                    ['nullable' => true, 'default' => ''],
                    'Consumer Key'
                )
                ->addColumn(
                    'consumer_secret',
                    Table::TYPE_TEXT,
                    50,
                    ['nullable' => true, 'default' => ''],
                    'Consumer Secret'
                )->addColumn(
                    'seller_id',
                    Table::TYPE_TEXT,
                    50,
                    ['nullable' => true, 'default' => ''],
                    'seller_id'
                )->addColumn(
                    'seller_entity_id',
                    Table::TYPE_TEXT,
                    50,
                    ['nullable' => true, 'default' => ''],
                    'seller_entity_id'
                )->addColumn(
                    'site_id',
                    Table::TYPE_TEXT,
                    50,
                    ['nullable' => true, 'default' => ''],
                    'site Id'
                )
                ->addColumn(
                    'access_token',
                    Table::TYPE_TEXT,
                    50,
                    ['nullable' => true, 'default' => ''],
                    'access_token'
                )
                ->addColumn(
                    'token_expiration',
                    Table::TYPE_TEXT,
                    50,
                    ['nullable' => true, 'default' => ''],
                    'access_token'
                )

                ->addColumn(
                    'account_status',
                    Table::TYPE_BOOLEAN,
                    null,
                    ['nullable' => true,],
                    'Account Status'
                );
            $setup->getConnection()->createTable($table);
            $setup->endSetup();
        }
        if (!$setup->getConnection()->isTableExists($setup->getTable('onbuy_queue'))) {
            $table = $setup->getConnection()
                ->newTable($setup->getTable('onbuy_queue'))
                ->addColumn(
                    'id',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'identity' => true,
                        'unsigned' => true,
                        'nullable' => false,
                        'primary' => true,
                        'auto_increment' => true,
                    ],
                    'Id'
                )
                ->addColumn(
                    'product_ids',
                    Table::TYPE_TEXT,
                    "2M",
                    array(
                        'nullable' => false,
                        'default' => null
                    ),
                    'Product Ids'
                )
                ->addColumn(
                    'start_time',
                    Table::TYPE_DATETIME,
                    null,
                    array(
                        'nullable' => false,
                    ),
                    'Start Time'
                )
                ->addColumn(
                    'finish_time',
                    Table::TYPE_DATETIME,
                    null,
                    array(
                        'nullable' => false,
                    ),
                    'Finish Time'
                )

                ->addColumn(
                    'cron_type',
                    Table::TYPE_TEXT,
                    null,
                    array(
                        'nullable' => true,
                        'default' => null
                    ),
                    'Cron Type'
                )
                ->addColumn(
                    'cron_status',
                    Table::TYPE_TEXT,
                    null,
                    array(
                        'nullable' => true,
                        'default' => null
                    ),
                    'Cron Status'
                )
                ->addColumn(
                    'feed_file_path',
                    Table::TYPE_TEXT,
                    null,
                    array(
                        'nullable' => false,
                        'default' => null
                    ),
                    'Path Of Feed File'
                )
                ->addColumn(
                    'error',
                    Table::TYPE_TEXT,
                    null,
                    array(
                        'nullable' => true,
                        'default' => null
                    ),
                    'Errors'
                )
                ->addColumn(
                    'account_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => true],
                    'Account Id'
                )->addColumn(
                    'threshold',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => true],
                    'Account Id'
                );
            $setup->getConnection()->createTable($table);
            $setup->endSetup();
        }


    }
}