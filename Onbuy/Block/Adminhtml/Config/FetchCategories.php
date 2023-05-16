<?php


namespace Ced\Onbuy\Block\Adminhtml\Config;


class FetchCategories extends \Magento\Config\Block\System\Config\Form\Field
{
    protected $_template = 'Ced/Onbuy/view/adminhtml/templates/Config/fetchcategories.phtml';

    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if (!$this->getTemplate()) { $this->setTemplate($this->_wizardTemplate); }
        return $this;
    }
    protected function _getButtonData($elementHtmlId, $originalData)
    {
        return array(
            'html_id' => $elementHtmlId,
        );
    }

}