<?php


namespace Ced\Onbuy\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

class PaymentMethod implements OptionSourceInterface
{
    public function toOptionArray()
    {
        $_options = array(
            array(
                'label' => 'None',
                'value' => 0
            ),
            array(
                'label' =>'BankDeposit',
                'value' => 1
            ),
            array(
                'label' => 'CreditCard',
                'value' => 2
            ),
            array(
                'label' => 'Cash',
                'value' => 4
            ),
            array(
                'label' => 'SafeTrader',
                'value' => 8
            ),
            array(
                'label' => 'Other',
                'value' => 16
            ),
            array(
                'label' => 'Ping',
                'value' => 32
            ),
            array(
                'label' => 'Afterpay',
                'value' => 64
            )
        );
        return $_options;
    }

}