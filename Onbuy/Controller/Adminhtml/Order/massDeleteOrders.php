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

namespace Ced\Onbuy\Controller\Adminhtml\Order;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class massDeleteOrders extends \Magento\Backend\App\Action
{
    /**
     * ResultPageFactory
     * @var PageFactory
     */
    public $resultPageFactory;

    public $helper;


    public $filter;

    public $trademeOrdersCollectionFactory;


    public $trademeOrdersFactory;

    /**
     * FailedOrders constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param \Ced\Onbuy\Helper\Order $helper
     */
    public function __construct(
        \Ced\Onbuy\Model\ResourceModel\Orders\CollectionFactory $trademeOrdersCollectionFactory,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Ced\Onbuy\Model\OrdersFactory $trademeOrdersFactory,
        Context $context,
        PageFactory $resultPageFactory
    )
    {
        $this->filter = $filter;
        $this->trademeOrdersCollectionFactory = $trademeOrdersCollectionFactory;
        $this->trademeOrdersFactory = $trademeOrdersFactory;
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * Execute
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $dataPost = $this->getRequest()->getParam('filters');
      
        if($dataPost) {
            $trademeOrdersModelIds = $this->filter->getCollection($this->trademeOrdersCollectionFactory->create())->getAllIds();
        } else {
            $trademeOrdersModelIds[] = $this->getRequest()->getParam('id');
        }

        if(isset($trademeOrdersModelIds)) {
            try {
                foreach ($trademeOrdersModelIds as $trademeOrdersModelId) {
                    $this->trademeOrdersFactory->create()
                        ->load($trademeOrdersModelId)
                        ->delete();
                }
                $count = count($trademeOrdersModelIds);
                if($count) {
                    $this->messageManager->addSuccess(
                        __($count .' Order(s) Delete Succesfully')
                    );
                }
                else {
                    $this->messageManager->addErrorMessage(__(' Order Not Deleted '));
                }
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__(''.$e->getMessage()));
            }
        }
        else {
            $this->messageManager->addErrorMessage(__('Please Select Order '));
        }
        return $this->_redirect('*/*/index');
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