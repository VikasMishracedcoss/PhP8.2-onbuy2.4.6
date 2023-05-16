<?php


namespace Ced\Onbuy\Helper;

use function GuzzleHttp\Psr7\str;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Framework\App\Helper\Context;

/**
 * Class MultiAccount
 * @package Ced\Onbuy\Helper
 */
class MultiAccount extends \Magento\Framework\App\Helper\AbstractHelper
{
    public $eavAttribute;
    public $eavSetup;
    protected $accountsCollectionFactory;
    protected $accountModel;
    protected $_coreRegistry;
    protected $profileModel;
    public $logger;
    public $numberFormatter;

    public function __construct(Context $context,
        \Magento\Catalog\Model\ResourceModel\Eav\Attribute $eavAttribute,
        \Magento\Framework\Setup\ModuleDataSetupInterface $setup,
        \Ced\Onbuy\Model\ResourceModel\Accounts\CollectionFactory $accountsCollectionFactory,
        \Magento\Eav\Setup\EavSetupFactory $eavSetupFactory,
        \Ced\Onbuy\Model\AccountsFactory $accounts,
        Logger $logger,
        \Magento\Framework\Registry $coreRegistry,
        \NumberFormatterFactory $numberFormatter
    )
    {
        parent::__construct($context);
        $this->numberFormatter = $numberFormatter;
        $this->eavAttribute = $eavAttribute;
        $this->accountModel = $accounts;
        $this->logger = $logger;
        $this->eavSetup = $eavSetupFactory->create(['setup' => $setup]);
        $this->accountsCollectionFactory = $accountsCollectionFactory;
        $this->_coreRegistry = $coreRegistry;

    }

    public function createProfileAttribute($accId = null, $accName = null)
    {
        $n = $this->numberFormatter->create([
            'locale' => 'en',
            'style' => \NumberFormatter::SPELLOUT
        ]);


        $word =  $n->format($accId);
        $word = str_replace('-', '_', $word);
        $attributeCode = 'tm_profile_' . $word;
        $attributeLabel = 'Onbuy ' . $accName . ' Profile';
        if (!$this->eavAttribute->getIdByCode('catalog_product', $attributeCode)) {
            $this->eavSetup->addAttribute(
                'catalog_product',
                $attributeCode,
                [
                    'group' => 'trademe',
                    'input' => 'select',
                    'type' => 'text',
                    'label' => $attributeLabel,
                    'backend' => '',
                    'visible' => true,
                    'required' => false,
                    'sort_order' => 10,
                    'user_defined' => true,
                    'source' => 'Ced\Onbuy\Model\Config\Profile',
                    'comparable' => false,
                    'visible_on_front' => false,
                    'global' => ScopedAttributeInterface::SCOPE_GLOBAL
                ]
            );
        }
        $attributeCode = 'tm_list_id_' . $word;
        $attributeLabel = 'Onbuy ' . $accName . ' Listing ID';
        if (!$this->eavAttribute->getIdByCode('catalog_product', $attributeCode)) {
            $this->eavSetup->addAttribute(
                'catalog_product',
                $attributeCode,
                [
                    'group' => 'trademe',
                    'input' => 'text',
                    'type' => 'text',
                    'label' => $attributeLabel,
                    'backend' => '',
                    'visible' => true,
                    'required' => false,
                    'sort_order' => 10,
                    'user_defined' => true,
                    'comparable' => false,
                    'visible_on_front' => false,
                    'global' => ScopedAttributeInterface::SCOPE_GLOBAL
                ]
            );
        }
        $attributeCode = 'tm_list_status_' . $word;
        $attributeLabel = 'Onbuy ' . $accName . ' Listing Status';
        if (!$this->eavAttribute->getIdByCode('catalog_product', $attributeCode)) {
            $this->eavSetup->addAttribute(
                'catalog_product',
                $attributeCode,
                [
                    'group' => 'trademe',
                    'input' => 'select',
                    'type' => 'text',
                    'label' => $attributeLabel,
                    'backend' => '',
                    'visible' => true,
                    'required' => false,
                    'sort_order' => 10,
                    'user_defined' => true,
                    'source' => 'Ced\Onbuy\Model\Config\Status',
                    'comparable' => false,
                    'visible_on_front' => false,
                    'global' => ScopedAttributeInterface::SCOPE_GLOBAL
                ]
            );
        }
        $attributeCode = 'tm_list_error_' . $word;
        $attributeLabel = 'Onbuy ' . $accName . ' Product Validation';
        if (!$this->eavAttribute->getIdByCode('catalog_product', $attributeCode)) {
            $this->eavSetup->addAttribute(
                'catalog_product',
                $attributeCode,
                [
                    'group' => 'trademe',
                    'input' => 'text',
                    'type' => 'text',
                    'label' => $attributeLabel,
                    'backend' => '',
                    'visible' => true,
                    'required' => false,
                    'sort_order' => 10,
                    'user_defined' => true,
                    'comparable' => false,
                    'visible_on_front' => false,
                    'global' => ScopedAttributeInterface::SCOPE_GLOBAL
                ]
            );
        }
        $attributeCode = 'tm_photo_id_' . $word;
        $attributeLabel = 'Onbuy ' . $accName . ' Photo Id';
        if (!$this->eavAttribute->getIdByCode('catalog_product', $attributeCode)) {
            $this->eavSetup->addAttribute(
                'catalog_product',
                $attributeCode,
                [
                    'group' => 'trademe',
                    'input' => 'text',
                    'type' => 'text',
                    'label' => $attributeLabel,
                    'backend' => '',
                    'visible' => true,
                    'required' => false,
                    'sort_order' => 10,
                    'user_defined' => true,
                    'comparable' => false,
                    'visible_on_front' => false,
                    'global' => ScopedAttributeInterface::SCOPE_GLOBAL
                ]
            );
        }

        $attributeCode = 'tm_uptime_' . $word;
        $attributeLabel = 'Onbuy ' . $accName . ' Upload Time';
        if (!$this->eavAttribute->getIdByCode('catalog_product', $attributeCode)) {
            $this->eavSetup->addAttribute(
                'catalog_product',
                $attributeCode,
                [
                    'group' => 'trademe',
                    'input' => 'text',
                    'type' => 'text',
                    'label' => $attributeLabel,
                    'backend' => '',
                    'visible' => true,
                    'required' => false,
                    'sort_order' => 10,
                    'user_defined' => true,
                    'comparable' => false,
                    'visible_on_front' => false,
                    'global' => ScopedAttributeInterface::SCOPE_GLOBAL
                ]
            );
        }

    }
    public function getAllAccounts($onlyActive = false) {
        if($onlyActive)
            $accountCollection = $this->accountsCollectionFactory->create()->addFieldToFilter('account_status', 1);
        else
            $accountCollection = $this->accountsCollectionFactory->create();
        return $accountCollection;
    }
    public function getAccountRegistry($accId = null) {
        /** @var \Ced\Onbuy\Model\Accounts $account */
        $account = $this->accountModel->create();
        if (isset($accId) && $accId > 0) {
            $account = $account->load($accId);
        }
        if(!$this->_coreRegistry->registry('onbuy_account'))
            $this->_coreRegistry->register('onbuy_account', $account);
        return $this->_coreRegistry->registry('onbuy_account');
    }
    public function deleteAccountAssociates($accountIds = array()) {
        try {
            if (is_array($accountIds) && count($accountIds) > 0) {
                $accountProfiles = $this->profileCollectionFactory->create()->addFieldToFilter('account_id', array('in', $accountIds));
                if (isset($accountProfiles) and $accountProfiles->getSize() > 0) {
                    $accountProfiles->walk('delete');
                }
                foreach ($accountIds as $accountId) {
                    $n = new \NumberFormatter('en', \NumberFormatter::SPELLOUT);
                    $word =  $n->format($accountId);
                    $word = str_replace('-', '_', $word);
                    $attributeCode = 'tm_profile_' . $word;
                    if ($this->eavAttribute->getIdByCode('catalog_product', $attributeCode)) {
                        $this->eavSetup->removeAttribute('catalog_product', $attributeCode);
                    }
                    $attributeCode = 'tm_list_id_' . $word;
                    if ($this->eavAttribute->getIdByCode('catalog_product', $attributeCode)) {
                        $this->eavSetup->removeAttribute('catalog_product', $attributeCode);
                    }
                    $attributeCode = 'tm_list_status_' . $word;
                    if ($this->eavAttribute->getIdByCode('catalog_product', $attributeCode)) {
                        $this->eavSetup->removeAttribute('catalog_product', $attributeCode);
                    }
                    $attributeCode = 'tm_list_error_' . $word;
                    if ($this->eavAttribute->getIdByCode('catalog_product', $attributeCode)) {
                        $this->eavSetup->removeAttribute('catalog_product', $attributeCode);
                    }
                    $attributeCode = 'tm_photo_id_' . $word;
                    if ($this->eavAttribute->getIdByCode('catalog_product', $attributeCode)) {
                        $this->eavSetup->removeAttribute('catalog_product', $attributeCode);
                    }
                    $attributeCode = 'tm_uptime_' . $word;
                    if ($this->eavAttribute->getIdByCode('catalog_product', $attributeCode)) {
                        $this->eavSetup->removeAttribute('catalog_product', $attributeCode);
                    }

                }
                return true;
            }
            return false;
        } catch (\Exception $e) {
            $this->logger->addError($e->getMessage(), ['path' => __METHOD__]);

            return false;
        }
    }
    public function getProfileAttrForAcc($accId = null) {
        $attributeCode = '';
        $n = new \NumberFormatter('en', \NumberFormatter::SPELLOUT);
        $word =  $n->format($accId);
        $word = str_replace('-', '_', $word);
        if($accId > 0) {
            $attributeCode = 'tm_profile_' . $word;
        } else {
            $attributeCode = '';
        }
        return $attributeCode;
    }

    public function getProdStatusAttrForAcc($accId = null) {
        $attributeCode = '';
        $n = new \NumberFormatter('en', \NumberFormatter::SPELLOUT);
        $word =  $n->format($accId);
        $word = str_replace('-', '_', $word);
        if($accId > 0) {
            $attributeCode = 'tm_list_status_' . $word;
        } else {
            $attributeCode = '';
        }
        return $attributeCode;
    }

    public function getProdPhotoIdAttrForAcc($accId = null) {
        $attributeCode = '';
        $n = new \NumberFormatter('en', \NumberFormatter::SPELLOUT);
        $word =  $n->format($accId);
        $word = str_replace('-', '_', $word);
        if($accId > 0) {
            $attributeCode = 'tm_photo_id_' . $word;
        } else {
            $attributeCode = '';
        }
        return $attributeCode;
    }

    public function getProdListingErrorAttrForAcc($accId = null) {
        $attributeCode = '';
        $n = new \NumberFormatter('en', \NumberFormatter::SPELLOUT);
        $word =  $n->format($accId);
        $word = str_replace('-', '_', $word);
        if($accId > 0) {
            $attributeCode = 'tm_list_error_' . $word;
        } else {
            $attributeCode = '';
        }
        return $attributeCode;
    }

    public function getProdListingIdAttrForAcc($accId = null) {
        $attributeCode = '';
        $n = new \NumberFormatter('en', \NumberFormatter::SPELLOUT);
        $word =  $n->format($accId);
        $word = str_replace('-', '_', $word);
        if($accId > 0) {
            $attributeCode = 'tm_list_id_' . $word;
        } else {
            $attributeCode = '';
        }
        return $attributeCode;
    }

    public function getUploadTimeAttrForAcc($accId = null) {
        $attributeCode = '';
        $n = new \NumberFormatter('en', \NumberFormatter::SPELLOUT);
        $word =  $n->format($accId);
        $word = str_replace('-', '_', $word);
        if($accId > 0) {
            $attributeCode = 'tm_uptime_' . $word;
        } else {
            $attributeCode = '';
        }
        return $attributeCode;
    }

    public function getAccountRegistryByPId($profileId = null, $profileCode = null) {
        if($profileId != null)
            $profile = $this->profileModel->create()->load($profileId);
        else
            $profile = $this->profileModel->create()->load($profileCode, 'profile_code');
        $accId = $profile->getAccountId();
        $account = $this->accountModel->create();
        if (isset($accId) and $accId > 0) {
            $account = $account->load($accId);
        }
        if(!$this->_coreRegistry->registry('trademe_account'))
            $this->_coreRegistry->register('trademe_account', $account);
        return $this->_coreRegistry->registry('trademe_account');
    }

    public function getAllProfileAttr() {
        $attributeCodes = array();
        $accounts = $this->accountsCollectionFactory->create();
        foreach ($accounts as $account) {
            $n = new \NumberFormatter('en', \NumberFormatter::SPELLOUT);
            $word =  $n->format($account);
            $word = str_replace('-', '_', $word);
            $accId = $account->getId();
            if($accId > 0) {
                $attributeCodes[] = 'tm_profile_' . $word;
            }
        }
        return $attributeCodes;
    }
    public function getProfileRegistry($profile = null) {
        $this->_coreRegistry->register('current_profile', $profile);
        return $this->_coreRegistry->registry('current_profile');
    }
}