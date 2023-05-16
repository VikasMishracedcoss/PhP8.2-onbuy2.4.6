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

namespace Ced\Onbuy\Block\Adminhtml\Product;
use Magento\Framework\Registry;

/**
 * Class Massupload
 * @package Ced\Onbuy\Block\Adminhtml\Product
 */




class Importcsv extends \Magento\Framework\View\Element\Template
{
    protected $_postFactory;
    protected $formKey;
    protected $_coreRegistry;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Data\Form\FormKey $formKey,
        Registry $coreRegistry
        


    )
    { 
        $this->_coreRegistry = $coreRegistry;
        $this->formKey = $formKey;
        parent::__construct($context);
    }

    // Creating form key 
    public function getFormKey()
    {   
        return $this->formKey->getFormKey();
    }

    // Creating insertion url 
    public function getFormAction()
    {   
        return $this->getUrl('onbuy/import/importcsvdata', ['_secure' => true]);
    }


    public function getAccountId()
    {
        return $this->_backendSession->getAccountId();
    }

}
