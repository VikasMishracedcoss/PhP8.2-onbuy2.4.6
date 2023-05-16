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
use Magento\Backend\App\Action;

/**
 * Class Save
 * @package Ced\Onbuy\Controller\Adminhtml\Account
 */
class Save extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Ced_Onbuy::Onbuy';

    /**
     * @var \Ced\Onbuy\Helper\MultiAccount
     */
    protected $multiAccountHelper;

    /**
     * @var \Ced\Onbuy\Model\AccountsFactory
     */
    public $accounts;

    public $data;

    public $accountCollection;
    /**
     * Save constructor.
     * @param Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     */
    public function __construct(
        Context $context,
        \Ced\Onbuy\Model\AccountsFactory $accounts,
        \Ced\Onbuy\Model\ResourceModel\Accounts\CollectionFactory $accountCollection,
        \Magento\Framework\DataObject $data,
        \Ced\Onbuy\Helper\MultiAccount $multiAccountHelper
    )
    {
        parent::__construct($context);
        $this->accounts = $accounts;
        $this->multiAccountHelper = $multiAccountHelper;
        $this->accountCollection = $accountCollection;
        $this->data = $data;
    }

    public function execute()
    {

        if ($this->validate()) {
            $accountDetails = $this->getRequest()->getParams();
            try {
                if (isset($accountDetails['account_code']) || isset($accountDetails['id'])) {
                    if (isset($accountDetails['id'])) {
                        $accounts = $this->accounts->create()->load($accountDetails['id']);
                    } else {
                        $accounts = $this->accounts->create();
                    }
                    $accounts->addData($accountDetails)->save();
                    $this->messageManager->addSuccessMessage(__('Account Saved Successfully.'));
                    $this->_redirect('*/*/edit', ['id' => $accounts->getId()]);
                } else {
                    $this->messageManager->addNoticeMessage(__('Please fill the Account Code'));
                    $this->_redirect('*/*/new');
                }
            } catch (\Exception $e) {
                $this->_objectManager->create('Ced\Onbuy\Helper\Logger')->addError('In Save Account: ' . $e->getMessage(), ['path' => __METHOD__]);
                $this->messageManager->addErrorMessage(__('Unable to Save Account Details Please Try Again.' . $e->getMessage()));
                $this->_redirect('*/*/new');
            }
            return;
        } else {
            $this->messageManager->addErrorMessage('Account Code Already Exists');
            $this->_redirect('*/*/new');
        }
    }

    private function validate()
    {
        $accountCode[] = $this->getRequest()->getParam('account_code');
        $accountEnv[] = $this->getRequest()->getParam('account_env');
        $accountLocation[] = $this->getRequest()->getParam('account_location');
        $accountStatus[] = $this->getRequest()->getParam('account_status');
        $accountStore[] = $this->getRequest()->getParam('account_store');
        $accountId[] = $this->getRequest()->getParam('id');

        if (!empty($accountCode)) {

            $this->data->setData('account_code', json_encode($accountCode));
        }
        $this->data->addData($accountCode);

        if (!empty($accountId)) {

            $this->data->setData('id', json_encode($accountId));
        }
        $this->data->addData($accountId);

        if (isset($accountEnv)) {
            $this->data->setData('account_env', json_encode($accountEnv));
        }
        $this->data->addData($accountEnv);


        if (isset($accountStatus)) {
            $this->data->setData('account_status', json_encode($accountStatus));
        }
        $this->data->addData($accountStatus);

        if (isset($accountStore)) {
            $this->data->setData('account_store', json_encode($accountStore));
        }
        $this->data->addData($accountStore);

        $collection = $this->accountCollection->create()->
        addFieldToFilter('account_code', array('eq' => $accountCode));
        if (!empty($collection->getData())) {
            return false;
        }

        return true;

    }
}