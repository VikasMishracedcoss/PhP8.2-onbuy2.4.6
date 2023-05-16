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
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license     http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Onbuy\Controller\Adminhtml\Account;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class Index
 * @package Ced\Onbuy\Controller\Adminhtml\Account
 */
class Index extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Ced_Onbuy::Onbuy';
    /**
     * ResultPageFactory
     * @var PageFactory
     */
    public $resultPageFactory;
    
    public $num;
    
    public $bookmarkRepository;
    /**
     * Index constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Magento\Ui\Api\BookmarkRepositoryInterface $bookmarkRepository
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->bookmarkRepository = $bookmarkRepository;
    }

    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        // $bookmarkId='onbuy_product_listing';
        // $bookmark = $this->_bookmarkRepository->load(5);
        // echo '<pre>';
        // print_r($bookmark);
        // die(__FILE__);
        // $this->_bookmarkRepository->delete($bookmarkId);

        // $orderid = 6;
        // $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        // $order = $objectManager->create('Magento\Ui\Model\Bookmark')->load(2);
         
        // //fetch whole order information
        // echo '<pre>';
        // print_r($order->getData());
        // die(__FILE__);
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Ced_Onbuy::Onbuy');
        $resultPage->getConfig()->getTitle()->prepend(__('Manage Account'));
        return $resultPage;
    }
}