<?php
namespace Ced\Onbuy\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @var \Magento\Framework\DB\Ddl\TriggerFactory
     */
    protected $triggerFactory;

    public function __construct(
        \Magento\Framework\DB\Ddl\TriggerFactory $triggerFactory
    )
    {
        $this->triggerFactory = $triggerFactory;
    }


    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        //Your code for upgrade data base
        $installer = $setup;
        $installer->startSetup();

        $triggerName = 'onbuy_stock';
        $event = 'UPDATE';

        /** @var \Magento\Framework\DB\Ddl\Trigger $trigger */
        $trigger = $this->triggerFactory->create()
            ->setName($triggerName)
            ->setTime(\Magento\Framework\DB\Ddl\Trigger::TIME_AFTER)
            ->setEvent($event)
            ->setTable($setup->getTable('cataloginventory_stock_item'));

        $trigger->addStatement("IF OLD.qty <> new.qty THEN
        INSERT INTO onbuy_product_change(product_id,old_value,new_value,action,cron_type,account_id,threshold_limit)VALUES(old.product_id, old.qty, new.qty,'update','inventory','1','0');END IF");

        $setup->getConnection()->dropTrigger($trigger->getName());
        $setup->getConnection()->createTrigger($trigger);

        $installer->endSetup();

    }
}