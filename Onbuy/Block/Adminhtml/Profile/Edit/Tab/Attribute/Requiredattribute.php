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

use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;
use \Magento\Backend\Block\Widget;

/**
 * Class Requiredattribute
 * @package Ced\Onbuy\Block\Adminhtml\Profile\Edit\Tab\Attribute
 */
class Requiredattribute extends Widget implements RendererInterface
{

    /**
     * @var string
     */
    protected $_template = 'Ced_Onbuy::profile/attribute/required_attribute.phtml';
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected  $_objectManager;
    /**
     * @var \Magento\Framework\Registry
     */
    protected  $_coreRegistry;
    /**
     * @var mixed
     */
    protected  $_profile;
    /**
     * @var
     */
    protected  $_trademeAttribute;
    
    protected $helper;
    /**
     * Requiredattribute constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Registry $registry,
        \Ced\Onbuy\Helper\Data $helperData,
        array $data = []


    )
    {
        $this->_objectManager = $objectManager;
        $this->_coreRegistry = $registry;
        $this->_profile = $this->_coreRegistry->registry('current_profile');
        $this->helper = $helperData;
        parent::__construct($context, $data);
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        $button = $this->getLayout()->createBlock(
            'Magento\Backend\Block\Widget\Button'
        )->setData(
            ['label' => __('Add Attribute'), 'onclick' => 'return requiredAttributeControl.addItem()', 'class' => 'add']
        );
        $button->setName('add_required_item_button');

        $this->setChild('add_button', $button);
        return parent::_prepareLayout();
    }

    /**
     * @return string
     */
    public function getAddButtonHtml()
    {
        return $this->getChildHtml('add_button');
    }

    public function shippingtemp()
    {
 
    return $this->helper->Deliver_template();
   // $helper = $this->helper('Ced\Onbuy\Helper\Data');
   // return $data = $helper->Deliver_template();
    
    }
    /**
     * @return array
     */
    public function getOnbuyAttributes()
    {  
        $Delivery='';
        $listingshipping = $this->shippingtemp();
        if(isset($listingshipping)){ 
           $Delivery=$listingshipping;
         }
       //print_r($listingshipping); die(__DIR__);
        $requiredAttribute = [
            'Title' => ['onbuy_attribute_name' => 'product_name','onbuy_attribute_type' => 'text', 'onbuy_attribute_enum' => '','magento_attribute_code' => 'name', 'required' => 1],
            'Price' => ['onbuy_attribute_name' => 'price','onbuy_attribute_type' => 'text', 'onbuy_attribute_enum' => '','magento_attribute_code' => 'price', 'required' => 1],
            'SKU' => ['onbuy_attribute_name' => 'sku','onbuy_attribute_type' => 'text', 'onbuy_attribute_enum' => '','magento_attribute_code' => 'sku', 'required' => 1],
            'Description' => ['onbuy_attribute_name' => 'description','onbuy_attribute_type' => 'textarea', 'onbuy_attribute_enum' => '','magento_attribute_code' => 'description', 'required' => 1],
            'Product Codes' => ['onbuy_attribute_name' => 'product_codes','onbuy_attribute_type' => 'select', 'onbuy_attribute_enum' => /*implode(',', $listingDuration)*/ /*$listingDuration*/'','magento_attribute_code' => '', 'required' => 1],
            'Brand' => ['onbuy_attribute_name' => 'brand_name','onbuy_attribute_type' => 'select', 'onbuy_attribute_enum' => /*implode(',', $listingDuration)*/ /*$pickup*/'','magento_attribute_code' => '', 'required' => 1],
            'Quantity' => ['onbuy_attribute_name' => 'stock','onbuy_attribute_type' => 'text', 'onbuy_attribute_enum' => '','magento_attribute_code' => 'quantity_and_stock_status', 'required' => 1]
        ];
        $optionalAttribues = [
            'Manufacturer Part Number' => ['onbuy_attribute_name' => 'mpn','onbuy_attribute_type' => 'text', 'onbuy_attribute_enum' => ''],
            'Handling Time' => ['onbuy_attribute_name' => 'handling_time','onbuy_attribute_type' => 'text', 'onbuy_attribute_enum' => ''],
            'Shipping Weight' => ['onbuy_attribute_name' => 'delivery_weight','onbuy_attribute_type' => 'text', 'onbuy_attribute_enum' => ''],
            'Return Time' => ['onbuy_attribute_name' => 'return_time','onbuy_attribute_type' => 'text', 'onbuy_attribute_enum' => ''],
            'Free Return' => ['onbuy_attribute_name' => 'free_returns','onbuy_attribute_type' => 'text', 'onbuy_attribute_enum' => ''],
            'Warranty' => ['onbuy_attribute_name' => 'warranty','onbuy_attribute_type' => 'text', 'onbuy_attribute_enum' => ''],
            'Delivery Template Id' => ['onbuy_attribute_name' => 'delivery_template_id','onbuy_attribute_type' => 'select', 'onbuy_attribute_enum' =>$listingshipping],
            'RRP' => ['onbuy_attribute_name' => 'rrp','onbuy_attribute_type' => 'text', 'onbuy_attribute_enum' => '','magento_attribute_code' => 'quantity_and_stock_status', 'required' => 1],
            'Summary Points1' => ['onbuy_attribute_name' => 'summary_points1','onbuy_attribute_type' => 'text', 'onbuy_attribute_enum' => ''],
            'Summary Points2' => ['onbuy_attribute_name' => 'summary_points2','onbuy_attribute_type' => 'text', 'onbuy_attribute_enum' => ''],
            'Summary Points3' => ['onbuy_attribute_name' => 'summary_points3','onbuy_attribute_type' => 'text', 'onbuy_attribute_enum' => ''],
            'Summary Points4' => ['onbuy_attribute_name' => 'summary_points4','onbuy_attribute_type' => 'text', 'onbuy_attribute_enum' => ''],
            'Summary Points5' => ['onbuy_attribute_name' => 'summary_points5','onbuy_attribute_type' => 'text', 'onbuy_attribute_enum' => ''],
            'Boost Marketing Comission' => ['onbuy_attribute_name' => 'boost_marketing_commission','onbuy_attribute_type' => 'decimal', 'onbuy_attribute_enum' => ''],

        ];

        $this->_trademeAttribute[] = array(
            'label' => __('Required Attributes'),
            'value' => $requiredAttribute
        );


        $this->_trademeAttribute[] = array(
            'label' => __('Optional Attributes'),
            'value' => $optionalAttribues
        );

       return $this->_trademeAttribute;
    }

    /**
     * @return mixed
     */
    public function getMagentoAttributes()
    {
        $attributes = $this->_objectManager->create('Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection')
            ->getItems();

        $mattributecode = '--please select--';
        $magentoattributeCodeArray[''] = $mattributecode;
        $magentoattributeCodeArray['default'] = "--Set Default Value--";
        foreach ($attributes as $attribute){

            $magentoattributeCodeArray[$attribute->getAttributecode()] = $attribute->getFrontendLabel();
            asort($magentoattributeCodeArray);
        }
        return $magentoattributeCodeArray;
    }

    /**
     * @return array|mixed
     */
    public function getMappedAttribute()
    {
        $data = $this->_trademeAttribute[0]['value'];
        if($this->_profile && $this->_profile->getId()>0){
            $data = json_decode($this->_profile->getOptReqAttribute(), true);
            if(isset($data['required_attributes']) && isset($data['optional_attributes']))
                $data = array_merge($data['required_attributes'], $data['optional_attributes']);
        }
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
