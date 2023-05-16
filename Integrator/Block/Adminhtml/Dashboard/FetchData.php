<?php

namespace Ced\Integrator\Block\Adminhtml\Dashboard;

use Magento\Framework\View\Element\Template;

class FetchData extends Template
{
    public function __construct(
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Ced\Integrator\Block\Adminhtml\Dashboard\OrderData $order,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Backend\Block\Template\Context $context,
        \Ced\Integrator\Model\Path $path,
        array $data = []
    ) {
      
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->objectManager = $objectManager;
        $this->order = $order;
        $this->path = $path;
        parent::__construct($context, $data);
    }

    /**Function
     * Used To Get
     * The Selected Module
     * Order Id Collection */
    public function getSelectedModuleOrderData()
    {
       
        $collection = [];
        /**Getting Selected Module File Path and Name */
        $path = $this->path->getCollectionFile();
        $orderDetails = $this->objectManager->get($path);

        $orderIdCollection = $orderDetails->create();
        $moduleName = $this->path->moduleName();

        /**Condition When Ebay And Amazon is Selected  */
        if ($moduleName == 'EbayMultiAccount' || $moduleName == 'Amazon') {
            $accountId = $this->path->accountId();
            $orderIdCollection->AddFieldToSelect('*')->AddFieldToFilter('account_id', $accountId);
        }

        /**Loop To Get The Collection Of All The Order Id Of The Selected Extension */
        foreach ($orderIdCollection as $value) {
            if ($value['magento_order_id'] != 'N/A' || $value['magento_order_id'] != "") {
                if ($moduleName == 'Amazon') {

                    $collection[] = $value['magento_increment_id'];
                } else {
                    $collection[] = $value['magento_order_id'];
                }
            }
        }

        return $collection;
    }

    /**Function
     * Used To Trim The
     * value To get The Dates
     * */
    public function prepareDailyData($data)
    {
        foreach ($data as $values) {
            $getDate = substr($values['updated_at'], 0, strrpos($values['updated_at'], ' '));
            $staticsArray[$getDate] = $values['this_is_total'];
        }
        if (isset($staticsArray)) {
            return $staticsArray;
        }
    }

    /**Function
     * Used To Trim The
     * value To get The Months
     * */
    public function prepareMonthlyData($data)
    {
        foreach ($data as $values) {
            $getDate = substr($values['updated_at'], 0, strrpos($values['updated_at'], ' '));
            $getDate = substr_replace($getDate, "", -3);
            $staticsArray[$getDate] = $values['this_is_total'];
        }
        if (isset($staticsArray)) {
            return $staticsArray;
        }
    }

    public function prepareCurrentMonthlyData()
    {
        $endDate = date("Y-m-d");
        $endDates = date('Y-m-d', strtotime('+1 day', strtotime($endDate)));
        $startDate = date('Y-m-01');
        for ($currentDate = strtotime($startDate); $currentDate <= strtotime($endDates); $currentDate += (86400)) {
            $Store = date('Y-m-d', $currentDate);
            $datecollection[] = $Store;
        }

        $orderIdCollection = $this->getSelectedModuleOrderData();
        $salesCollection = $this->orderCollectionFactory->create()->AddAttributeToSelect('*')
            ->addAttributetoFilter('increment_id', ['in' => $orderIdCollection])
            ->addAttributetoFilter('status', 'complete')
            ->addAttributetoFilter('updated_at', ['from' => $startDate, 'to' => $endDates])
            ->addExpressionFieldToSelect('this_is_total', 'SUM({{base_grand_total}})', 'base_grand_total');
        $salesCollection->getSelect()->group('DATE_FORMAT(updated_at, "%d%m%y")');

        $salesdata = $this->prepareDailyData($salesCollection);

        $returnCollection = $this->orderCollectionFactory->create()->AddAttributeToSelect('*')
            ->addAttributetoFilter('increment_id', ['in' => $orderIdCollection])
            ->addAttributetoFilter('status', 'closed')
            ->addAttributetoFilter('updated_at', ['from' => $startDate, 'to' => $endDates])
            ->addExpressionFieldToSelect('this_is_total', 'SUM({{base_grand_total}})', 'base_grand_total');
        $returnCollection->getSelect()->group('DATE_FORMAT(updated_at, "%d%m%y")');

        $returndata = $this->prepareDailyData($returnCollection);

        $averageCollection = $this->orderCollectionFactory->create()->AddAttributeToSelect('*')
            ->addAttributetoFilter('increment_id', ['in' => $orderIdCollection])
            ->addAttributetoFilter('status', 'complete')
            ->addAttributetoFilter('updated_at', ['from' => $startDate, 'to' => $endDates])
            ->addExpressionFieldToSelect('this_is_total', 'AVG({{base_grand_total}})', 'base_grand_total');
        $averageCollection->getSelect()->group('DATE_FORMAT(updated_at, "%d%m%y")');

        $averagedata = $this->order->prepareDailyData($averageCollection);
        $preparedData=$this->order->dateDataTrim($datecollection, $salesdata, $returndata, $averagedata);

        return [
            "sales" => $preparedData['salesData'],
            "return" => $preparedData['returnData'],
            "average"=>$preparedData['averageData'],
            "date" => $datecollection
        ];
    }

    public function prepare12MonthlyData()
    {

        $time2  = strtotime(date("Y-m-d"));
        $time1  = strtotime('-12 month', $time2);
        $my     = date('mY', $time2);
        $months[date('Y-m-d', $time1)] = date('F Y', $time1);

        while ($time1 < $time2) {
            $time1 = strtotime(date('Y-m-d', $time1) . ' +1 month');
            if (date('mY', $time1) != $my && ($time1 < $time2)) {
                $months[date('Y-m-d', $time1)] = date('F Y', $time1);
            }
        }

        $months[date('Y-m-d', $time2)] = date('F Y', $time2);

        $endDate = date("Y-m-d");
        $startDate = date('Y-m-d', strtotime('-12 month', strtotime($endDate)));

        $orderIdCollection = $this->getSelectedModuleOrderData();
        $salesCollection = $this->orderCollectionFactory->create()->AddAttributeToSelect('*')
            ->addAttributetoFilter('increment_id', ['in' => $orderIdCollection])
            ->addAttributetoFilter('status', 'complete')
            ->addAttributetoFilter('updated_at', ['from' => $startDate, 'to' => $endDate])
            ->addExpressionFieldToSelect('this_is_total', 'SUM({{base_grand_total}})', 'base_grand_total');
        $salesCollection->getSelect()->group('DATE_FORMAT(updated_at, "%m%y")');

        $salesdata = $this->prepareMonthlyData($salesCollection);

        $returnCollection = $this->orderCollectionFactory->create()->AddAttributeToSelect('*')
            ->addAttributetoFilter('increment_id', ['in' => $orderIdCollection])
            ->addAttributetoFilter('status', 'closed')
            ->addAttributetoFilter('updated_at', ['from' => $startDate, 'to' => $endDate])
            ->addExpressionFieldToSelect('this_is_total', 'SUM({{base_grand_total}})', 'base_grand_total');
        $returnCollection->getSelect()->group('DATE_FORMAT(updated_at, "%m%y")');

        $returndata = $this->prepareMonthlyData($returnCollection);

        $averageCollection = $this->orderCollectionFactory->create()->AddAttributeToSelect('*')
            ->addAttributetoFilter('increment_id', ['in' => $orderIdCollection])
            ->addAttributetoFilter('status', 'complete')
            ->addAttributetoFilter('updated_at', ['from' => $startDate, 'to' => $endDate])
            ->addExpressionFieldToSelect('this_is_total', 'AVG({{base_grand_total}})', 'base_grand_total');
        $averageCollection->getSelect()->group('DATE_FORMAT(updated_at, "%m%y")');

        $averagedata = $this->order->prepareMonthlyData($averageCollection);

        $datecollection = $months;
        $preparedData=$this->order->monthDataTrim($datecollection, $salesdata, $returndata, $averagedata);

        return [
            "sales" => $preparedData['salesData'],
            "return" => $preparedData['returnData'],
            "average"=>$preparedData['averageData'],
            "date" => $datecollection
        ];   
    }

    public function prepare24MonthlyData()
    {

        $time2  = strtotime(date("Y-m-d"));
        $time1  = strtotime('-24 month', $time2);
        $my     = date('mY', $time2);
        $months[date('Y-m-d', $time1)] = date('F Y', $time1);

        while ($time1 < $time2) {
            $time1 = strtotime(date('Y-m-d', $time1) . ' +1 month');
            if (date('mY', $time1) != $my && ($time1 < $time2)) {
                $months[date('Y-m-d', $time1)] = date('F Y', $time1);
            }
        }

        $months[date('Y-m-d', $time2)] = date('F Y', $time2);

        $endDate = date("Y-m-d");
        $startDate = date('Y-m-d', strtotime('-24 month', strtotime($endDate)));

        $orderIdCollection = $this->getSelectedModuleOrderData();

        $averageCollection = $this->orderCollectionFactory->create()->AddAttributeToSelect('*')
        ->addAttributetoFilter('increment_id', ['in' => $orderIdCollection])
        ->addAttributetoFilter('status', 'complete')
        ->addAttributetoFilter('updated_at', ['from' => $startDate, 'to' => $endDate])
        ->addExpressionFieldToSelect('this_is_total', 'AVG({{base_grand_total}})', 'base_grand_total');
    $averageCollection->getSelect()->group('DATE_FORMAT(updated_at, "%m%y")');

    $averagedata = $this->order->prepareMonthlyData($averageCollection);
        $returnCollection = $this->orderCollectionFactory->create()->AddAttributeToSelect('*')
            ->addAttributetoFilter('increment_id', ['in' => $orderIdCollection])
            ->addAttributetoFilter('status', 'closed')
            ->addAttributetoFilter('updated_at', ['from' => $startDate, 'to' => $endDate])
            ->addExpressionFieldToSelect('this_is_total', 'SUM({{base_grand_total}})', 'base_grand_total');
        $returnCollection->getSelect()->group('DATE_FORMAT(updated_at, "%m%y")');

        $returndata = $this->prepareMonthlyData($returnCollection);

        $salesCollection = $this->orderCollectionFactory->create()->AddAttributeToSelect('*')
        ->addAttributetoFilter('increment_id', ['in' => $orderIdCollection])
        ->addAttributetoFilter('status', 'complete')
        ->addAttributetoFilter('updated_at', ['from' => $startDate, 'to' => $endDate])
        ->addExpressionFieldToSelect('this_is_total', 'SUM({{base_grand_total}})', 'base_grand_total');
    $salesCollection->getSelect()->group('DATE_FORMAT(updated_at, "%m%y")');

    $salesdata = $this->prepareMonthlyData($salesCollection);

        $datecollection = $months;
        $preparedData=$this->order->monthDataTrim($datecollection, $salesdata, $returndata, $averagedata);

        return [
            "sales" => $preparedData['salesData'],
            "return" => $preparedData['returnData'],
            "average"=>$preparedData['averageData'],
            "date" => $datecollection
        ];
    }

    public function preparelast7days()
    {
        $endDate = date("Y-m-d");
        $startDate = date('Y-m-d', strtotime('-6 day', strtotime($endDate)));
        for ($currentDate = strtotime($startDate); $currentDate <= strtotime($endDate); $currentDate += (86400)) {
            $Store = date('Y-m-d', $currentDate);
            $datecollection[] = $Store;
        }

        $orderIdCollection = $this->getSelectedModuleOrderData();
        $salesCollection = $this->orderCollectionFactory->create()->AddAttributeToSelect('*')
            ->addAttributetoFilter('increment_id', ['in' => $orderIdCollection])
            ->addAttributetoFilter('status', 'complete')
            ->addAttributetoFilter('updated_at', ['from' => $startDate, 'to' => $endDate])
            ->addExpressionFieldToSelect('this_is_total', 'SUM({{base_grand_total}})', 'base_grand_total');
        $salesCollection->getSelect()->group('DATE_FORMAT(updated_at, "%d%m%y")');

        $salesdata = $this->prepareDailyData($salesCollection);

        $returnCollection = $this->orderCollectionFactory->create()->AddAttributeToSelect('*')
            ->addAttributetoFilter('increment_id', ['in' => $orderIdCollection])
            ->addAttributetoFilter('status', 'closed')
            ->addAttributetoFilter('updated_at', ['from' => $startDate, 'to' => $endDate])
            ->addExpressionFieldToSelect('this_is_total', 'SUM({{base_grand_total}})', 'base_grand_total');
        $returnCollection->getSelect()->group('DATE_FORMAT(updated_at, "%d%m%y")');

        $returndata = $this->prepareDailyData($returnCollection);

        $averageCollection = $this->orderCollectionFactory->create()->AddAttributeToSelect('*')
            ->addAttributetoFilter('increment_id', ['in' => $orderIdCollection])
            ->addAttributetoFilter('status', 'complete')
            ->addAttributetoFilter('updated_at', ['from' => $startDate, 'to' => $endDate])
            ->addExpressionFieldToSelect('this_is_total', 'AVG({{base_grand_total}})', 'base_grand_total');
        $averageCollection->getSelect()->group('DATE_FORMAT(updated_at, "%d%m%y")');

        $averagedata =  $this->order->prepareDailyData($averageCollection);

        $preparedData=$this->order->dateDataTrim($datecollection, $salesdata, $returndata, $averagedata);

        return [
            "sales" => $preparedData['salesData'],
            "return" => $preparedData['returnData'],
            "average"=>$preparedData['averageData'],
            "date" => $datecollection
        ];
    }
}
