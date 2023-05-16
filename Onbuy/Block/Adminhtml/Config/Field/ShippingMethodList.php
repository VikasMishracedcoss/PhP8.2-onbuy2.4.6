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

namespace Ced\Onbuy\Block\Adminhtml\Config\Field;

use Magento\Framework\Api\SearchCriteriaBuilder;


/**
 * HTML select element block with customer groups options
 */
class ShippingMethodList extends \Magento\Framework\View\Element\Html\Select
{
    /**
     * @var
     */
    private $_shippingMethod;
    /**
     * @var SearchCriteriaBuilder
     */

    private  $searchCriteriaBuilder;
    /**
     * @var \Ced\Onbuy\Model\Config\ShippingService
     */

    private  $_shipMethod;

    /**
     * ShippingMethodList constructor.
     * @param \Magento\Framework\View\Element\Context $context
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Ced\Onbuy\Model\Config\ShippingMethods $shipMethod
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Context $context,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        \Ced\Onbuy\Model\Config\ShippingMethods $shipMethod,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_shipMethod = $shipMethod;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }


    /**
     * @return array|null
     */
    protected function _getShippingMethod()
    {
        if ($this->_shippingMethod === null) {
            $shipMethod = $this->_shipMethod->toOptionArray();

            $this->_shippingMethod = $shipMethod;
        }
        return $this->_shippingMethod;
    }

    /**
     * @param $value
     * @return mixed
     */
    public function setInputName($value)
    {
        return $this->setName($value);
    }

    /**
     * @return string
     */
    public function _toHtml()
    {
        if (!$this->getOptions()) {
            foreach ($this->_getShippingMethod() as  $method) {
                $this->addOption($method['value'], addslashes($method['label']));
            }
        }
        return parent::_toHtml();
    }
}
