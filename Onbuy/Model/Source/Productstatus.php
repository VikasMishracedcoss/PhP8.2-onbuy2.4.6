<?php
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement(EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category  Ced
 * @package   Ced_Onbuy
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */
namespace Ced\Onbuy\Model\Source;

/**
 * Class Productstatus
 * @package Ced\Onbuy\Model\Source
 */
class Productstatus extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    const STATUS_UPLOADED = 'uploaded';  // extension status
    const STATUS_PROCESSING = 'processing';  // extension status
    const STATUS_NOT_UPLOADED = 'not_uploaded'; // extension status
    const STATUS_INVALID = 'invalid'; // extension status
    const STATUS_VALID = 'valid'; // extension status
    const STATUS_ENDED = 'ended'; // extension status
    public $collection;
    public function __construct(
        \Ced\Onbuy\Model\ResourceModel\Accounts\CollectionFactory $collection

    )
    {
        $this->collection = $collection;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
       /* $data = $this->collection->create()->addFieldToFilter('account_status', ['eq' => 1] )->getAllIds();

        if (empty($data))
            return [];
        else*/
            return $this->getOptions();
    }

    /**
     * @return array
     */
    public function getAllOptions()
    {
        return [
            [
                'value' => self::STATUS_NOT_UPLOADED,
                'label' => __('Not Uploaded')
            ],
            [
                'value' => self::STATUS_PROCESSING,
                'label' => __('Processing on Onbuy')
            ],
            [
                'value' => self::STATUS_INVALID,
                'label' => __('Invalid On Onbuy')
            ],
            [
                'value' => self::STATUS_UPLOADED,
                'label' => __('Uploaded on Onbuy')
            ],
            [
                'value' => self::STATUS_VALID,
                'label' => __('Ready To Uplaod')
            ],
            [
                'value' => self::STATUS_ENDED,
                'label' => __('End Listing on Onbuy')
            ],
        ];
    }

    /**
     * Retrieve option array
     *
     * @return array
     */
    public function getOptionArray()
    {
        $options = [];
        foreach ($this->getAllOptions() as $option) {
            $options[$option['value']] =(string)$option['label'];
        }
        return $options;
    }

    /**
     * @return array
     */
    public function getAllOption()
    {
        $options = $this->getOptionArray();
        array_unshift($options, ['value' => '', 'label' => '']);
        return $options;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        $res = [];
        foreach ($this->getOptionArray() as $index => $value) {
            $res[] = ['value' => $index, 'label' => $value];
        }
        return $res;
    }

    /**
     * @param int|string $optionId
     * @return mixed|null
     */
    public function getOptionText($optionId)
    {
        $options = $this->getOptionArray();
        return isset($options[$optionId]) ? $options[$optionId] : null;
    }

}
