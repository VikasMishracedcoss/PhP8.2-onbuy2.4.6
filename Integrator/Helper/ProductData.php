<?php

namespace Ced\Integrator\Helper;

class ProductData extends \Magento\Framework\App\Helper\AbstractHelper
{
    public $productChangeLog;
    public $productRepository;
    public $_attributeFactory;
    public $collectionFactory;
    public $path;
    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory,
        \Ced\Integrator\Model\Path $path,
        \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $attributeFactory,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Ced\Integrator\Model\ProductChangeLogFactory $productChangeLog
    ) {
        $this->productRepository = $productRepository;
        $this->_attributeFactory = $attributeFactory;
        $this->collectionFactory = $collectionFactory;
        $this->path = $path;
        $this->productChangeLog = $productChangeLog;
    }

    public function getproductdata()
    {

        $moduleName = $this->path->moduleName();

        /**Fetching Product Upload Data,Product Validation Data For Walmart */
        if ($moduleName == 'Walmart') {
            $collection = $this->collectionFactory;

            $notUploaded = $collection->create()->addAttributeToSelect('*')
                ->addAttributeToFilter('visibility', ['eq' => 4])
                ->addAttributeToFilter('walmart_product_status', ['eq' => 'Not-Uploaded'])->addAttributeToFilter('walmart_profile_id', ['nin' => 'Unassigned'])
                ->getSize();

            $notValidated = $collection->create()->addAttributeToSelect('*')
                ->addAttributeToFilter('visibility', ['eq' => 4])
                ->addAttributeToFilter('walmart_product_validation', ['eq' => 'Not-Validated'])->addAttributeToFilter('walmart_profile_id', ['nin' => 'Unassigned'])
                ->getSize();

            $validated = $collection->create()->addAttributeToSelect('*')
                ->addAttributeToFilter('visibility', ['eq' => 4])
                ->addAttributeToFilter('walmart_product_validation', ['eq' => 'Validated'])->addAttributeToFilter('walmart_profile_id', ['nin' => 'Unassigned'])
                ->getSize();

            $error = $collection->create()->addAttributeToSelect('*')
                ->addAttributeToFilter('visibility', ['eq' => 4])
                ->addAttributeToFilter('walmart_product_validation', ['eq' => 'INVALID'])->addAttributeToFilter('walmart_profile_id', ['nin' => 'Unassigned'])
                ->getSize();

            $unpublished = $collection->create()->addAttributeToSelect('*')
                ->addAttributeToFilter('visibility', ['eq' => 4])
                ->addAttributeToFilter('walmart_product_status', ['eq' => 'UNPUBLISHED'])->addAttributeToFilter('walmart_profile_id', ['nin' => 'Unassigned'])
                ->getSize();

            $published = $collection->create()->addAttributeToSelect('*')
                ->addAttributeToFilter('visibility', ['eq' => 4])
                ->addAttributeToFilter('walmart_product_status', ['eq' => 'PUBLISHED'])->addAttributeToFilter('walmart_profile_id', ['nin' => 'Unassigned'])
                ->getSize();

            return [
                'notuploaded' => $notUploaded,
                'unpublished' => $unpublished,
                'published' => $published,
                'validated' => $validated,
                'notvalidated' => $notValidated,
                'error' => $error
            ];
        } elseif ($moduleName == 'EbayMultiAccount') {
            /**Fetching Product Upload Data,Product Validation Data For EbayMultiAccount */
            $account = $this->path->accountId();
            $collection = $this->collectionFactory;

            $validentry = $collection->create()->addAttributeToSelect('*')
                ->addAttributeToFilter('visibility', ['eq' => 4])
                ->addAttributeToFilter('em_listing_error_' . $account, ['eq' => '["valid"]'])
                ->getSize();

            $invalidcount = $collection->create()->addAttributeToSelect('*')
                ->addAttributeToFilter('visibility', ['eq' => 4])
                ->addAttributeToFilter('em_prod_status_' . $account, ['notnull' => true])
                ->addAttributeToFilter('em_listing_error_' . $account, ['notnull' => true])
                ->getSize();

            $invalidentry = $invalidcount - $validentry;
            $notvalidated = $collection->create()->addAttributeToSelect('*')
                ->addAttributeToFilter('visibility', ['eq' => 4])
                ->addAttributeToFilter('em_prod_status_' . $account, ['notnull' => true])
                ->addAttributeToFilter('em_listing_error_' . $account, ['null' => true])
                ->getSize();



            $notUploaded = $collection->create()->addAttributeToSelect('*')
                ->addAttributeToFilter('visibility', ['eq' => 4])
                ->addAttributeToFilter('em_prod_status_' . $account, ['eq' => '1'])
                ->getSize();

            $published = $collection->create()->addAttributeToSelect('*')
                ->addAttributeToFilter('visibility', ['eq' => 4])
                ->addAttributeToFilter('em_prod_status_' . $account, ['eq' => '4'])
                ->getSize();

            $unpublished = $collection->create()->addAttributeToSelect('*')
                ->addAttributeToFilter('visibility', ['eq' => 4])
                ->addAttributeToFilter('em_prod_status_' . $account, ['eq' => '2'])
                ->getSize();

            return [
                'notuploaded' => $notUploaded,
                'unpublished' => $unpublished,
                'published' => $published,
                'error' => $invalidentry,
                'validated' => $validentry,
                'notvalidated' => $notvalidated

            ];
        } elseif ($moduleName == 'Amazon') {
            /**Fetching Product Upload Data,Product Validation Data For Amazon */
            $account = $this->path->accountId();
            $collection = $this->collectionFactory;

            $objectManager =  \Magento\Framework\App\ObjectManager::getInstance();

            $collection = $objectManager->get('\Ced\Amazon\Model\ResourceModel\Product\CollectionFactory');

            $active = $collection->create()->addFieldToSelect('*')
                ->addFieldToFilter('account_id', $account)
                ->addFieldToFilter('status', 'active')->getSize();

            $inactive = $collection->create()->addFieldToSelect('*')
                ->addFieldToFilter('account_id', $account)
                ->addFieldToFilter('status', 'inactive')->getSize();

            $incomplete = $collection->create()->addFieldToSelect('*')
                ->addFieldToFilter('account_id', $account)
                ->addFieldToFilter('status', 'Incomplete')->getSize();

            $valid = $collection->create()->addFieldToSelect('*')
                ->addFieldToFilter('account_id', $account)
                ->addFieldToFilter('validation_errors', ['eq' => '["valid"]'])->getSize();

            $invalidcount = $collection->create()->addFieldToSelect('*')
                ->addFieldToFilter('account_id', $account)
                ->addFieldToFilter('validation_errors', ['notnull' => true])->getSize();

            $invalid = $invalidcount - $valid;

            $notvalidated = $collection->create()->addFieldToSelect('*')
                ->addFieldToFilter('account_id', $account)
                ->addFieldToFilter('validation_errors', ['null' => true])->getSize();

            return [
                'notuploaded' => $valid,
                'unpublished' => $invalid,
                'published' => $notvalidated,
                'error' => $inactive,
                'validated' => $active,
                'notvalidated' => $incomplete

            ];
        }
    }
    public function insertMultipleRows($data) {
        try {
            if (!empty($data) && isset($data['product_id']) && isset($data['action'])) {
                $model = $this->productChangeLog->create();
                $model->setProductId($data['product_id']);
                $model->setAction($data['action']);
                $model->save();
            }
        } catch (\Exception $e) {
            //silence
        }
    }
}
