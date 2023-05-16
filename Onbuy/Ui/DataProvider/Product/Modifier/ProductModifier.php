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
 * @copyright   Copyright © 2018 CedCommerce. All rights reserved.
 * @license     EULA http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Onbuy\Ui\DataProvider\Product\Modifier;

use Ced\Onbuy\Helper\Data;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;

class ProductModifier implements ModifierInterface
{
    public $request;

    public $collectionFactory;

    public $profileCollection;

    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Ced\Onbuy\Helper\Data $data,
        \Ced\Onbuy\Model\ResourceModel\Profileproducts\CollectionFactory $profileCollection,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory
    ) {
        $this->request = $request;
        $this->data = $data;
        $this->profileCollection = $profileCollection;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @param array $data
     * @return array
     * @since 100.1.0
     */
    public function modifyData(array $data)
    {
        $accountId = $this->data->getAccountSession();
        $productIds = array_column($data, 'entity_id');
        $profileData = $this->profileCollection->create()->addFieldToFilter('account_id', ['eq' => $accountId])
            ->addFieldToFilter('product_id', ['in' => $productIds])->getData();
        $profileIds = array_column($profileData, 'profile_id');

        foreach ($data as &$item) {
            if (empty($item['product_status'])) {
                $item['product_status'] = 'NOT_UPLOADED';
            }
        }
        return $data;
    }

    /**
     * @param array $meta
     * @return array
     * @since 100.1.0
     */
    public function modifyMeta(array $meta)
    {
        return $meta;
    }
}
