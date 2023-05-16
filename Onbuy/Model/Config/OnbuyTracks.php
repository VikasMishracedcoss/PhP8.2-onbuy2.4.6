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

class OnbuyTracks implements \Magento\Framework\Option\ArrayInterface
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
                'label' => 'DPD',
                'value' =>'1'
            ],
            [
                'label' =>'InterParcel',
                'value' =>'2'
            ],
            [
                'label' =>'DHL',
                'value' =>'3'
            ],
            [
                'label' =>'Parcelforce',
                'value' =>'4'
            ],
            [
                'label' =>'UK Mail',
                'value' =>'5'
            ],
            [
                'label' =>'Royal Mail',
                'value' =>'6'
            ],
            [
                'label' =>'myHermes',
                'value' =>'7'
            ],
            [
                'label' =>'Yodel',
                'value' =>'8'
            ],
            [
                'label' =>'Palletways',
                'value' =>'9'
            ],
            [
                'label' =>'Tuffnells',
                'value' =>'10'
            ],
            [
                'label' =>'UPS',
                'value' =>'11'
            ],
            [
                'label' =>'TNT',
                'value' =>'12'
            ],
            [
                'label' =>'FedEx',
                'value' =>'13'
            ],
            [
                'label' =>'Parcel2Go',
                'value' =>'14'
            ],
            [
                'label' =>'APC Overnight',
                'value' =>'15'
            ],
            [
                'label' =>'Unknown',
                'value' =>'16'
            ],
            [
                'label' =>'DX',
                'value' =>'17'
            ],
            [
                'label' =>'GLS',
                'value' =>'18'
            ],
            [
                'label' =>'13Ten',
                'value' =>'19'
            ],
            [
                'label' =>'DPD Local',
                'value' =>'20'
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
        return $this->toOptionArray();
        $options = [];
        foreach ($this->toOptionArray() as $option) {
            $options[$option['value']] = (string)$option['label'];
        }
//        print_r($options);die;
        return $options;
    }
    public function getAllOptions()
    {
        $serviceUrls = [];

        $serviceUrls = [
            [
                'label' => 'DPD',
                'value' =>'1'
            ],
            [
                'label' =>'InterParcel',
                'value' =>'2'
            ],
            [
                'label' =>'DHL',
                'value' =>'3'
            ],
            [
                'label' =>'Parcelforce',
                'value' =>'4'
            ],
            [
                'label' =>'UK Mail',
                'value' =>'5'
            ],
            [
                'label' =>'Royal Mail',
                'value' =>'6'
            ],
            [
                'label' =>'myHermes',
                'value' =>'7'
            ],
            [
                'label' =>'Yodel',
                'value' =>'8'
            ],
            [
                'label' =>'Palletways',
                'value' =>'9'
            ],
            [
                'label' =>'Tuffnells',
                'value' =>'10'
            ],
            [
                'label' =>'UPS',
                'value' =>'11'
            ],
            [
                'label' =>'TNT',
                'value' =>'12'
            ],
            [
                'label' =>'FedEx',
                'value' =>'13'
            ],
            [
                'label' =>'Parcel2Go',
                'value' =>'14'
            ],
            [
                'label' =>'APC Overnight',
                'value' =>'15'
            ],
            [
                'label' =>'Unknown',
                'value' =>'16'
            ],
            [
                'label' =>'DX',
                'value' =>'17'
            ],
            [
                'label' =>'GLS',
                'value' =>'18'
            ],
            [
                'label' =>'13Ten',
                'value' =>'19'
            ],
            [
                'label' =>'DPD Local',
                'value' =>'20'
            ]
        ];

        return $serviceUrls;
    }
}
