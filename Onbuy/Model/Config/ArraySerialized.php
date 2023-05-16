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
 * @package   Ced_Cdiscount
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */

/**
 * Backend for serialized array data
 *
 */
namespace Ced\Onbuy\Model\Config;

/**
 * @api
 * @since 100.0.2
 */
class ArraySerialized extends \Magento\Config\Model\Config\Backend\Serialized
{

    public function beforeSave()
    {
        $value = $this->getValue();

        if (is_array($value)) {
            $value = $this->unique($value, 'shipping_method');
        }
        $this->setValue($value);
        return parent::beforeSave();
    }

    function unique($array, $key1) {
        $parsedArray = [];
        $i = 0;
        $keyArray = [];
        foreach ($array as $key => $val) {
            if (!isset($val[$key1])) {
                continue;
            }

            if (!in_array($val[$key1], $keyArray)) {
                $keyArray[$i] = $val[$key1];
                $parsedArray[$key] = $val;
            }
            $i++;
        }
        /*$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $serviceType = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('ebay_config/shipping_details/service_type');
        foreach ($parsedArray as $parseArray) {
            $shippingDetails = $objectManager->get('Ced\Ebay\Model\Config\ShippingService')->getShhipingDetails();
            foreach ($shippingDetails['ShippingServiceDetails'] as $value) {
                if ($value['ShippingService'] == $parseArray['shipping_method']) {
                    if (in_array($serviceType, $value['ServiceType'])) {
                        continue;
                    } else {
                        $objectManager->get('Magento\Framework\Message\ManagerInterface')->addErrorMessage("this shipping service not allowed for ".$parseArray['shipping_method']." shipping service please change it.");
                    }
                }
            }
        }*/
        return $parsedArray;
    }
}
