<?php

/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category    Ced
 * @package     Ced_EbayMultiAccount
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license     http://cedcommerce.com/license-agreement.txt
 */
namespace Ced\Onbuy\Controller\Adminhtml\Export;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ResponseInterface;

class Excelexport extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    protected $fileFactory;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @var \Magento\Framework\View\Result\LayoutFactory
     */
    protected $resultLayoutFactory;

    /**
     * @var \Magento\Framework\File\Csv
     */
    protected $csvProcessor;

    /**
     * @var \Magento\Framework\App\Filesystem\DirectoryList
     */
    protected $directoryList;

    /**
     * @param \Magento\Framework\App\Action\Context            $context
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     * @param \Ced\Onbuy\Model\Profileproducts                 $productFactory
     * @param \Magento\Framework\View\Result\LayoutFactory     $resultLayoutFactory
     * @param \Magento\Framework\File\Csv                      $csvProcessor
     * @param \Magento\Framework\App\Filesystem\DirectoryList  $directoryList
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Ced\Onbuy\Model\Profileproducts $productFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Magento\Framework\File\Csv $csvProcessor,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList
    ) {
        $this->fileFactory = $fileFactory;
        $this->productFactory = $productFactory;
        $this->resultLayoutFactory = $resultLayoutFactory;
        $this->csvProcessor = $csvProcessor;
        $this->directoryList = $directoryList;
        parent::__construct($context);
    }

    /**
     * Excel File Create and Download
     *
     * @return ResponseInterface
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function execute()
    {

     /** Add yout header name here */
       $columns = [ 'entity_id', 
                    'attribute_set_id', 
                    'type_id',
                    'sku',
                    'has_options',
                    'required_options',
                    'created_at',
                    'updated_at', 
                    'qty',
                    'visibility',
                    'onbuy_profile_id',
                    'onbuy_product_status', 
                    'onbuy_listing_error', 
                    'opc',
                    'store_id'
                    ];

        $resultLayout = $this->resultLayoutFactory->create();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $product = $objectManager->get('Ced\Onbuy\Ui\DataProvider\Product\OnbuyProduct')->getData();
      // print_r(count($product['items'])); die(__DIR__);
        $fileName = 'Onbuy_product_excel.xls'; // Add Your CSV File name

        $filePath =  $this->directoryList->getPath(DirectoryList::MEDIA) . "/" . $fileName;

        $newErrors = array_values($product['items']);

           $a =1;

       // while ($product = count($product['items'])) {
           foreach($newErrors as $product){
            if($a==1){
                $content[] = $columns;
              } $a = $a+1;

            $content[] =[ 
            $product['entity_id'],
            $product['attribute_set_id'],
            $product['type_id'],
            $product['sku'],
            $product['has_options'],
            $product['required_options'],
            $product['created_at'],
            $product['updated_at'],
            $product['qty'],
            $product['visibility'],
            $product['onbuy_profile_id'],
            $product['onbuy_product_status'], 
            $product['onbuy_listing_error'],
            $product['opc'],
            $product['store_id'],
            ];
        }
       
     




        //print_r($content); die(__DIR__);
        $this->csvProcessor->setEnclosure('"')->setDelimiter(',')->saveData($filePath, $content);
        return $this->fileFactory->create(
            $fileName,
            [
                'type'  => "filename",
                'value' => $fileName,
                'rm'    => true, // True => File will be remove from directory after download.
            ],
            DirectoryList::MEDIA,
            'text/xls',
            null
        );


    }
}