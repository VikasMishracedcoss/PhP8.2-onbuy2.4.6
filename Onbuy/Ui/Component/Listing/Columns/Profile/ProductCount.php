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
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CEDCOMMERCE (http://cedcommerce.com/)
 * @license     http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Onbuy\Ui\Component\Listing\Columns\Profile;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Class ProductValidation
 */
class ProductCount extends Column
{
    /**
     * @var UrlInterface
     */
    public $urlBuilder;

    public $profileProduct;

    public $productModel;

    /**
     * @var \Ced\Onbuy\Helper\MultiAccount
     */
    protected $multiAccountHelper;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        \Ced\Onbuy\Model\ResourceModel\Profileproducts\CollectionFactory $profileProduct,
        \Magento\Catalog\Model\Product $productModel,
        \Ced\Onbuy\Helper\MultiAccount $multiAccountHelper,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->profileProduct = $profileProduct;
        $this->productModel = $productModel;
        $this->multiAccountHelper = $multiAccountHelper;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource) {

        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
//                echo "<pre>";
//                print_r($item);die;
                $profileAccountAttribute = $this->multiAccountHelper->getProfileAttrForAcc($item['account_id']);
                $pProds = $this->profileProduct->create()->addFieldToFilter('account_id', $item['account_id'])
                    ->addFieldToFilter('profile_id', $item['id'])->getColumnValues('product_id');
                $products = $this->productModel->getCollection()->addFieldToFilter('entity_id', ['in' => $pProds]);
                $products->addFieldToFilter('type_id', ['simple', 'configurable'])
                    ->addAttributeToFilter('visibility',  ['neq' => 1]);
                $value = count($products);
                $item['product_count'] = $value;

            }
        }

        return $dataSource;
    }

}
