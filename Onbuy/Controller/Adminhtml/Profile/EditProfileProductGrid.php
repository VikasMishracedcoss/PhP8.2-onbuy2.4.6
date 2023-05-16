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
 * @license      http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Onbuy\Controller\Adminhtml\Profile;

use Magento\Framework\View\Result\PageFactory;

/**
 * Class EditProfileProductGrid
 * @package Ced\Onbuy\Controller\Adminhtml\Profile
 */
class EditProfileProductGrid extends \Magento\Backend\App\Action
{
    /**
     * @var \Ced\Onbuy\Helper\MultiAccount
     */
    protected $multiAccountHelper;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    const ADMIN_RESOURCE = 'Ced_Onbuy::Onbuy';

    /**
     * EditProfileProductGrid constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Ced\Onbuy\Helper\MultiAccount $multiAccountHelper
    )
    {
        parent::__construct($context);
        $this->multiAccountHelper = $multiAccountHelper;
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $profileId = $this->getRequest()->getParam('id');
        $profileCode = $this->getRequest()->getParam('pcode');
        if ($profileCode) {
            $profile = $this->_objectManager->create('Ced\Onbuy\Model\Profile')->getCollection()->addFieldToFilter('profile_code', $profileCode)->getFirstItem();
        } else {
            $profile = $this->_objectManager->create('Ced\Onbuy\Model\Profile');
        }
        /*$this->_coreRegistry = $this->_objectManager->create('\Magento\Framework\Registry');
        $this->_coreRegistry->register('current_profile', $profile);*/
        $this->multiAccountHelper->getProfileRegistry($profile);
        $account = $this->multiAccountHelper->getAccountRegistry($profile->getAccountId());
        return $this->resultPageFactory->create();
    }
}
