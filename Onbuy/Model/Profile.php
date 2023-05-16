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
 * @package     Ced_Onbuy
 * @author 		CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */


namespace Ced\Onbuy\Model;

class Profile extends \Magento\Framework\Model\AbstractModel
{
    public $productActionFactory;

    /**
     * Profile constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollection
     * @param \Magento\Catalog\Model\Product\ActionFactory $productActionFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollection,
        \Ced\Onbuy\Model\ResourceModel\Profileproducts\CollectionFactory $profileProductsCollection,
        \Magento\Catalog\Model\Product\ActionFactory $productActionFactory,
        \Ced\Onbuy\Model\ProfileproductsFactory $profileProducts,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->productCollection = $productCollection;
        $this->profileProducts = $profileProducts;
        $this->profileProductsCollection = $profileProductsCollection;
        $this->productActionFactory = $productActionFactory;
    }

    /**
     * @return void
     */
    public function _construct()
    {
        $this->_init('Ced\Onbuy\Model\ResourceModel\Profile');
    }


    /**
     * Load entity by attribute
     *
     * @param string|array field
     * @param null|string|array $value
     * @param string $additionalAttributes
     * @return mixed
     */
    public function loadByField($field, $value, $additionalAttributes = '*')
    {
        $collection = $this->getResourceCollection()->addFieldToSelect($additionalAttributes);
        if(is_array($field) && is_array($value)){
            foreach($field as $key=>$f) {
                if(isset($value[$key])) {
                    //$f = $helper->getTableKey($f);
                    $collection->addFieldToFilter($f, $value[$key]);
                }
            }
        } else {
            $collection->addFieldToFilter($field, $value);
        }

        $collection->setCurPage(1)
            ->setPageSize(1);
        foreach ($collection as $object) {
            $this->load($object->getId());
            return $this;
        }
        return $this;
    }

    /**
     * @param $profileProducts
     * @param $profileId
     * @param $accId
     */
    public function updateProducts($profileProducts, $profileId, $accId)
    {

        if ($profileId && $accId) {
            $oldIds = $this->profileProductsCollection->create()
                ->addFieldToFilter('profile_id', ['eq' => $profileId])
                ->addFieldToFilter('account_id',['eq' => $accId])
                ->addFieldToSelect('product_id');
            $oldIds = array_column($oldIds->getData(), 'product_id');
            $newIds = array_diff($profileProducts, $oldIds);

            $toBeRemoveIds = array_diff($oldIds, $profileProducts);

            if (!empty($newIds)) {
                foreach ($newIds as $newId){
                    $pProduct = $this->profileProductsCollection->create()->addFieldToFilter('product_id', $newId)
                        ->addFieldToFilter('account_id', $accId)->getFirstItem();
                    if (!empty($pProduct->getData())){

                        $pProduct->setProfileId($profileId)->save();


                    } else {
                        $this->profileProducts->create()
                            ->setProfileId($profileId)
                            ->setProductId($newId)
                            ->setAccountId($accId)
                            ->save();
                    }
                }

            }

            if (!empty($toBeRemoveIds)) {
                $this->profileProductsCollection->create();
                //foreach ($toBeRemoveIds as $toBeRemoveId){
                $this->profileProductsCollection->create()
                    ->addFieldToFilter('account_id', ['eq' => $accId])
                    ->addFieldToFilter('profile_id', ['eq' => $profileId])
                    ->addFieldToFilter('product_id', ['in', $toBeRemoveIds])
                    ->walk('delete');

            }

        }
    }
}