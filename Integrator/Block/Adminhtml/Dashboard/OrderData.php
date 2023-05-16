<?php

namespace Ced\Integrator\Block\Adminhtml\Dashboard;

use Magento\Framework\View\Element\Template;

class OrderData extends Template
{

    protected $_template = 'dashboard/mainDashboard.phtml';
    public function __construct(
        \Magento\Sales\Model\ResourceModel\Order\Grid\CollectionFactory $orderCollectionFactory,
        \Magento\Backend\Block\Template\Context $context,
        \Ced\Integrator\Model\Path $path,
        \Ced\Integrator\Block\Adminhtml\Dashboard\ModuleList $list,
        array $data = []
    ) {
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->path = $path;
        $this->list = $list;
        parent::__construct($context, $data);
    }

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

    public function preparemonthlyData($data)
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

    public function dateDataTrim($datecollection, $staticsArray, $staticsArrays, $staticArrayss)
    {
        foreach ($datecollection as $value) {
            if (isset($staticsArrays[$value])) {
                $salesData[] = round($staticsArrays[$value]);
            } else {
                $salesData[] = 0;
            }

            if (isset($staticsArray[$value])) {
                $returnData[] = round($staticsArray[$value]);
            } else {
                $returnData[] = 0;
            }

            if (isset($staticArrayss[$value])) {
                $averageData[] = round($staticArrayss[$value]);
            } else {
                $averageData[] = 0;
            }
        }
        return [
            'salesData'=>$salesData,
            'returnData'=>$returnData,
            'averageData'=>$averageData
        ];
    }

    public function monthDataTrim($datecollection, $staticArray, $staticArrays, $staticArrayss)
    {
        foreach ($datecollection as $value => $key) {
            $value = substr_replace($value, "", -3);
            if (isset($staticArrays[$value])) {
                $salesData[] = round($staticArrays[$value]);
            } else {
                $salesData[] = 0;
            }

            if (isset($staticArray[$value])) {
                $returnData[] = round($staticArray[$value]);
            } else {
                $returnData[] = 0;
            }
            if (isset($staticArrayss[$value])) {
                $averageData[] = round($staticArrayss[$value]);
            } else {
                $averageData[] = 0;
            }
        }
        return [
            'salesData'=>$salesData,
            'returnData'=>$returnData,
            'averageData'=>$averageData
        ];
    }

    public function blocksData()
    {
        $paymentArray = $this->getPaymentMethods();
        $returnCollection = $this->orderCollectionFactory->create()->AddFieldToSelect('*')
            ->addFieldToFilter('payment_method', ['in' => $paymentArray])
            ->addFieldToFilter('status', 'closed')
            ->addExpressionFieldToSelect('this_is_total', 'SUM({{base_grand_total}})', 'base_grand_total');

        $salesCollection = $this->orderCollectionFactory->create()->AddFieldToSelect('*')
            ->addFieldToFilter('payment_method', ['in' => $paymentArray])
            ->addFieldToFilter('status', 'complete')
            ->addExpressionFieldToSelect('this_is_total', 'SUM({{base_grand_total}})', 'base_grand_total');

        $totalquantity = $this->orderCollectionFactory->create()->AddFieldToSelect('*')
            ->addFieldToFilter('payment_method', ['in' => $paymentArray])
            ->addFieldToFilter('status', 'complete');

        $quantitydata = $totalquantity->getdata();
        $quantityRecord = count($quantitydata);

        $salesdata = $salesCollection->getdata();
        $salesRecord = $salesdata[0]['this_is_total'];

        $returndata = $returnCollection->getdata();

        $returnRecord = $returndata[0]['this_is_total'];
        $salesRecord=empty($salesRecord)?0:$salesRecord;
        $returnRecord=empty($returnRecord)?0:$returnRecord;

        return [
            "sales" => $salesRecord,
            "return" => $returnRecord,
            "quantity" => $quantityRecord
        ];
    }

    public function getPaymentMethods()
    {
        $list = $this->list->modulesList();
        $paymentArray = [];
        foreach ($list as $module) {

            $fileCollection = $this->path->dashboardFilePath($module);
            $path = $fileCollection['Payment'];
            $paymentName = ($path::METHOD_CODE);
            array_push($paymentArray, $paymentName);
        }
        return $paymentArray;
    }

    public function last7days()
    {
        $paymentArray = $this->getPaymentMethods();
        $endDate = date("Y-m-d");
        $startDate = date('Y-m-d', strtotime('-6 day', strtotime($endDate)));
        for ($currentDate = strtotime($startDate); $currentDate <= strtotime($endDate); $currentDate += (86400)) {
            $Store = date('Y-m-d', $currentDate);
            $datecollection[] = $Store;
        }

        $returnCollection = $this->orderCollectionFactory->create()->AddFieldToSelect('*')
            ->addFieldToFilter('payment_method', ['in' => $paymentArray])
            ->addFieldToFilter('status', 'closed')
            ->addFieldToFilter('updated_at', ['from' => $startDate, 'to' => $endDate])
            ->addExpressionFieldToSelect('this_is_total', 'SUM({{base_grand_total}})', 'base_grand_total');

        $returnCollection->getSelect()->group('DATE_FORMAT(updated_at, "%d%m%y")');
        $staticsArray = $this->prepareDailyData($returnCollection);

        $salesCollection = $this->orderCollectionFactory->create()->AddFieldToSelect('*')
            ->addFieldToFilter('payment_method', ['in' => $paymentArray])
            ->addFieldToFilter('status', 'complete')
            ->addFieldToFilter('updated_at', ['from' => $startDate, 'to' => $endDate])
            ->addExpressionFieldToSelect('this_is_total', 'SUM({{base_grand_total}})', 'base_grand_total');
        $salesCollection->getSelect()->group('DATE_FORMAT(updated_at, "%d%m%y")');

        $staticsArrays = $this->prepareDailyData($salesCollection);

        $preparedData=$this->dateDataTrim($datecollection, $staticsArray, $staticsArrays, []);

        return [
            "sales" => $preparedData['salesData'],
            "return" => $preparedData['returnData'],
            "date" => $datecollection
        ];
    }

    public function last24months()
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

        $paymentArray = $this->getPaymentMethods();
        $endDate = date("Y-m-d");
        $startDate = date('Y-m-d', strtotime('-24 month', strtotime($endDate)));

        $returnCollection = $this->orderCollectionFactory->create()->AddFieldToSelect('*')
            ->addFieldToFilter('payment_method', ['in' => $paymentArray])
            ->addFieldToFilter('status', 'closed')
            ->addFieldToFilter('updated_at', ['from' => $startDate, 'to' => $endDate])
            ->addExpressionFieldToSelect('this_is_total', 'SUM({{base_grand_total}})', 'base_grand_total');

        $returnCollection->getSelect()->group('DATE_FORMAT(updated_at, "%m%y")');

        $staticsArray = $this->preparemonthlyData($returnCollection);
        $salesCollection = $this->orderCollectionFactory->create()->AddFieldToSelect('*')
            ->addFieldToFilter('payment_method', ['in' => $paymentArray])
            ->addFieldToFilter('status', 'complete')
            ->addFieldToFilter('updated_at', ['from' => $startDate, 'to' => $endDate])
            ->addExpressionFieldToSelect('this_is_total', 'SUM({{base_grand_total}})', 'base_grand_total');
        $salesCollection->getSelect()->group('DATE_FORMAT(updated_at, "%m%y")');

        $staticsArrays = $this->preparemonthlyData($salesCollection);
        $datecollection = $months;

        $preparedData=$this->monthDataTrim($datecollection, $staticsArray, $staticsArrays, []);

        return [
            "sales" => $preparedData['salesData'],
            "return" => $preparedData['returnData'],
            "date" => $datecollection
        ];
    }

    public function last12months()
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

        $paymentArray = $this->getPaymentMethods();
        $endDate = date("Y-m-d");
        $startDate = date('Y-m-d', strtotime('-12 month', strtotime($endDate)));


        $salesCollection = $this->orderCollectionFactory->create()->AddFieldToSelect('*')
            ->addFieldToFilter('payment_method', ['in' => $paymentArray])
            ->addFieldToFilter('status', 'complete')
            ->addFieldToFilter('updated_at', ['from' => $startDate, 'to' => $endDate])
            ->addExpressionFieldToSelect('this_is_total', 'SUM({{base_grand_total}})', 'base_grand_total');
        $salesCollection->getSelect()->group('DATE_FORMAT(updated_at, "%m%y")');

        $staticsArrays = $this->preparemonthlyData($salesCollection);
        
        $returnCollection = $this->orderCollectionFactory->create()->AddFieldToSelect('*')
        ->addFieldToFilter('payment_method', ['in' => $paymentArray])
        ->addFieldToFilter('status', 'closed')
        ->addFieldToFilter('updated_at', ['from' => $startDate, 'to' => $endDate])
        ->addExpressionFieldToSelect('this_is_total', 'SUM({{base_grand_total}})', 'base_grand_total');

    $returnCollection->getSelect()->group('DATE_FORMAT(updated_at, "%m%y")');

    $staticsArray = $this->preparemonthlyData($returnCollection);
        $datecollection = $months;
        $preparedData=$this->monthDataTrim($datecollection, $staticsArray, $staticsArrays, []);

        return [
            "sales" => $preparedData['salesData'],
            "return" => $preparedData['returnData'],
            "date" => $datecollection
        ];
    }

    public function currentMonth()
    {
        $paymentArray = $this->getPaymentMethods();
        $endDate = date("Y-m-d");
        $endDates = date('Y-m-d', strtotime('+1 day', strtotime($endDate)));
        $startDate = date('Y-m-01');
        for ($currentDate = strtotime($startDate); $currentDate <= strtotime($endDates); $currentDate += (86400)) {
            $Store = date('Y-m-d', $currentDate);
            $datecollection[] = $Store;
        }

        $returnCollection = $this->orderCollectionFactory->create()->AddFieldToSelect('*')
            ->addFieldToFilter('payment_method', ['in' => $paymentArray])
            ->addFieldToFilter('status', 'closed')
            ->addFieldToFilter('updated_at', ['from' => $startDate, 'to' => $endDates])
            ->addExpressionFieldToSelect('this_is_total', 'SUM({{base_grand_total}})', 'base_grand_total');

        $returnCollection->getSelect()->group('DATE_FORMAT(updated_at, "%d%m%y")');
        $staticsArray = $this->prepareDailyData($returnCollection);

        $salesCollection = $this->orderCollectionFactory->create()->AddFieldToSelect('*')
            ->addFieldToFilter('payment_method', ['in' => $paymentArray])
            ->addFieldToFilter('status', 'complete')
            ->addFieldToFilter('updated_at', ['from' => $startDate, 'to' => $endDates])
            ->addExpressionFieldToSelect('this_is_total', 'SUM({{base_grand_total}})', 'base_grand_total');
        $salesCollection->getSelect()->group('DATE_FORMAT(updated_at, "%d%m%y")');

        $staticsArrays = $this->prepareDailyData($salesCollection);
        $preparedData=$this->dateDataTrim($datecollection, $staticsArray, $staticsArrays, []);

        return [
            "sales" => $preparedData['salesData'],
            "return" => $preparedData['returnData'],
            "date" => $datecollection
        ];
    }
}
