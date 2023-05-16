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

namespace Ced\Onbuy\Block\Adminhtml\Profile\Edit\Tab\Attribute;

use Ced\Onbuy\Helper\Data;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;
use \Magento\Backend\Block\Widget;

/**
 * Class Trademeattribute
 * @package Ced\Onbuy\Block\Adminhtml\Profile\Edit\Tab\Attribute
 */
class Trademeattribute extends Widget implements RendererInterface

{
    /**
     * @var string
     */
    public $_template = 'Ced_Onbuy::profile/attribute/trademeattribute.phtml';
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    public  $_objectManager;
    /**
     * @var \Magento\Framework\Registry
     */
    public  $_coreRegistry;
    /**
     * @var mixed
     */
    public  $_profile;
    /**
     * @var
     */
    public  $_onbuyAttribute;
    /**
     * @var
     */
    public $_onbuyAttributeFeature;
    /**
     * @var Data
     */
    public $helper;

    public $logger;

    public $categoryFactory;

    /**
     * Trademeattribute constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\Registry $registry
     * @param Data $helper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Registry $registry,
        \Ced\Onbuy\Helper\Logger $logger,
        \Ced\Onbuy\Model\CategoryFactory $categoryFactory,
        Data $helper,
        array $data = []
    ) {
        $this->_objectManager = $objectManager;
        $this->logger = $logger;
        $this->helper = $helper;
        $this->categoryFactory = $categoryFactory;
        $this->_coreRegistry = $registry;
        $this->_profile = $this->_coreRegistry->registry('current_profile');
        parent::__construct($context, $data);
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        $button = $this->getLayout()->createBlock(
            'Magento\Backend\Block\Widget\Button'
        )->setData(['label' => 'Add Attribute', 'onclick' =>'return onbuyAttributeControl.addItem()', 'class' => 'add']);

        $button->setName('add_required_item_button');
        $this->setChild('add_button', $button);
        
        return parent::_prepareLayout();
    }

    /**
     * @return string
    **/
    public function getAddButtonHtml()
    {
        return $this->getChildHtml('add_button');
    }

    /**
     * @return array
     */
    public function getOnbuyAttributes()
    {
        try {
            $catId = $this->getRequest()->getParam('feature');

            $requiredAttributes = $optionalAttribues = [];
            if ($this->_profile && $this->_profile->getId() > 0) {
                $catId = ($this->_profile->getProfileCategory());

            }

            if (!$catId){
                $catId = $this->_session->getCatId();
                if (!$catId)
                $catId = $this->_coreRegistry->registry('category_id');
            }

            if ($catId) {
                $getAttribute = $this->helper->getRequest("categories/$catId/features?site_id=2000&limit=20&offset=0",true);
                $techDetails = json_decode($this->helper->getRequest("categories/$catId/technical-details?site_id=2000", true), true);
                $getAttribute = json_decode($getAttribute, true);


              //print_r($getAttribute); die(__DIR__); 

                $response = isset($getAttribute['results']) ? $getAttribute['results'] : $getAttribute;
                if (isset($getAttribute['results'])) {
                foreach ($response as $value) {
                    $options = array();
                    if (isset($value['options'])) {
                        $temOptions = array();
                        foreach ($value['options'] as $opt) {
                            $temOptions = array('name' => $opt['name'],
                                'value' => $opt['option_id']);
                            $options[] = $temOptions;
                            }
                        }
                        $val = $optionValue = [];
                        foreach ($options as $option) {
                            $val[] = $option['name'] . ":" . $option['value'];
                            $optionValue[$option['name'] . ":". $option['value']] = $option['value'];
                        }
                        $allowValueIds = !empty($options) ? array('value' => $options) : '';

                            if (isset($value['required']) && !empty($value['required'])) {
                                $requiredAttributes [$value['name']] = array(
                                    'onbuy_attribute_name' => $value['name'],
                                    'onbuy_attribute_type' => 'LABEL',
                                    'magento_attribute_code' => '',
                                    'required' => 1,
                                    'onbuy_attribute_enum' => implode(',', $val)/*$allowValueIds*/,
                                    'options' => json_encode($optionValue)
                                );
                            } else {
                                $optionalAttribues [$value['name']] = array(
                                    'onbuy_attribute_name' => $value['name'],
                                    'onbuy_attribute_type' => 'LABEL',
                                    'magento_attribute_code' => '',
                                    'required' => 0,
                                    'onbuy_attribute_enum' => $allowValueIds,
                                    'options' => json_encode($optionValue)
                                );
                            }
                    }
                }
                if (isset($techDetails['results'])) {
                    foreach ($techDetails['results'] as $value) {
                        $options = array();
                        if (isset($value['options'])) {
                            $temOptions = [];
                            foreach ($value['options'] as $opt) {
                                $temOptions = [
                                    'name' => $opt['name'],
                                    'value' => $opt['detail_id']
                                ];
                                if (isset($opt['units']))
                                    $temOptions['units'] = implode(",", $opt['units']);
                                $options[] = $temOptions;
                            }
                        }
                        $val = $optionValue = $v = [];
                        foreach ($options as $option) {
                            if (isset($option['units'])){
                                $val[] = $option['units'];
                                $optionValue[$option['name']] = $option['value'];
                                if (isset($option['required'])) {

                                    $requiredAttributes [$option['name']. " [Technical Detail]"] = array(
                                        'onbuy_attribute_name' => $option['name']. " [Technical Detail]",
                                        'onbuy_attribute_type' => 'LABEL',
                                        'magento_attribute_code' => '',
                                        'required' => 1,
                                        'onbuy_attribute_enum' => implode(',', $v),
                                        'options' => json_encode($optionValue)
                                    );

                                } else {
                                    $optionalAttribues [$option['name']. " [Technical Detail]"] = array(
                                        'onbuy_attribute_name' => $option['name'] . " [Technical Detail]",
                                        'onbuy_attribute_type' => 'LABEL',
                                        'magento_attribute_code' => '',
                                        'required' => 0,
                                        'onbuy_attribute_enum' => implode(',', $v),
                                        'options' => json_encode($optionValue)
                                    );
                                    if (!empty($val)){
                                        $optionalAttribues [$option['name']. " [Technical Detail]"]['units'] = implode(',',$val);
                                    }
                                }
                            }else {

                         $optionalAttribues [$option['name'].":".$option['value']. " [Technical Detail]"] = array(
                                    'onbuy_attribute_name' => $option['name'].":".$option['value'] . " [Technical Detail]",
                                    'onbuy_attribute_type' => 'LABEL',
                                    'magento_attribute_code' => '',
                                    'required' => 0,
                                    'onbuy_attribute_enum' =>implode(',', $v),
                                    'options' => '{}'
                                );
                            
                            }

                        }
                        $allowValueIds = !empty($options) ? array('value' => $options) : '';


                    }

                }

            }

            $this->_onbuyAttribute[] = [
                'label' => __('Required Attributes'),
                'value' => $requiredAttributes
            ];

            $this->_onbuyAttribute[] = [
                'label' => __('Optional Attributes'),
                'value' => $optionalAttribues
            ];

            return $this->_onbuyAttribute;
        } catch (\Exception $e) {
            $this->logger->addError('In Onbuy Attributes: has exception '.$e->getMessage(), ['path' => __METHOD__]);
            return false;
        }
    }

    /**
     * @return mixed
     */
    public function getMagentoAttributes()
    {
        $attributes = $this->_objectManager->create('Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection')->getItems();
        $mattributecode = '--please select--';
        $magentoattributeCodeArray[''] = ['label' => $mattributecode, 'options' => ''];
        $magentoattributeCodeArray['default'] = ['label' => "--Set Default Value--", 'options' => ''];
        foreach ($attributes as $attribute) {
    if ($attribute->getFrontendInput() =='select'){

        $attributeOptions = $attribute->getSource()->getAllOptions(false);
        $attributeOptions = str_replace('\'', '&#39;', json_encode($attributeOptions));
        $optionValues = addslashes($attributeOptions);
        $magentoattributeCodeArray[$attribute->getAttributecode()] = ['label' => $attribute->getFrontendLabel() . ' [select]', 'options' => /*json_encode*/($optionValues)];


            } else {
                $magentoattributeCodeArray[$attribute->getAttributecode()] = ['label' => $attribute->getFrontendLabel(), 'options' => ''];
            }


        }
        
        asort($magentoattributeCodeArray);
        return $magentoattributeCodeArray;
    }


    /**
     * @return array|mixed
     */
    public function getMappedAttribute()
    {
        $data = $this->_onbuyAttribute[0]['value'];
        $temp = $temp2 = [];
        if($this->_profile && $this->_profile->getId()>0){
            $data = json_decode($this->_profile->getCatDependAttribute(), true);

            if(isset($data['required_attributes']) && isset($data['optional_attributes'])) {
             
                if(!empty($data['required_attributes'])) {
                $data = array_merge($data['required_attributes'], $data['optional_attributes']);
                }else{
                       $data = ($data['optional_attributes']);
                 }
            } elseif(!empty($data['required_attributes'])) {

                $temp = $data['required_attributes'];
            }

        }
        if ($temp && count($temp) > 0)
            $data = $temp;
        return $data;
    }


    /**
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $this->setElement($element);
        return $this->toHtml();
    }

}
