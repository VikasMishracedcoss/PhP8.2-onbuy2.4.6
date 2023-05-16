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

namespace Ced\Onbuy\Ui\Component\Listing\Columns\Feeds;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\Json\Helper\Data;

/**
 * Class Validation
 * @package Ced\Onbuy\Ui\Component\Listing\Columns\Feeds
 */
class Validation extends Column
{
    /**
     * @var UrlInterface
     */
    public $urlBuilder;

    /**
     * @var Data
     */
    public $json;

    /**
     * @var
     */
    public $product;

    /**
     * Validation constructor.
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param Data $json
     * @param ProductFactory $productFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        Data $json,
        ProductFactory $productFactory,
        $components = [],
        $data = []
    ) {
        $this->product = $productFactory->create();
        $this->urlBuilder = $urlBuilder;
        $this->json = $json;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $name = $this->getData('name');
                if (isset($item[$name])) {
                    $data = $item[$name];
                    $item[$name] = [];
                    $item[$name]['view'] = [
                        'label' => __('View'),
                        'popup' => [
                            'title' => __("File Data"),
                            'type' => 'json',
                            'render' => 'html',
                            'message' => $data
                        ],
                        'class' => 'cedcommerce actions view'
                    ];
                }
            }
        }

        return $dataSource;
    }
}
