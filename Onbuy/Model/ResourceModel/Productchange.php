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

namespace Ced\Onbuy\Model\ResourceModel;

class Productchange extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @return void
     */
    public function _construct()
    {

        $this->_init('onbuy_product_change', 'id');

    }

    public function deleteFromProductChange($productIds, $type, $accountId)
    {

        if ( count($productIds)<=0) {
            return $this;
        }

        $dbh = $this->getConnection();
        $condition = "{$this->getTable('onbuy_product_change')}.product_id in (" . $dbh->quote($productIds).")";
        //. " AND {$this->getTable('jet/profileproducts')}.profile_id = " . $dbh->quote($profileId);
        $condition .= " AND {$this->getTable('onbuy_product_change')}.cron_type = '".$type."'";
        $condition .= " AND {$this->getTable('onbuy_product_change')}.account_id = '".$accountId."'";

        $dbh->delete($this->getTable('onbuy_product_change'), $condition);
        return $this;
    }

}

