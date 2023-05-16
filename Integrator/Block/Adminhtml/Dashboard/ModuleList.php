<?php

namespace Ced\Integrator\Block\Adminhtml\Dashboard;

use Magento\Framework\View\Element\Template;

class ModuleList extends Template
{

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Sales\Model\ResourceModel\Order\Grid\CollectionFactory $ordergrid,
        \Magento\Framework\Module\ModuleList $enableModuleList,
        \Magento\Framework\Data\Form\FormKey $formKey,
        \Ced\Integrator\Model\Path $path,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Module\FullModuleList $allModuleList,
        \Magento\Framework\Pricing\PriceCurrencyInterface $currency,
        \Magento\Store\Model\StoreManagerInterface $store,
        array $data = []
    ) {
        $this->allModuleList = $allModuleList;
        $this->objectManager = $objectManager;
        $this->ordergrid = $ordergrid;
        $this->path = $path;
        $this->store = $store;
        $this->formKey = $formKey;
        $this->enableModuleList = $enableModuleList;
        $this->_currency = $currency;
        parent::__construct($context, $data);
    }

    /**Function Used To
     *  Get the List of Enable
     *  Module That This Particular Extension
     *  Support
     *  */
    public function modulesList()
    {
        $module = [];
        $dashboardextension = ['Amazon', 'EbayMultiAccount', 'Walmart'];
        $allModules = $this->enableModuleList->getNames();
        foreach ($allModules as $values) {
            if (substr($values, 0, 3) === "Ced") {
                $moduleName = substr($values, 4);
                if (in_array($moduleName, $dashboardextension)) {
                    $module[] = $moduleName;
                }
            }
        }
        return $module;
    }

    /**Function Used To
     *  Get the List of All
     *  Enable Module That Have
     *  Ced As Vendor
     *  */
    public function enableModuleList()
    {

        $module = [];
        $enabledModules = $this->enableModuleList->getNames();
        foreach ($enabledModules as $modules) {
            if (substr($modules, 0, 3) === "Ced") {
                $moduleName = substr($modules, 4);
                if ($moduleName != 'Integrator') {
                    $module[] = $moduleName;
                }
            }
        }
        return $module;
    }

    /**Function Used To
     *  Get the List of All
     *  Disable Module That Have
     *  Ced As Vendort
     *  */
    public function disableModuleList()
    {
        $module = [];
        $allModules = $this->allModuleList->getNames();
        $enabledModules = $this->enableModuleList->getNames();
        $disabledModules = array_diff($allModules, $enabledModules);

        foreach ($disabledModules as $modules) {
            if (substr($modules, 0, 3) === "Ced") {
                $moduleName = substr($modules, 4);
                $module[] = $moduleName;
            }
        }
        return $module;
    }

    /**Function Used
     * To Return The Form
     * Key
     * */
    public function getFormKey()
    {
        return $this
            ->formKey
            ->getFormKey();
    }

    public function getFormAction()
    {
        return $this->getUrl('integrator/dashboard/index', ['_secure' => true]);
    }

    /**Function Used
     * To Return All The
     * Account Id of Particular Marketplace
     * */
    public function accountList($moduleName)
    {
        switch ($moduleName) {

            case "EbayMultiAccount":
                $accountcollection = [];
                /**Getting Module File Paths */
                $filePath = $this->path->dashboardFilePath($moduleName);
                // $collectionPath=$filePath['Collection'].'Factory';
                $accountPath = $filePath['Account'] . 'Factory';
                // $account =  $this->objectManager->get($collectionPath);
                // $accountId = $account->create();
                // $accountId->getSelect()->group('account_id');
                // foreach ($accountId as $value) {
                //     $accountIds[] = $value['account_id'];
                // }
                // if (isset($accountIds)) {
                $accounts = $this->objectManager->get($accountPath);
                $accountdetail = $accounts->create()->addFieldToSelect('*');
                // ->addFieldToFilter('id', ['in' => $accountIds]);
                foreach ($accountdetail as $value) {
                    $accountcollection[] = [
                        'id' => $value['id'],
                        'code' => $value['account_code']
                    ];
                }
                return $accountcollection;
                // } else {
                //     return [];
                // }
            case "Amazon":
                $accountcollection = [];
                /**Getting Module File Paths */
                $filePath = $this->path->dashboardFilePath($moduleName);
                // $collectionPath=$filePath['Collection'].'Factory';
                $accountPath = $filePath['Account'] . 'Factory';
                // $account = $this->objectManager->get($collectionPath);
                // $accountId = $account->create();
                // $accountId->getSelect()->group('account_id');
                // foreach ($accountId as $value) {
                //     $accountIds[] = $value['account_id'];
                // }
                // if (isset($accountIds)) {
                $accounts = $this->objectManager->get($accountPath);
                $accountdetail = $accounts->create()->addFieldToSelect('*');
                // ->addFieldToFilter('id', ['in' => $accountIds]);
                foreach ($accountdetail as $value) {
                    $accountcollection[] = [
                        'id' => $value['id'],
                        'code' => $value['name']
                    ];
                }
                return $accountcollection;
                // } else {
                //     return [];
                // }
            case "Walmart":
                $accountIds = [
                    0 => [
                        'id' => 'Walmart',
                        'code' => 'Walmart'
                    ]
                ];
                return $accountIds;
        }
    }


    public function getModuleDetail($moduleName)
    {
        $filePath = $this->path->dashboardFilePath($moduleName);
        $accountPath = $filePath['Account'] . 'Factory';
        $fileCollection = $this->path->dashboardFilePath($moduleName);
        $path = $fileCollection['Payment'];
        $paymentName = ($path::METHOD_CODE);
        $ordercollection = $this->ordergrid->create()->AddFieldToSelect('*')
        ->addFieldToFilter('payment_method', $paymentName)
        ->addFieldToFilter('status', 'complete');

    $ordersize = $ordercollection->getSize();
        switch ($moduleName) {
            case "EbayMultiAccount":
                $ordercollection = $this->ordergrid->create()->AddFieldToSelect('*')
                    ->addFieldToFilter('payment_method', $paymentName)
                    ->addFieldToFilter('status', 'complete')
                    ->addExpressionFieldToSelect('this_is_total', 'SUM({{base_grand_total}})', 'base_grand_total');

                foreach ($ordercollection as $values) {
                    $orderTotal = $values['this_is_total'];
                }

                $accounts = $this->objectManager->get($accountPath);
                $accountdetail = $accounts->create();
                $accountSize = $accountdetail->getSize();

                $data = [
                    'totalOrder' => $ordersize,
                    'totalSale' => $orderTotal,
                    'totalAccount' => $accountSize
                ];

                return $data;

            case "Amazon":
                $ordercollection = $this->ordergrid->create()->AddFieldToSelect('*')
                    ->addFieldToFilter('payment_method', $paymentName)
                    ->addFieldToFilter('status', 'complete')
                    ->addExpressionFieldToSelect('this_is_total', 'SUM({{base_grand_total}})', 'base_grand_total');

                foreach ($ordercollection as $values) {
                    $orderTotal = $values['this_is_total'];
                }

                $accounts = $this->objectManager->get($accountPath);
                $accountdetail = $accounts->create();
                $accountSize = $accountdetail->getSize();

                $data = [
                    'totalOrder' => $ordersize,
                    'totalSale' => $orderTotal,
                    'totalAccount' => $accountSize
                ];

                return $data;

            case "Walmart":
                $ordercollection = $this->ordergrid->create()->AddFieldToSelect('*')
                    ->addFieldToFilter('payment_method', ['in' => 'paybywalmart'])
                    ->addFieldToFilter('status', 'complete')
                    ->addExpressionFieldToSelect('this_is_total', 'SUM({{base_grand_total}})', 'base_grand_total');

                foreach ($ordercollection as $values) {
                    $orderTotal = $values['this_is_total'];
                }

                $data = [
                    'totalOrder' => $ordersize,
                    'totalSale' => $orderTotal,
                    'totalAccount' => 0,
                ];

                return $data;
        }
    }

    public function getMediaUrl()
    {
        $media_dir = $this->store
            ->getStore()
            ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);

        return $media_dir;
    }

    protected function _prepareLayout()
    {
        $this->addChild('data', \Ced\Integrator\Block\Adminhtml\Dashboard\OrderData::class);
        $this->addChild('best', \Ced\Integrator\Block\Adminhtml\Dashboard\Order\MonthlySale::class);
    }

    public function getCurrentCurrencySymbol()
    {
        return $this->_currency->getCurrencySymbol('default');
    }
}
