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

namespace Ced\Onbuy\Block\Adminhtml\Account\Edit\Tab;

/**
 * Class Info
 * @package Ced\Onbuy\Block\Adminhtml\Account\Edit\Tab
 */
class Info extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * Info constructor.
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Framework\ObjectManagerInterface $objectInterface
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\ObjectManagerInterface $objectInterface,
        array $data = []
    )
    {
        $this->_coreRegistry = $registry;
        $this->_objectManager = $objectInterface;
        parent::__construct($context, $registry, $formFactory);
    }

    /**
     * @return \Magento\Backend\Block\Widget\Form\Generic
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareForm()
    {
        $form = $this->_formFactory->create();
        $id = $this->getRequest()->getParam('id');
        if ($id) {
            $account = $this->_objectManager->get('Ced\Onbuy\Model\Accounts')->load($id);
        } else {
            $account = $this->_objectManager->get('Ced\Onbuy\Model\Accounts');
        }

        $fieldset = $form->addFieldset('Account_info', ['legend' => __('Account Information')]);

        $fieldset->addField('account_code', 'text',
            [
                'name' => "account_code",
                'label' => __('Account Code'),
                'note' => __('To Identify the Account'),
                'required' => true,
                'note' => __('For internal use. Must be unique with no spaces'),
                'class' => 'validate-code',
                'value' => $account->getData('account_code'),
            ]
        );

        $fieldset->addField('account_env', 'select',
            array(
                'name' => "account_env",
                'label' => __('Account Environment'),
                'required' => true,
                'value' => $account->getData('account_env'),
                'values' => $this->_objectManager->get('Ced\Onbuy\Model\Config\Environment')->getOptionArray(),
            )
        );

        $fieldset->addField('account_status', 'select',
            array(
                'name' => "account_status",
                'label' => __('Account Status'),
                'required' => true,
                'value' => $account->getData('account_status'),
                'values' => $this->_objectManager->get('Ced\Onbuy\Model\Source\Account\Status')->getOptionArray(),
            )
        );

        $fieldset->addField('account_store', 'select',
            array(
                'name' => "account_store",
                'label' => __('Account Store'),
                'required' => true,
                'value' => $account->getData('account_store'),
                'values' => $this->_objectManager->get('Magento\Config\Model\Config\Source\Store')->toOptionArray(),
            )
        );
        $fieldset->addField('consumer_key', 'text',
            [
                'name' => "consumer_key",
                'label' => __('Consumer Key'),
                'required' => true,
                'value' => $account->getData('consumer_key'),
            ]
        );
        $fieldset->addField('consumer_secret', 'text',
            [
                'name' => "consumer_secret",
                'label' => __('Consumer Secret'),
//                'note' => __('Credentials for Fetching Token'),
                'required' => true,
                'value' => $account->getData('consumer_secret'),
            ]
        );
        $fieldset->addField('seller_id', 'text',
            [
                'name' => "seller_id",
                'label' => __('Seller Id'),
                'required' => true,
                'value' => $account->getData('seller_id'),
            ]
        );
        $fieldset->addField('seller_entity_id', 'text',
            [
                'name' => "seller_entity_id",
                'label' => __('Seller Entity Id'),
                'required' => true,
                'value' => $account->getData('seller_entity_id'),
            ]
        );$fieldset->addField('site_id', 'text',
            [
                'name' => "site_id",
                'label' => __('Site Id'),
                'note' => "The OnBuy Regional site id e.g. 2000 for UK.",
                'required' => true,
                'value' => $account->getData('site_id'),
            ]
        );


        if ($account->getId()) {
            $form->getElement('account_code')->setDisabled(1);
        }
//        $form->getElement('access_token')->setDisabled(1);
        $this->setForm($form);
        return parent::_prepareForm();
    }
}