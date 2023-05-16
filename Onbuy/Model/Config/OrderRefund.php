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
namespace Ced\Onbuy\Model\Config;

class OrderRefund implements \Magento\Framework\Option\ArrayInterface
{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'label' => 'Customer return',
                'value' =>'1'
            ],
            [
                'label' =>'Item not delivered ',
                'value' =>'2'
            ],
            [
                'label' =>'Item is damaged/faulty',
                'value' =>'3'
            ],
            [
                'label' =>'Incorrect item sent',
                'value' =>'4'
            ],
            [
                'label' =>'Other issue',
                'value' =>'5'
            ],
            [
                'label' =>'Order Cancelled',
                'value' =>'6'
            ],
        ];
    }

    /**
     * Retrieve option array
     *
     * @return array
     */
    public function getOptionArray()
    {
        $options = [];
        foreach ($this->toOptionArray() as $option) {
            $options[$option['value']] = (string)$option['label'];
        }
        return $options;
    }
}
