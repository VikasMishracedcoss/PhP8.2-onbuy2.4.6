<?php

namespace Ced\Integrator\Block\Adminhtml\Dashboard;

use Magento\Framework\View\Element\Template;

class Switcher extends Template
{

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Data\Form\FormKey $formKey,
        \Ced\Integrator\Model\Path $path,
        \Magento\Framework\Session\SessionManagerInterface $coreSession,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        array $data = []
    ) {
        $this->objectManager = $objectManager;
        $this->coreSession = $coreSession;
        $this->formKey = $formKey;
        $this->path = $path;
        parent::__construct($context, $data);
    }

    public function getModuleName()
    {
        return $this->path->moduleName();
    }

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

    public function accountList($moduleName)
    {
        $collectionFilePath = $this->path->getCollectionFile();
        $collection =$this->objectManager->get($collectionFilePath);
        $accountFilePath=$this->path->getAccountFile();

        switch ($moduleName) {
            case "EbayMultiAccount":
                // $accountCollection = $collection->create();
                // $accountCollection->getSelect()->group('account_id');
                // foreach ($accountCollection as $value) {
                //     $accountIds[] = $value['account_id'];
                // }
                $accounts = $this->objectManager->get($accountFilePath);
                $accountFilter = $accounts->create()->addFieldToSelect('*');
                    // ->addFieldToFilter('id', ['in' => $accountIds]);
                foreach ($accountFilter as $value) {
                    $accountcollection[] = [
                        'id' => $value['id'],
                        'code' => $value['account_code']
                    ];
                }
                return $accountcollection;

            case "Amazon":
                // $accountCollection = $collection->create();
                // $accountCollection->getSelect()->group('account_id');
                // foreach ($accountCollection as $value) {
                //     $accountIds[] = $value['account_id'];
                // }
                // if(!empty($accountIds))
                // {
                $accounts = $this->objectManager->get($accountFilePath);
                $accountFilter = $accounts->create()->addFieldToSelect('*');
                    // ->addFieldToFilter('id', ['in' => $accountIds]);
                foreach ($accountFilter as $value) {
                    $accountcollection[] = [
                        'id' => $value['id'],
                        'code' => $value['name']
                    ];
                }
                return $accountcollection;
            // }else {
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
    
    public function getCurrentAccount()
    {
        $detail = [
            'id' => $this->coreSession->getAccount(),
            'name' => $this->coreSession->getAccountName(),
            'module'=>$this->coreSession->getModule()
        ];

        return $detail;
    }
}
