<?php

namespace Ced\Integrator\Block\Adminhtml\Dashboard\Order;

class ReturnOrder extends \Magento\Backend\Block\Widget\Grid\Extended
{

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Ced\Integrator\Helper\OrderData $orderFactory,
        \Magento\Framework\Module\Manager $moduleManager,
        array $data = []
    ) {
        $this->orderFactory = $orderFactory;
        $this->moduleManager = $moduleManager;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        $collection = $this->orderFactory->returnOrderDetails()->setPageSize(5);
        $this->setCollection($collection);
        return $this;
    }

    /**
     * @return $this
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'increment_id',
            [
                'header' => __('Order Id'),
                'sortable' => false,
                'index' => 'increment_id',
            ]
        );

        $this->addColumn(
            'customer_firstname',
            [
                'header' => __('Customer Name'),
                'sortable' => false,
                'index' => 'customer_firstname',
            ]
        );

        $this->addColumn(
            'customer_email',
            [
                'header' => __('Customer Email'),
                'sortable' => false,
                'index' => 'customer_email',
            ]
        );

        $this->addColumn(
            'base_grand_total',
            [
                'header' => __('Grand Total'),
                'sortable' => false,
                'index' => 'base_grand_total',
                'type' => 'currency',
                'currency_code' => (string)$this->_storeManager->getStore(
                    (int)$this->getParam('store')
                )->getBaseCurrencyCode(),
            ]
        );

        $this->setFilterVisibility(false);
        $this->setPagerVisibility(false);
        return parent::_prepareColumns();
    }
    
    public function getRowUrl($row)
    {
        return $this->getUrl('sales/order/view', ['order_id' => $row->getId()]);
    }
}
