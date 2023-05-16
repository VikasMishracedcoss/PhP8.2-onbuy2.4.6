<?php


namespace Ced\Onbuy\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

class OrderFilter implements OptionSourceInterface
{
    public function toOptionArray()
    {
        return array(
            array(
                'value' => 'Last24Hours',
                'label' => __('last 24 hours')
            ),
            array(
                'value' => 'LastHour',
                'label' => __('last hour')
            ),
            array(
                'value' => 'FeedbackToPlace',
                'label' => __('Feedback To Place')
            ),
            array(
                'value' => 'PaymentInstructionsToSend',
                'label' => __('Payment Instructions To Send')
            ),
            array(
                'value' => 'EmailSent',
                'label' => __('Email Sent')
            ),
            array(
                'value' => 'PaymentReceived',
                'label' => __('Payment Received')
            ),
            array(
                'value' => 'GoodsShipped',
                'label' => __('Goods Shipped')
            ),
            array(
                'value' => 'SaleCompleted',
                'label' => __('Sale Completed')
            ),
            array(
                'value' => 'Last3Days',
                'label' => __('last 3 days')
            ),
            array(
                'value' => 'Last7Days',
                'label' => __('last 7 days')
            ),
            array(
                'value' => 'Last30Days',
                'label' => __('last 30 days')
            ),
            array(
                'value' => 'Last45Days',
                'label' => __('last 45 days')
            )
        );
    }

}