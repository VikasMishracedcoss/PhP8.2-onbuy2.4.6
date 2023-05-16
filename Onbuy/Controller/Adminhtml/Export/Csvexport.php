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

class Csvexport extends \Magento\Backend\App\Action
{
   

/** @var \Magento\Framework\Stdlib\DateTime\DateTime $date */
public $date;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Ced\Onbuy\Model\Profileproducts $locationFactory,
        \Magento\Framework\Filesystem\Driver\File $fileDriver,
        \Magento\Framework\File\Csv $csvParser
    ) {
        parent::__construct($context);
        $this->fileDriver = $fileDriver;
        $this->date = $date;
        $this->csvParser = $csvParser;
        $this->_fileFactory = $fileFactory;
        $this->_locationFactory = $locationFactory;
        $this->directory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR); // VAR Directory Path
    }

    public function execute()
    {

        
       // $productFeedHelper = $this->productFeedHelper;
        // $name = $productFeedHelper::FILE_NAME;
        // $filepath = $productFeedHelper::FILE_PATH;
        $name = 'onbuy_product_listing';
        $filepath = 'export/custom' . $name . '.csv';
        $filterArray = [
            'start_date' => $this->prepareDate(1, 'second'),
            'end_date' => $this->prepareDate(1, 'day')
        ];
       // $this->productFeedHelper->updateErrorsCsvFromFeed($filterArray);
       $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
       $product = $objectManager->get('Ced\Onbuy\Ui\DataProvider\Product\OnbuyProduct')->getData();
      
        $newErrors = array_values($product['items']);
      
        $this->directory->create('export');

        $stream = $this->directory->openFile($filepath, 'w+');
        $stream->lock();

        //column name dispay in your CSV

        $columns = ['entity_id', 
        'attribute_set_id', 
        'type_id',
        'sku',
        'has_options','required_options',
        'created_at','updated_at', 'qty',
        'visibility',
         'onbuy_profile_id',
         'onbuy_product_status', 
        'onbuy_listing_error', 
        'opc','name','thumbnail','url_key','gift_message_available',
        'product_ean', 
        'price','status','tax_class_id','store_id',
        'color'];

        foreach ($columns as $column)
        {
            $header[] = $column; //storecolumn in Header array
        }
      
        $stream->writeCsv($header);
       
        foreach($newErrors as $newError){
         
            $stream->writeCsv($newError);
        } 

        $content = [];
        $content['type'] = 'filename'; // must keep filename
        $content['value'] = $filepath;
        $content['rm'] = '0'; //remove csv from var folder

        $csvfilename = $name.'.csv';
        return $this->_fileFactory->create($csvfilename, $content, DirectoryList::VAR_DIR);

    }


    public function prepareDate($numberOfWeek, $intervalType = 'day') {
        $date = ($numberOfWeek && $intervalType)
            ? $this->date->gmtDate('Y-m-d\TH:i:s\Z', strtotime("-{$numberOfWeek} {$intervalType}"))
            : false;
        return $date;
    }


    /**
     * Retrieve data from file
     *
     * @return string[]
     */
    public function getFileData($file)
    {
        $data = [];
        if ($this->fileDriver->isExists($file)) {
            $this->csvParser->setDelimiter(',');
            $data = $this->csvParser->getData($file);
        }
        return $data;
    }
}
