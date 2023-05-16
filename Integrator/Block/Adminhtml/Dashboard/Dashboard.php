<?php

namespace Ced\Integrator\Block\Adminhtml\Dashboard;

use Magento\Framework\View\Element\Template;

class Dashboard extends Template
{

    protected $_template = 'dashboard/subDashboard.phtml';

    public function __construct(
        \Magento\Framework\Pricing\PriceCurrencyInterface $currency,
        \Magento\Backend\Block\Template\Context $context,
        \Ced\Integrator\Helper\ProductData $productdata,
        \Ced\Integrator\Helper\OrderData $orderdata,
        array $data = []
    ) {
        $this->orderdata = $orderdata;
        $this->productdata = $productdata;
        $this->_currency = $currency;
        parent::__construct($context, $data);
    }

    protected function _prepareLayout()
    {
        
    
        /**Block To Fetch Monthly,Yearly,Daily Basis Data */
        $this->addChild('data', \Ced\Integrator\Block\Adminhtml\Dashboard\FetchData::class);

        /**Block To Show Return Order Grid */
        $this->addChild('return', \Ced\Integrator\Block\Adminhtml\Dashboard\Order\ReturnOrder::class);

        /**Block To Show Most Sold Product Grid*/
        $this->addChild('mostSold', \Ced\Integrator\Block\Adminhtml\Dashboard\Order\MonthlySale::class);

        /**Block To Show Recent Shipped Orders Grid */
        $this->addChild('lastOrders', \Ced\Integrator\Block\Adminhtml\Dashboard\Order\Order::class);

        /**Block To Show Pending Orders Grid */
        $this->addChild('pendingOrder', \Ced\Integrator\Block\Adminhtml\Dashboard\Order\PendingOrder::class);
    }

    /** Function To Return Data
     *  of All Product Status
     *  For PieChart
     * */
    public function productstatus()
    {
        $products = $this->productdata->getproductdata();
        return $products;
    }

    /**Function
     * To Get LifeTime
     * Sale
     * */
    public function lifeTimeSale()
    {
        $successProduct = $this->orderdata->completedOrderDetails();
        $total = 0;
        foreach ($successProduct as $value) {
            $total = $total + $value['base_grand_total'];
        }

        return $total;
    }

    /**Function
     * To Get Average
     * Sale
     * */
    public function averageSale()
    {
        $successProduct = $this->orderdata->completedOrderDetails();

        $total = 0;
        foreach ($successProduct as $value) {
            $total = $total + $value['base_grand_total'];
        }
        if (count($successProduct) != 0) {
            return $total / count($successProduct);
        } else {
            return $total;
        }
    }

    /**Function
     * To Get Total
     * Quantity Shipped
     * */
    public function totalQuantity()
    {
        $successProduct = $this->orderdata->completedOrderDetails();

        $total = 0;
        foreach ($successProduct as $value) {
            $total = $total + $value['total_qty_ordered'];
        }

        return $total;
    }

    /**Function
     * To Get Current
     * Currency Code
     * Symbol
     * */
    public function getCurrentCurrencySymbol()
    {
        return $this->_currency->getCurrencySymbol('default');
    }
}
