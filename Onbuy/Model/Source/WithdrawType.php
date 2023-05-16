<?php


namespace Ced\Onbuy\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

class WithdrawType implements OptionSourceInterface
{
    public function toOptionArray()
    {
        return array(
            0 => array(
                'value' => "",
                'label' => "--Please Select--",
            ),
            1 => array(
                'value' => "1",
                'label' => "The item was sold",
            ),
            2 => array(
                'value' => "2",
                'label' => "The item didn’t sell",
            )

        );
    }

}