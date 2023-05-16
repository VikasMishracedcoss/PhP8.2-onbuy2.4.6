<?php
/**
  * CedCommerce
  *
  * NOTICE OF LICENSE
  *
  * This source file is subject to the End User License Agreement (EULA)
  * that is bundled with this package in the file LICENSE.txt.
  * It is also available through the world-wide-web at this URL:
  * http://cedcommerce.com/license-agreement.txt
  *
  * @category    Ced
  * @package     Ced_Fruugo
  * @author      CedCommerce Core Team <connect@cedcommerce.com>
  * @copyright   Copyright CEDCOMMERCE (http://cedcommerce.com/)
  * @license     http://cedcommerce.com/license-agreement.txt
  */

namespace Ced\Onbuy\Model\Source;

class OnbuyAttributes implements \Magento\Framework\Option\ArrayInterface
{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray() {
        $onbuyAttributes = [
            
            [
                'value' => 'product_name',
                'label' => __('Title')
            ],
          
            
            [
                'value' => 'description',
                'label' => __('Description')
            ],
           
            

            [
                'value' => 'handling_time',
                'label' => __('Handling Time')
            ],
            [
                'value' => 'delivery_weight',
                'label' => __('Shipping Weight')
            ],

            [
                'value' => 'return_time',
                'label' => __('Return Time')
            ],
            [
                'value' => 'free_returns',
                'label' => __('Free Return')
            ],
            [
                'value' => 'warranty',
                'label' => __('Warranty')
            ],
            [
                'value' => 'delivery_template_id',
                'label' => __('Delivery Template Id')
            ],
            [
                'value' => 'rrp',
                'label' => __('RRP')
            ],
            [
                'value' => 'summary_points1',
                'label' => __('Summary Points1')
            ],
            [
                'value' => 'summary_points2',
                'label' => __('Summary Points2')
            ],
            [
                'value' => 'summary_points3',
                'label' => __('Summary Points3')
            ],
            [
                'value' => 'summary_points4',
                'label' => __('Summary Points4')
            ],
            [
                'value' => 'summary_points5',
                'label' => __('Summary Points5')
            ],
           

        ];
        return $onbuyAttributes;
    }

}
