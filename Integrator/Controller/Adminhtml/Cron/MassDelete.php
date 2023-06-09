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
 * @package     Ced_Integrator
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license     http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Integrator\Controller\Adminhtml\Cron;

/**
 * Class MassDelete
 * @package Ced\Integrator\Controller\Adminhtml\Cron
 */
class MassDelete extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Ced_Integrator::cron_log';

    /**
     * @var \Magento\Cron\Model\ResourceModel\Schedule\CollectionFactory $crons
     */
    public $crons;

    /**
     * @var \Magento\Ui\Component\MassAction\Filter
     */
    public $filter;

    /**
     * MassDelete constructor.
     * @param Action\Context $context
     * @param \Magento\Ui\Component\MassAction\Filter $filter
     * @param \Magento\Cron\Model\ResourceModel\Schedule\CollectionFactory $crons
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Magento\Cron\Model\ResourceModel\Schedule\CollectionFactory $crons
    ) {
        parent::__construct($context);
        $this->filter = $filter;
        $this->crons = $crons;
    }

    public function execute()
    {
        $schedulerIds = $this->filter->getCollection($this->crons->create())->getAllIds();
        if (!empty($schedulerIds)) {
            try {
                foreach ($schedulerIds as $id) {
                    $cron = $this->_objectManager->create(\Magento\Cron\Model\Schedule::class)->load($id);
                    $cron->delete();
                }
                $this->messageManager->addSuccessMessage(__('Total of %1 record(s) have been deleted.', count($schedulerIds)));
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }
}
