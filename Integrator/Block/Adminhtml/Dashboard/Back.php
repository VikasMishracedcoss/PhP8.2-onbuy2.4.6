<?php
namespace Ced\Integrator\Block\Adminhtml\Dashboard;

class Back extends \Magento\Backend\Block\Widget\Container
{
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        array $data = []
    ) {
        $this->_request = $context->getRequest();
        parent::__construct($context, $data);
    }
    
    protected function _construct()
    {
        $this->addButton(
            'preview_product',
            $this->getButtonData()
        );
        parent::_construct();
    }
    
    /**Function
     * To Return
     * Back Button For
     * SubDashBoard
     */
    public function getButtonData()
    {
        return [
            'label' => __('Back'),
            'onclick'   => 'setLocation(\'' . $this->getUrl('*/*/MasterDash') . '\')',
            'class' => 'back'
        ];
    }
}
