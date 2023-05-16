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

class OrderCancel implements \Magento\Framework\Option\ArrayInterface
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
                'label' => 'Out of stock',
                'value' =>'1'
            ],
            [
                'label' =>'Buyer cancelled order',
                'value' =>'2'
            ],
            [
                'label' =>'Undeliverable address',
                'value' =>'3'
            ],
            [
                'label' =>'Pricing error',
                'value' =>'4'
            ],
            [
                'label' =>'Other issue (please specify)',
                'value' =>'5'
            ]
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
