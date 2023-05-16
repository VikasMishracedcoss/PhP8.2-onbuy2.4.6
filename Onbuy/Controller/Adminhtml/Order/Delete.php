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

/**
 * Class Delete
 * @package Ced\Onbuy\Controller\Adminhtml\Order
 */
class Delete extends \Magento\Customer\Controller\Adminhtml\Group
{
    const ADMIN_RESOURCE = 'Ced_Onbuy::Onbuy';
    /**
     * @var
     */
    protected $_objectManager;
    /**
     * @var
     */
    protected $_session;

    /**
     * @return $this|void
     */
    public function execute()
    {
        $code = $this->getRequest()->getParam('id');

        $resultRedirect = $this->resultRedirectFactory->create();
        if ($code) {
            $model = $this->_objectManager->create('Ced\Onbuy\Model\Orders')->getCollection()->addFieldToFilter('id', $code);
            // entity type check
            try {
                foreach ($model as $value) {
                    if($code == $value->getData('id')){
                        $value->delete();
                    }
                }
                $this->messageManager->addSuccessMessage(__('You deleted the order row.'));
            } catch (\Exception $e) {
                $this->_objectManager->create('Ced\Onbuy\Helper\Logger')->addError('In Delete Order Row: '.$e->getMessage(), ['path' => __METHOD__]);
                $this->messageManager->addErrorMessage($e->getMessage());
                return $resultRedirect->setPath('onbuy/order/index');
            }
        }
        $this->_redirect('onbuy/order/index');
        return ;
    }
}

