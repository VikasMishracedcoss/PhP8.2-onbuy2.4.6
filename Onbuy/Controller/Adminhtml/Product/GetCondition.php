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

namespace Ced\Onbuy\Controller\Adminhtml\Product;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\JsonFactory;


/**
 * Class Edit
 * @package Ced\Onbuy\Controller\Adminhtml\Profile
 */
class GetCondition extends \Magento\Backend\App\Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;
    /**
     * @var
     */
    protected $_entityTypeId;

    const ADMIN_RESOURCE = 'Ced_Onbuy::Onbuy';
    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var \Ced\Onbuy\Helper\MultiAccount
     */
    protected $multiAccountHelper;

    /**
     * @var \Ced\Onbuy\Helper\Data
     */
    protected $dataHelper;

    protected $jsonFactory;
    
    /**
     * Edit constructor.
     * @param Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Registry $coreRegistry,
        PageFactory $resultPageFactory,
        \Ced\Onbuy\Helper\MultiAccount $multiAccountHelper,
        \Ced\Onbuy\Helper\Data $helper,
        JsonFactory $jsonFactory

    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->multiAccountHelper = $multiAccountHelper;
        $this->dataHelper = $helper;
        $this->_coreRegistry = $coreRegistry;
        $this->jsonFactory = $jsonFactory;
    }

    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {   
        $response = '';
        $accountId = '';
        if ($this->_coreRegistry->registry('onbuy_account')) {

            $account = $this->_coreRegistry->registry('onbuy_account');
            $accountId = $account->getId();
        }
        if (!$accountId) {
            $accountId = $this->_session->getAccountId();
        }
        if(isset($accountId)){
            $this->multiAccountHelper->getAccountRegistry($accountId);
            $this->dataHelper->updateAccountVariable($accountId);

            $response = $this->dataHelper->getRequest("conditions?site_id=2000", true);
              
           
            $response = '{"results":[{"condition_id":1,"name":"New","code":"new"},{"condition_id":4,"name":"Used (Excellent) (Used)","code":"excellent"},{"condition_id":5,"name":"Used (Very Good) (Used)","code":"verygood"},{"condition_id":6,"name":"Used (Good) (Used)","code":"good"},{"condition_id":7,"name":"Used (Average) (Used)","code":"average"},{"condition_id":8,"name":"Used (Below Average) (Used)","code":"belowaverage"},{"condition_id":9,"name":"Refurbished (Diamond) (Refurbished)","code":"diamond"},{"condition_id":10,"name":"Refurbished (Platinum) (Refurbished)","code":"platinum"},{"condition_id":11,"name":"Refurbished (Gold) (Refurbished)","code":"gold"},{"condition_id":12,"name":"Refurbished (Silver) (Refurbished)","code":"silver"},{"condition_id":13,"name":"Refurbished (Bronze) (Refurbished)","code":"bronze"},{"condition_id":14,"name":"Refurbished (Refurbished)","code":"refurbished-ungraded"}],"metadata":{"limit":20,"offset":0,"total_rows":12}}';  
            $response = json_decode($response, true); //die(__DIR__);
              
        
            $container = array();
            $container[] = ['value' => '', 'label' => 'Please Select Condition'];
            if (isset($response['results'][0])) {
                foreach ($response as $category => $value) {
                    foreach ($value as $val) {
                    $container[] = ['value' => isset($val['code']) ? $val['code'] : '',
                            'label' => isset($val['name']) ? $val['name'] : ''];


                }
                }
            }
        }else{
            $container[] = ['value' => '', 'label' => 'Account not found'];;
        }
        return $container; 

    }
}

