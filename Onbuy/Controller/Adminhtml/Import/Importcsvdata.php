<?php
namespace Ced\Onbuy\Controller\Adminhtml\Import;

class Importcsvdata extends \Magento\Framework\App\Action\Action
{
    
    protected $resultPageFactory;
    protected $jsonHelper;
    protected $_fileUploaderFactory;
    protected $csvProcessor;
    public $ProfileproductsFactory;

public function __construct(
        \Magento\Framework\File\Csv $csvProcessor,
        \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory,
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
         \Ced\Onbuy\Model\ProfileproductsFactory $ProfileproductsFactory
    ) {
        $this->csvProcessor = $csvProcessor;
        $this->_fileUploaderFactory = $fileUploaderFactory;
        $this->resultPageFactory = $resultPageFactory;
        $this->jsonHelper = $jsonHelper;
        $this->ProfileproductsFactory = $ProfileproductsFactory;
        parent::__construct($context);
    }
    public function execute()
        {

           $postdata = $this->getRequest()->getPostValue();

           $profilecode = $postdata['profilecode'];
           $account_id = $postdata['account_id'];

            try{ 
                 if (isset($_POST['submit'])) 
                 {  
                    $msg='';
                    if(($_FILES['file_upload']['type']=='text/csv') && ($_FILES['file_upload']['size'] <= 2000))
                     {
                     $handle = fopen($_FILES['file_upload']['tmp_name'], "r");
                     $headers = fgetcsv($handle, 0, ",");
            


            $this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();   

            $profileId = $this->_objectManager->create('Ced\Onbuy\Model\Profile')->getCollection()->addFieldToFilter('profile_code', $profilecode)->getColumnValues('id');


            $prods = $this->_objectManager->create('Ced\Onbuy\Model\ResourceModel\Profileproducts\Collection')->addFieldToFilter('account_id',$account_id)
               ->addFieldToFilter('profile_id',$profileId[0])->addFieldToSelect('product_id')->getData();

            $product = $this->ProfileproductsFactory->create();
               
                     while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) 
                     {  
                          
                              $data_product[] = $data;
                     }
                     //print_r($data_product); die();
                     foreach($data_product as $data){
                              //print_r($data); 


                        $productin  = $this->_objectManager->get('Magento\Catalog\Model\Product')->getCollection()->addAttributeToFilter('entity_id', ['in' => $data[0]])->getColumnValues('entity_id');

                        //print_r($data);                   
                         if (!empty($productin)) { 
                            
                            $pdata = $this->_objectManager->create('Ced\Onbuy\Model\ResourceModel\Profileproducts\Collection')->addFieldToFilter('product_id',[ 'eq' => $data[0]])->getData();
                   

                                //print_r($pdata[0]);    //die();                   
                           
                                if(empty($pdata))
                                 {         
                                        $myData = [ 'product_id' => $data[0],
                                                    'account_id' => $data[9],
                                                    'profile_id' => $data[8],
                                                  ];
                                        //echo "<pre>"; print_r($myData);
                                        $product->addData($myData);
                                        $product->save();  
                                        $product->unsetData();
                                        
 
                                $this->messageManager->addSuccessMessage(__('Product Assigned.'));
                                } else {
                                  //echo "<pre>"; print_r($productids['product_id']); 
                                  $this->messageManager->addErrorMessage(__('Product already in profile and product id.'.$data[0]));
                                }
                          
                     } else {
                                $msg = array(''=>'');
                                $this->messageManager->addErrorMessage(__('Product not in Magento.'));
                           }       
                           
                        }


                    $this->_redirect('onbuy/profile/edit/pcode/'.$profilecode.'/account_id/'.$account_id);

                   } else {
                        $this->messageManager->addErrorMessage(__('Select CSV File Only and Size 2M.'.$msg));
                        $this->_redirect('onbuy/profile/edit/pcode/'.$profilecode.'/account_id/'.$account_id);
                   } }
            } catch (\Exception $e) {
                print_r($e->getMessage());
            }


        }
 }

    