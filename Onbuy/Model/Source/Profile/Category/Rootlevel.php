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
 * @package     Ced_Onbuy
 * @author 		CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license     http://cedcommerce.com/license-agreement.txt
 */


namespace Ced\Onbuy\Model\Source\Profile\Category;


class Rootlevel implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Objet Manager
     * @var \Magento\Framework\ObjectManagerInterface
     */
    public $objectManager;

    /**
     * @var \Magento\Framework\Registry
     */
    public $_coreRegistry;
    public $categoryFactory;

    /**
     * Constructor
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Registry $registry
    ) {
        $this->objectManager = $objectManager;
        $this->_coreRegistry = $registry;

    }

    /**
     * To Array
     * @return string|[]
     */
    public function toOptionArray()
    {
        $options[] = [
            'value'=>'',
            'label'=>"Please Select Category"
        ];

        return $options;
    }

}