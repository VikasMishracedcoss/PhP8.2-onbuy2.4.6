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
namespace Ced\Onbuy\Model\Source;


/**
 * Class ListingDuration
 * @package Ced\Onbuy\Model\Source
 */
class MagentoAttributes extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    public $_jdecode;

    public function __construct(
        \Magento\Framework\Json\Helper\Data $_jdecode,
        \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $attributes

    )
    {
        $this->_jdecode = $_jdecode;
        $this->attributes = $attributes;
    }

    /**
     * @return array
     */
    public function getOptionArray()
    {
        $options = [];
        foreach ($this->getAllOptions() as $option) {
            $options[$option['value']] =(string)$option['label'];
        }
        return $options;
    }
    /**
     * @return array
     */

    public function jsonData() {
        $data = $this->getAllOptions();
        $val = $label = [];
        foreach ($data as $item) {
            $val['value'][] = $item['value'];
            $val['label'][] = $item['label'];
        }
        //$finalData = array_merge($val, $label);
        return ($this->_jdecode->jsonEncode($val));

    }

    public function getAllOptions()
    {
        $attributes = $this->attributes->create()->getItems();
        $mattributecode = '--please select--';
        $magentoattributeCodeArray[] = ['value' => '', 'label' => $mattributecode];
        $magentoattributeCodeArray[] = ['value' => 'default', 'label' => "--Set Default Value--"];
        foreach ($attributes as $attribute) {
            $magentoattributeCodeArray[] = ['value' => $attribute->getAttributecode(), 'label' => $attribute->getFrontendLabel()];
        }
//        echo "<pre>";
//        print_r($magentoattributeCodeArray);
//        echo "next";
        return $magentoattributeCodeArray;
    }


    /**
     * @return array
     */
    public function toOptionArray()
    {
        return $this->getOptions();
    }

    /**
     * @return array
     */
    public function getAllOption()
    {
        $options = $this->getOptionArray();
        array_unshift($options, ['value' => '', 'label' => '']);
        return $options;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        $res = [];
        foreach ($this->getOptionArray() as $index => $value) {
            $res[] = ['value' => $index, 'label' => $value];
        }
        return $res;
    }

    /**
     * @param int|string $optionId
     * @return mixed|null
     */
    public function getOptionText($optionId)
    {
        $options = $this->getOptionArray();
        return isset($options[$optionId]) ? $options[$optionId] : null;
    }

    /**
     * @return array
     */
    public function getLabel($options = [])
    {
        foreach ($this->getAllOptions() as $option) {
            $options[] =(string)$option['label'];
        }
        return $options;
    }
}
