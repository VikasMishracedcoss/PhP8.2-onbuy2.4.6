<?php

namespace Ced\Integrator\Block\Adminhtml\Dashboard\Order;

class MonthlySale extends \Magento\Backend\Block\Widget\Grid\Extended
{

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Ced\Integrator\Helper\OrderData $orderFactory,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        array $data = []
    ) {
        
        $this->orderFactory = $orderFactory;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->moduleManager = $moduleManager;
        parent::__construct($context, $backendHelper, $data);
    }
    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        $collection = $this->orderFactory->bestSellerProduct();
        $List = implode(',', $collection);
        $productIds = [$List];
        $total = $this->productCollectionFactory->create()->addAttributeToSelect('*')
            ->addAttributeToFilter('sku', ['in' => $collection])
            ->addAttributeToFilter('name', ['notnull' => true]);
        if (count($collection) > 1) {
            $total->getselect()->order(new \Zend_Db_Expr('FIELD(sku,' . implode(',', $productIds) . ')'));
        }
        $this->setCollection($total);
        parent::_prepareCollection();
        return $this;
    }

    /**
     * @return $this
     */
    protected function _prepareColumns()
    {

        $this->addColumn(
            'entity_id',
            [
                'header' => __('Product Id'),
                'sortable' => false,
                'index' => 'entity_id',
            ]
        );

        $this->addColumn(
            'name',
            [
                'header' => __('Product Name'),
                'sortable' => false,
                'index' => 'name',
            ]
        );
        $this->addColumn(
            'type_id',
            [
                'header' => __('Product Name'),
                'sortable' => false,
                'index' => 'type_id',
            ]
        );
        $this->addColumn(
            'sku',
            [
                'header' => __('Product Sku'),
                'sortable' => false,
                'index' => 'sku',

            ]
        );

        $this->setFilterVisibility(false);
        $this->setPagerVisibility(false);
        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('catalog/product/edit/', ['id' => $row->getId()]);
    }
}
