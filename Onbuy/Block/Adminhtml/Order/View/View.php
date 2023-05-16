<?php
namespace Ced\Onbuy\Block\Adminhtml\Order\View;
class View extends \Magento\Backend\Block\Template
{
    protected $orderRepository;
    protected $_ordersFactory;
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Ced\Onbuy\Model\OrdersFactory $ordersFactory,
        array $data = []
    ){
        parent::__construct($context, $data);
        $this->orderRepository = $orderRepository;
        $this->_ordersFactory=$ordersFactory;
    }
    public function myFunction()
    {
        $id = $this->getRequest()->getParam('order_id');
        
        $order = $this->orderRepository->get($id);
        $orderIncrementId = $order->getIncrementId();
        try
        {

            $orders=$this->_ordersFactory->create()->getCollection()->addFieldToFilter('magento_increment_id',$orderIncrementId);
            
            if (!empty($orders->getData())) {
            $onbuyorderdata=$orders->getData();
            $converdata = json_decode($onbuyorderdata[0]['order_data'],true);
            $onbuy_order_id= $converdata['order_id'];
            $paypal_capture_id= $converdata['paypal_capture_id'];
            
                $arr=array
                    ('order'=>$onbuy_order_id,
                    'paypal'=>$paypal_capture_id
            );

                return $arr;
        }
        } catch (\Exception $e){
            return;
        }
       
         
    }
}