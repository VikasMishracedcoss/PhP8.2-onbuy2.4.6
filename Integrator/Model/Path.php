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

class Path
{
    public $coreSession;
    public function __construct(
        \Magento\Framework\Session\SessionManagerInterface $coreSession
    ) {
        $this->coreSession = $coreSession;
    }

    public function getFiles()
    {
        $moduleName = $this->coreSession->getModule();

        if ($moduleName == 'Amazon') {
            $file = "\Ced\\" . $moduleName . "\\Helper\Directory\Data";
        } else {
            $file = "\Ced\\" . $moduleName . "\\Helper\Data";
        }
        $filePath = $file::CED_EXTENSION;

        return $filePath;
    }

    public function dashboardFilePath($moduleName)
    {
        if ($moduleName == 'Amazon') {
            $file = "\Ced\\" . $moduleName . "\\Helper\Directory\Data";
        } else {
            $file = "\Ced\\" . $moduleName . "\\Helper\Data";
        }
        $filePath = $file::CED_EXTENSION;

        return $filePath;
    }

    public function getCollectionFile()
    {
        $collection=$this->getFiles();
        $collectionFilePath=$collection['Collection'].'Factory';
        return $collectionFilePath;
    }

    public function getAccountFile()
    {
        $collection=$this->getFiles();
        $accountFilePath=$collection['Account'].'Factory';
        return $accountFilePath;
    }

    public function getPaymentFile()
    {
        $collection=$this->getFiles();
        $paymentFilePath=$collection['Payment'];
        return $paymentFilePath;
    }

    public function moduleName()
    {
        $moduleName = $this->coreSession->getModule();
        return $moduleName;
    }

    public function accountId()
    {
        $moduleName = $this->coreSession->getAccount();
        return $moduleName;
    }
}
