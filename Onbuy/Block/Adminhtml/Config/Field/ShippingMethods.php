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
 * @package   Ced_Cdiscount
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Onbuy\Block\Adminhtml\Config\Field;

class ShippingMethods extends \Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray
{

    /**
     * @var
     */
    protected $_shippingRegion;

    /**
     * @var
     */
    protected $_shippingMethod;
    /**
     * @var
     */

    protected $_magentoAttr;
    /**
     * @var
     */

    protected  $_enabledRenderer;

    /**
     * @return \Magento\Framework\View\Element\BlockInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _getEnabledRenderer()
    {
        if (!$this->_enabledRenderer) {
            $this->_enabledRenderer = $this->getLayout()->createBlock(
                'Ced\Onbuy\Block\Adminhtml\Config\Field\OnbuyCarriers',
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
            $this->_enabledRenderer->setClass('shipping_region_select');
            $this->_enabledRenderer->setId('<%- _id %>');
        }
        return $this->_enabledRenderer;
    }

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareToRender()
    {

        $this->addColumn(
            'shipping_method',
            [
                'label' => __('OnBuy Carriers'),
                'renderer' => $this->_getEnabledRenderer()
            ]
        );
        $this->addColumn('price', ['label' => __('Magento Carrier Title')]);
        $this->addColumn('additional_price', ['label' => __('Tracking Url'), 'note' =>"Enter the tracking url in the following format - www.yourdomain.com/{trackingId}"]);
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add Method');
    }

    /**
     * @param \Magento\Framework\DataObject $row
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareArrayRow(\Magento\Framework\DataObject $row)
    {
        $optionExtraAttr = [];

        $optionExtraAttr['option_' . $this->_getEnabledRenderer()->calcOptionHash($row->getData('shipping_method'))] =
            'selected="selected"';
        $row->setData(
            'option_extra_attrs',
            $optionExtraAttr
        );
    }

}
