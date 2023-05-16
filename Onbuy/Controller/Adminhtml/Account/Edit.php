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

namespace Ced\Onbuy\Controller\Adminhtml\Account;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Ced\Onbuy\Model\AccountsFactory;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Edit
 * @package Ced\Onbuy\Controller\Adminhtml\Account
 */
class Edit extends \Magento\Backend\App\Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    const ADMIN_RESOURCE = 'Ced_Onbuy::Onbuy';

    /**
     * @var \Ced\Onbuy\Model\Accounts
     */
    public $accounts;
    protected $_storeManager;

    /**
     * Edit constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param AccountsFactory $accounts
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        StoreManagerInterface $_storeManager,
        AccountsFactory $accounts
    )
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->_storeManager = $_storeManager;
        $this->accounts = $accounts;
    }

    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $media = $this->_storeManager->getStore()->getBaseUrl();
        $id = $this->getRequest()->getParam('id');
        if ($id) {
            $accounts = $this->accounts->create()->load($id);
        } else {
            $accounts = $this->accounts->create();
        }
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend($accounts->getId() ? $accounts->getAccountCode() : __('New Account'));
        return $resultPage;
    }
}