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

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Registry;
/**
 * Class NewAction
 * @package Ced\Onbuy\Controller\Adminhtml\Profile
 */
class NewAction extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Ced_Onbuy::Onbuy';
    /**
     * @var \Magento\Backend\Model\View\Result\ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * @var \Ced\Onbuy\Helper\MultiAccount
     */
    protected $multiAccountHelper;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    protected $_coreRegistry;

    public $dataHelper;
    public $scopeConfigManager;
    /**
     * NewAction constructor.
     * @param Context $context
     * @param \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
        \Ced\Onbuy\Helper\MultiAccount $multiAccountHelper,
        Registry $_coreRegistry,
        \Ced\Onbuy\Helper\Data $dataHelper
    )
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->multiAccountHelper = $multiAccountHelper;
        $this->_coreRegistry = $_coreRegistry;
        $this->dataHelper = $dataHelper;
        $this->scopeConfigManager = $this->_objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface');
    }

    public function execute()
    {
       
        if ($this->scopeConfigManager->getValue('onbuy_config/product_upload/primary_account'))
            $accountID = $this->scopeConfigManager->getValue('onbuy_config/product_upload/primary_account');
        else {
            $accounts = $this->multiAccountHelper->getAllAccounts(true);
            $accountID = $this->getRequest()->getParam('account_id');
        }
        

        /** @var \Ced\Onbuy\Model\Accounts $account */
        $account = $this->multiAccountHelper->getAccountRegistry($accountID);

        if ($accountID && $accountID != '' && $account->getId()/* || $totalAccounts == 1*/) {
            if ($this->_coreRegistry->registry('onbuy_account'))
                $this->_coreRegistry->unregister('onbuy_account');
            $this->multiAccountHelper->getAccountRegistry($accountID);
            $this->dataHelper->updateAccountVariable();
            if ($this->dataHelper->generateAccessToken())
            return $this->resultForwardFactory->create()->forward('edit');
            else{
                $this->messageManager->addErrorMessage('Check Account Details before creating Profile.');
                return $this->_redirect("*/*/index");
            }
        }
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Ced_Onbuy::Onbuy');
        $resultPage->getConfig()->getTitle()->prepend(__('Profiles'));
        $resultPage->getConfig()->getTitle()->prepend(__('Select Account'));
        return $resultPage;
    }
}
