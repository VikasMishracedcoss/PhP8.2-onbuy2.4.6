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
 * @author        CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license     http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Onbuy\Controller\Adminhtml\Profile;

use Ced\Onbuy\Helper\Data;

/**
 * Class UpdateCategoryAttributes
 * @package Ced\Onbuy\Controller\Adminhtml\Profile
 */
class UpdateCategoryAttributes extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Ced_Onbuy::Onbuy';
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;
    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var \Ced\Onbuy\Helper\MultiAccount
     */
    protected $multiAccountHelper;

    /**
     * @var Data
     */
    public $helper;

    /**
     * UpdateCategoryAttributes constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Registry $coreRegistry
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Registry $coreRegistry,
        \Ced\Onbuy\Helper\MultiAccount $multiAccountHelper,
        Data $helper
    )
    {
        parent::__construct($context);
        $this->multiAccountHelper = $multiAccountHelper;
        $this->resultPageFactory = $resultPageFactory;
        $this->_coreRegistry = $coreRegistry;
        $this->helper = $helper;
        $this->_onbuyAttribute = [];
    }

    public function execute()
    {
        $accountId = $this->_session->getAccountId();
        $feature = $this->getRequest()->getParam('feature');

        if ($this->_coreRegistry->registry('onbuy_account'))
            $this->_coreRegistry->unregister('onbuy_account');
        $this->multiAccountHelper->getAccountRegistry($accountId);
        $this->helper->updateAccountVariable();
        $profileId = $this->getRequest()->getParam('profile_id');


        $result = $this->resultPageFactory->create(true)->getLayout()->createBlock('Ced\Onbuy\Block\Adminhtml\Profile\Edit\Tab\Attribute\Trademeattribute')->setCatId($feature)->toHtml();
        $this->getResponse()->setBody($result);
    }

}
