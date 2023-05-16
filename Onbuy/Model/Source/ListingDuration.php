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
class ListingDuration extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    public $_jdecode;

    public function __construct(
        \Magento\Framework\Json\Helper\Data $_jdecode

    )
    {
        $this->_jdecode = $_jdecode;
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
        $data = [
            [
                'value' => 0,
                'label' => __('EndDate')
            ],
            [
                'value' => 2,
                'label' => __('2 Days')
            ],
            [
                'value' => 3,
                'label' => __('3 Days')
            ],
            [
                'value' => 4,
                'label' => __('4 Days')
            ],
            [
                'value' => 5,
                'label' => __('5 Days')
            ],
            [
                'value' => 6,
                'label' => __('6 Days')
            ],
            [
                'value' => 7,
                'label' => __('7 Days')
            ],
            [
                'value' => 10,
                'label' => __('10 Days')
            ],
            [
                'value' => 14,
                'label' => __('14 Days')
            ],
            [
                'value' => 21,
                'label' => __('21 Days')
            ],
            [
                'value' => 28,
                'label' => __('28 Days')
            ],
            [
                'value' => 30,
                'label' => __('30 Days')
            ],
            [
                'value' => 42,
                'label' => __('42 Days')
            ],
            [
                'value' => 56,
                'label' => __('56 Days')
            ],
            [
                'value' => 90,
                'label' => __('90 Days')
            ],
            [
                'value' => -1,
                'label' => __('UntilWithdrawn')
            ],
        ];
        return (json_encode($data));
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
