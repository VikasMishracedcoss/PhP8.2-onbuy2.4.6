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

namespace Ced\Onbuy\Block\Adminhtml\Profile\Edit\Tab;

use Magento\Catalog\Api\Data\ProductSearchResultsInterfaceFactory;

/**
 * Class Mapping
 * @package Ced\Onbuy\Block\Adminhtml\Profile\Edit\Tab
 */
class Mapping extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * Mapping constructor.
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Framework\ObjectManagerInterface $objectInterface
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\ObjectManagerInterface $objectInterface,
        array $data = []
    )
    {
        $this->_coreRegistry = $registry;
        $this->_objectManager = $objectInterface;
        parent::__construct($context, $registry, $formFactory);
    }

    /**
     * @return $this
     */
    protected function _prepareForm()
    {

        $form = $this->_formFactory->create();
        $profile = $this->_coreRegistry->registry('current_profile');

        $fieldset = $form->addFieldset('category', array('legend' => __('Category Mapping')));

        $fieldset->addField(
            'level_0',
            'select',
            [
                'name' => 'level_0',
                'label' => __('Select Category'),
                'title' => __('Select Category'),
                'required' => true,
                'values' => $this->_objectManager->create('Ced\Onbuy\Model\Source\Profile\Category\Rootlevel')->toOptionArray(),
                'value' => $profile->getProfileCategory()
            ]
        );

        $fieldset->addField('category_js', 'text', [
                'label' => __('Category JS Mapping'),
                'class' => 'action',
                'name' => 'category_js_mapping'
            ]
        );

        $locations = $form->getElement('category_js');
        $locations->setRenderer(
            $this->getLayout()->createBlock('Ced\Onbuy\Block\Adminhtml\Profile\Edit\Tab\Attribute\CategoryJs')
        );

        $fieldset->addField(
            'searchcategory',
            'text',
            [
                'name' => 'searchcategory',
                'label' => __('Search Category'),
                'title' => __('Search Category'),
                'value' => $profile->getProfileCatSearch(),
                'class' => __('searchcategory')
            ]
        );
        
        $fieldset->addField('search_category', 'text', [
                'label' => __('Search Category'),
                'class' => 'action',
                'name' => 'search_category'
            ]
        );

        $locations = $form->getElement('search_category');
        $locations->setRenderer(
            $this->getLayout()->createBlock('Ced\Onbuy\Block\Adminhtml\Profile\Edit\Tab\Search\Searchcategory')
        );
//---------------------OnBuy-Magento Category Dependent Attributes Mapping-------------------------//
        $fieldset = $form->addFieldset('onbuy_attributes', array('legend' => __('OnBuy-Magento Category Dependent Attributes Mapping')));

        $fieldset->addField('onbuy_attribute', 'text', [
                'label' => __('Attribute Mapping'),
                'class' => 'action',
                'name' => 'required_attribute'
            ]
        );

        $locations = $form->getElement('onbuy_attribute');

        $locations->setRenderer($this->getLayout()->createBlock('Ced\Onbuy\Block\Adminhtml\Profile\Edit\Tab\Attribute\Trademeattribute')
        );
//---------------------OnBuy-Magento Required Attributes Mapping-------------------------//
        $fieldset = $form->addFieldset('required_attributes', array('legend' => __('OnBuy-Magento Required Attributes Mapping')));

        $fieldset->addField('required_attribute', 'text', [
                'label' => __('Attribute Mapping'),
                'class' => 'action',
                'name' => 'required_attribute'
            ]
        );

        $locations = $form->getElement('required_attribute');
     $locations->setRenderer($this->getLayout()->createBlock('Ced\Onbuy\Block\Adminhtml\Profile\Edit\Tab\Attribute\Requiredattribute')
        );
        $this->setForm($form);
        return parent::_prepareForm();
    }
}