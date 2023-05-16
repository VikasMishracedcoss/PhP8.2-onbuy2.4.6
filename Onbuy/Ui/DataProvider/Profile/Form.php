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
 * @license      http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Onbuy\Ui\DataProvider\Profile;

use Magento\Framework\UrlInterface;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;

/**
 * Class DataProvider for EbayMultiAccount Categories
 */
class Form extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    public $collection;

    /**
     * @var $addFieldStrategies
     */
    public $addFieldStrategies;

    /**
     * @var $addFilterStrategies
     */
    public $addFilterStrategies;

    /** @var UrlInterface  */
    public $url;


    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        UrlInterface $url,
        \Ced\Onbuy\Model\ResourceModel\Profile\CollectionFactory $collectionFactory,
        $addFieldStrategies = [],
        $addFilterStrategies = [],
        $meta = [],
        $data = []
    )
    {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->url = $url;
        $this->collection = $collectionFactory->create();
        $this->addFieldStrategies = $addFieldStrategies;
        $this->addFilterStrategies = $addFilterStrategies;
    }


    /**
     *
     * @return array
     */
    public function getData()
    {
        if (!$this->getCollection()->isLoaded()) {
            $this->getCollection()->load();
        }
        /** @var array $items */
        $items = $this->getCollection();
        $data = [];

        foreach ($items as &$item) {
            $item->setData("id_field_name", 'id');
            $data[$item->getId()] = $item->getData();
        }
        return $data;
    }
}

