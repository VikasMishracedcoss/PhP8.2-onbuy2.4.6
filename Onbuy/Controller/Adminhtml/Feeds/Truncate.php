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

namespace Ced\Onbuy\Controller\Adminhtml\Feeds;

use Magento\Backend\App\Action;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class Truncate
 *
 * @package Ced\Onbuy\Controller\Adminhtml\Log
 */
class Truncate extends Action
{
    /**
     * @var PageFactory
     */
    public $resultPageFactory;

    /**
     * @var \Magento\Cron\Model\ResourceModel\Schedule\CollectionFactory $logs
     */
    public $logs;

    /**
     * @var \Magento\Framework\Filesystem\Io\File
     */
    public $fileIo;

    /**
     * Truncate constructor.
     * @param Action\Context $context
     * @param PageFactory $resultPageFactory
     * @param Ced\Onbuy\Model\ResourceModel\Feeds\CollectionFactory $feeds
     */
    public function __construct(
        Action\Context $context,
        PageFactory $resultPageFactory,
        \Ced\Onbuy\Model\ResourceModel\Feeds\CollectionFactory $feeds
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->feeds = $feeds;
    }

    public function execute()
    {
        $status = false;
        $collection = $this->feeds->create();
        if (isset($collection) and $collection->getSize() > 0) {
            $status = true;
            $collection->walk('delete');
        }

        if ($status) {
            $this->messageManager->addSuccessMessage('Feeds deleted successfully.');
        } else {
            $this->messageManager->addNoticeMessage('No Feeds to delete.');
        }

        $this->_redirect('onbuy/feeds/index');
    }

    /**
     * IsALLowed
     * @return boolean
     */
    public function _isAllowed()
    {
        return $this->_authorization->isAllowed('Ced_Onbuy::Onbuy');
    }
}
