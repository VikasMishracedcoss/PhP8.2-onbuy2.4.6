<?php
namespace Ced\Onbuy\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;

class UpgradeData implements UpgradeDataInterface {
    private $eavSetupFactory;
    public $eavAttribute;
    public $directoryList;
    public $objectManager;

    public function __construct(
        EavSetupFactory $eavSetupFactory,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Catalog\Model\ResourceModel\Eav\Attribute $eavAttribute,
        \Magento\Framework\Filesystem\DirectoryList $directoryList,
        \Magento\Framework\App\State $state
    ) {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->objectManager = $objectManager;
        $this->eavAttribute = $eavAttribute;
        $this->directoryList = $directoryList;
        $state->setAreaCode('adminhtml');
    }
    public function upgrade( ModuleDataSetupInterface $setup, ModuleContextInterface $context ) {
        $installer = $setup;
        $appPath = $this->directoryList->getRoot();
       if ( version_compare( $context->getVersion(), '1.0.4', '<' ) ) {
              /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

        /**
         * Add attributes to the eav/attribute
         */
        $groupName = 'Onbuy';
        $entityTypeId = $eavSetup->getEntityTypeId(\Magento\Catalog\Model\Product::ENTITY);
        $attributeSetId = $eavSetup->getDefaultAttributeSetId($entityTypeId);
        $eavSetup->addAttributeGroup($entityTypeId, $attributeSetId, $groupName, 1000);
        $eavSetup->getAttributeGroupId($entityTypeId, $attributeSetId, $groupName);

        $eavSetup->addAttribute('catalog_product', 'onbuy_product_condition', [
            'group'            => 'Onbuy',
            'input'            => 'select',
            'type'             => 'varchar',
            'label'            => 'Onbuy Product Condition',
            'backend'          => '',
            'visible'          => 1,
            'required'         => 0,
            'sort_order'       => 2,
            'user_defined'     => 1,
            'source'           => 'Ced\Onbuy\Model\Source\Product\Condition',
            'searchable'       => 1,
            'visible_on_front' => 0,
            'global'           => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
        ]
    );

    // $dataHelper = $this->objectManager->create('\Ced\Onbuy\Helper\Data');
    $storeManager = $this->objectManager->get('\Magento\Store\Model\StoreManagerInterface');
   // $dataHelper->registerDomain($storeManager->getStore()->getBaseUrl());

        }
    }
}