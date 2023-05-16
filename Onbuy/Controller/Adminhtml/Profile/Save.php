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

namespace Ced\Onbuy\Controller\Adminhtml\Profile;

use Magento\Backend\App\Action\Context;

/**
 * Class Save
 * @package Ced\Onbuy\Controller\Adminhtml\Profile
 */
class Save extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Ced_Onbuy::Onbuy';
    /**
     * @var \Magento\Framework\Registry
     */
    public $_coreRegistry;
    /**
     * @var \Ced\Onbuy\Helper\Cache
     */
    public $_cache;

    /**
     * @var \Ced\Onbuy\Helper\MultiAccount
     */
    protected $multiAccountHelper;

    public $logger;

    /**
     * Save constructor.
     * @param Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Ced\Onbuy\Helper\Cache $cache
     */
     public function __construct(
        Context $context,
        \Ced\Onbuy\Helper\Logger $logger,
        \Magento\Framework\Registry $coreRegistry,
        \Ced\Onbuy\Helper\MultiAccount $multiAccountHelper
    )
    {
        parent::__construct($context);
        $this->_coreRegistry = $coreRegistry;
        $this->logger = $logger;
        //$this->_cache = $cache;
        $this->multiAccountHelper = $multiAccountHelper;
    }

    /**
     * @param string $idFieldName
     * @return mixed
     */
    protected function _initProfile($idFieldName = 'pcode')
    {
        $profileCode = $this->getRequest()->getParam($idFieldName);
        $profile = $this->_objectManager->get('Ced\Onbuy\Model\Profile');
        if ($profileCode) {
            $profile->loadByField('profile_code', $profileCode);
        }
        $this->getRequest()->setParam('is_onbuy', 1);
        $this->_coreRegistry->register('current_profile', $profile);
        return $this->_coreRegistry->registry('current_profile');
    }

    public function execute()
    {
        $trademeAttribute = $trademeReqOptAttribute = [];
        $data = $this->_objectManager->create('Magento\Config\Model\Config\Structure\Element\Group')->getData();
        $redirectBack = $this->getRequest()->getParam('back', false);
        $pcode = $this->getRequest()->getParam('pcode', false);
        $profileData = $this->getRequest()->getPostValue();
      
        $accountId = $this->_session->getAccountId();
        $category = isset($profileData['level_0']) ? $profileData['level_0'] : "";

        $profileData = json_decode(json_encode($profileData), 1);

        $profileProductsStr = $this->getRequest()->getParam('in_profile_products', null);
        if (strlen($profileProductsStr) > 0) {
            $profileProducts = explode(',', $profileProductsStr);
        } else {
            $profileProducts = [];
        }

        try {
            $profile = $this->_initProfile('pcode');
            if (!$profile->getId() && $pcode) {
                $this->messageManager->addErrorMessage(__('This Profile no longer exists.'));
                $this->_redirect('*/*/');
                return;
            }

            if (isset($profileData['profile_code'])) {
                $pcode = $profileData['profile_code'];
                $profileCollection = $this->_objectManager->get('Ced\Onbuy\Model\Profile')->getCollection()->
                addFieldToFilter('profile_code', $profileData['profile_code']);
                if (count($profileCollection) > 0) {
                    $this->messageManager->addErrorMessage(__('This Profile Already Exist Please Change Profile Code'));
                    $this->_redirect('*/*/new');
                    return;
                }
            }

            $profile->addData($profileData);
            $profile->setProfileCategory(/*json_encode*/($category));
            $profile->setProfileCatSearch($profileData['searchcategory']);

            // save attribute
            $reqAttribute = [];
            $optAttribute = [];
          

            //unset($profileData['onbuy_attributes']);
            
            if (!empty($profileData['onbuy_attributes'])) {
              
               
                $temAttribute = $this->unique_multidim_array($profileData['onbuy_attributes'], 'onbuy_attribute_name');

                if (!empty($temAttribute)) {

                    foreach ($temAttribute as $item) {
                        $temp1 = $temp2 = [];
                        if ($item['required']) {
                            $temp1['onbuy_attribute_name'] = $item['onbuy_attribute_name'];
                            $temp1['onbuy_attribute_type'] = $item['onbuy_attribute_type'];
                            $temp1['magento_attribute_code'] = $item['magento_attribute_code'];
                            $temp1['option_mapping'] = $item['option_mapping'];
                            $temp1['options'] = $item['options'];
                            if (isset($item['default'])) {
                                $temp1['default'] = $item['default'];
                            }
                            if (isset($item['units'])) {
                                $temp1['units'] = $item['units'];
                            }
                            $temp1['required'] = $item['required'];
                            $reqAttribute[] = $temp1;
                        } else {
                            $temp2['onbuy_attribute_name'] = $item['onbuy_attribute_name'];
                            $temp2['onbuy_attribute_type'] = $item['onbuy_attribute_type'];
                            $temp2['magento_attribute_code'] = $item['magento_attribute_code'];
                            $temp2['option_mapping'] = $item['option_mapping'];
                            $temp2['options'] = $item['options'];
                            if (isset($item['default'])) {
                                $temp2['default'] = $item['default'];
                            }
                            if (isset($item['units'])) {
                                $temp2['units'] = $item['units'];
                            }
                            $temp2['required'] = $item['required'];
                            $optAttribute[] = $temp2;
                        }
                    }
                    //$reqAttribute=[];
                   // $optAttribute=[];
                    $trademetAttribute['required_attributes'] = $reqAttribute;
                    $trademetAttribute['optional_attributes'] = $optAttribute;
                  
                    $profile->setCatDependAttribute(json_encode($trademetAttribute));
                    
                } else {
                    $trademetAttribute['required_attributes'] = '';
                    $trademetAttribute['optional_attributes'] = '';
                    $profile->setCatDependAttribute(json_encode($trademetAttribute));
                }
            }

            // save required and optional attribute
            $reqAttribute1 = [];
            $optAttribute1 = [];
            if (!empty($profileData['required_attributes'])) {
                $temAttribute1 = $this->unique_multidim_array($profileData['required_attributes'], 'onbuy_attribute_name');
                $temp3 = $temp4 = [];
                foreach ($temAttribute1 as $item) {
                    if ($item['required']) {
                        $temp3['onbuy_attribute_name'] = $item['onbuy_attribute_name'];
                        $temp3['onbuy_attribute_type'] = $item['onbuy_attribute_type'];
                        $temp3['magento_attribute_code'] = $item['magento_attribute_code'];

                        if (isset($item['default'])) {
                            $temp3['default'] = $item['default'];
                        }
                        $temp3['required'] = $item['required'];
                        $reqAttribute1[] = $temp3;
                    } else {
                        $temp4['onbuy_attribute_name'] = $item['onbuy_attribute_name'];
                        $temp4['onbuy_attribute_type'] = $item['onbuy_attribute_type'];
                        $temp4['magento_attribute_code'] = $item['magento_attribute_code'];
                        if (isset($item['default'])) {
                            $temp4['default'] = $item['default'];
                        }
                        $temp4['required'] = 0;
                        $optAttribute1[] = $temp4;
                    }
                } 
                $trademeReqOptAttribute['required_attributes'] = $reqAttribute1;
                $trademeReqOptAttribute['optional_attributes'] = $optAttribute1;

                $profile->setOptReqAttribute(json_encode($trademeReqOptAttribute));
            } else {
                $profile->setOptReqAttribute('');
            }

            // save category features
            $profile->setAccountId($accountId);
            //save profile
            $profile->save($profile);
//            print_r($profile->getCatDependAttribute());die;
            $profile->updateProducts($profileProducts, $profile->getId(), $accountId);

            if ($redirectBack && $redirectBack == 'edit') {
                $this->messageManager->addSuccessMessage(__('
		   		You Saved The Onbuy Profile And Its Products.
		   			'));
                $this->_redirect('*/*/edit', array(
                    'pcode' => $pcode,
                ));
            }  else {
                $this->messageManager->addSuccessMessage(__('
		   		You Saved The Onbuy Profile And Its Products.
		   		'));
                $this->_redirect('*/*/index');
            }
        } catch (\Exception $e) {
            $this->logger->addError($e->getMessage(), ['path' => __METHOD__]);
            $this->_objectManager->create('Ced\Onbuy\Helper\Logger')->addError('In Save Profile: ' . $e->getMessage(), ['path' => __METHOD__]);
            $this->messageManager->addErrorMessage(__('
		   		Unable to Save Profile Please Try Again.
		   			' . $e->getMessage()));
            $this->_redirect('*/*/new');
        }

        return;
    }

    /**
     * @param $array
     * @param $key
     * @return array
     */
    function unique_multidim_array($array, $key)
    {
        $temp_array = array();
        $i = 0;
        $key_array = array();

        foreach ($array as $val) {
            if ($val['delete'] == 1)
                continue;

            if (!in_array($val[$key], $key_array)) {
                $key_array[$i] = $val[$key];
                $temp_array[$i] = $val;
            }
            $i++;
        }
        // print_r(json_encode($temp_array));
        // die(__FILE__);
        return $temp_array;
    }
}