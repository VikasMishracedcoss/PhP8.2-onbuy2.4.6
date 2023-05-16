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
 * @copyright   Copyright © 2018 CedCommerce. All rights reserved.
 * @license     EULA http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Integrator\Model;

class Cleaner
{
    /** @var \Magento\Cron\Model\ResourceModel\Schedule\CollectionFactory */
    public $cronCollectionFactory;

    /** @var ResourceModel\Log\CollectionFactory */
    public $logCollectionFactory;

    /** @var \Ced\Integrator\Helper\Logger */
    public $logger;

    public function __construct(
        \Ced\Integrator\Model\ResourceModel\Log\CollectionFactory $log,
        \Magento\Cron\Model\ResourceModel\Schedule\CollectionFactory $cronCollectionFactory,
        \Ced\Integrator\Helper\File\Logger $logger
    ) {
        $this->logCollectionFactory = $log;
        $this->logger = $logger;
        $this->cronCollectionFactory = $cronCollectionFactory;
    }
    
    public function clean($from = null, $to = null)
    {
        /** @var \Ced\Integrator\Model\ResourceModel\Log\Collection $records */
        $records = $this->logCollectionFactory->create();
        if (empty($from)) {
            $from = date('Y-m-d H:i:s', strtotime('-7 days'));
        }

        if (empty($to)) {
            $to = date('Y-m-d H:i:s', strtotime('now'));
        }

        $records = $records->addFieldToFilter(
            'datetime',
            [
                /*'from' => $from,*/
                'to' => $from,
                'date' => true,
            ]
        );

        /*$records->setPageSize(1000)
            ->setCurPage(1)
            ->setOrder('id','ASC');*/

        $size = $records->getSize();
        if ($size > 0) {
            $records->walk('delete');
            $this->logger->addNotice("{$size} logs cleared from db.");
            return true;
        }

        return false;
    }


    public function removeStuckedCron($from = null, $to = null)
    {
        /** @var \Magento\Cron\Model\ResourceModel\Schedule\CollectionFactory $records */
        $records = $this->cronCollectionFactory->create();
        if (empty($from)) {
            $from = date('Y-m-d H:i:s', strtotime('-1 hour'));
        }

        $records = $records
            ->addFieldToFilter('status', ['eq' => 'running'])
            ->addFieldToFilter('job_code', ['like' => '%ced_%'])
            ->addFieldToFilter(
                'executed_at',
                [
                    'to' => $from,
                    'date' => true,
                ]
            );

        $size = $records->getSize();
        if ($size > 0) {
            $records->walk('delete');
            $this->logger->addNotice("{$size} cron cleared from db.");
            return true;
        }

        return false;
    }
}
