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
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\JsonFactory;


/**
 * Class Edit
 * @package Ced\Onbuy\Controller\Adminhtml\Profile
 */
class GetCategory extends \Magento\Backend\App\Action
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

    )
    {
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
        $searchText = $this->getRequest()->getParam('searchText');
        $catId = $this->getRequest()->getParam('feature');
        $feature = $this->getRequest()->getParam('feature');
        if ($this->_coreRegistry->registry('onbuy_account')){

            $account = $this->_coreRegistry->registry('onbuy_account');
            $accountId =  $account->getId();
        }
        if (!$accountId){
            $accountId = $this->_session->getAccountId();
        }
        $this->multiAccountHelper->getAccountRegistry($accountId);
        $this->dataHelper->updateAccountVariable($accountId);
        if ($searchText)
            $response = $this->dataHelper->getRequest("categories?site_id=2000&limit=500&offset=0&filter[search]=$searchText&filter[can_list_in]=1", true);
        elseif ($feature)
            $response = $this->dataHelper->getRequest("categories/$feature/features?site_id=2000&limit=20&offset=0", true);
            else{
                $response = $this->dataHelper->getRequest("conditions?site_id=2000", true);
                $response = json_decode($response, true);
               
        $container = array();
        $container[] = ['value' => '', 'label' => 'Please Select Category'];
        if($response){
            foreach ($response as $category=>$value) {
                foreach($value as $val){
                    $container[] = ['value' => $val['code'],
                            'label' => $val['name']];
                 
                }
                      
                }}
    return $container;
            }

        $response = json_decode($response, true);
        $container = array();
        $container[] = ['value' => '', 'label' => 'Please Select Category'];

        if ($response) {

        foreach ($response as $category) {
            foreach ($category as $cat) {

                if (isset($cat['category_id'])) {
                    $container[] = ['value' => $cat['category_id'],
                        'label' => isset($cat['category_tree']) ? $cat['name'] . ' (' . $cat['category_tree'] . ')' : $cat['name']];
                } elseif (isset($cat['feature_id'])) {
                    if ($cat['required']) {
                        $container[] = ['value' => $cat['name'],
                            'label' => isset($cat['options']) ? $cat['options'] : ''];
                    }
                }
            }

        }
    }

        return $this->jsonFactory->create()
            ->setData([
                'category' => $container
            ]);
        return $this->getResponse()->setBody(json_encode($container));
    }
}