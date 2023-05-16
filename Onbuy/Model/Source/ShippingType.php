<?php


namespace Ced\Onbuy\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

class ShippingType implements OptionSourceInterface
{
    public function toOptionArray()
    {
        $_options = array(
           /* array(
                'label' => 'None',
                'value' => 0
            ),*/
            array(
                'label' => 'Undecided',
                'value' => 1
            ),
            array(
                'label' => 'Pickup',
                'value' => 2
            ),
            array(
                'label' => 'Free',
                'value' => 3
            ),
            array(
                'label' => 'Custom',
                'value' => 4
            ),
            array(
                'label' => 'Trademe',
                'value' => 5
            ),
        );
        return $_options;
    }

}