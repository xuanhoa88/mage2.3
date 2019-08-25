<?php
/**
 * @category    CleverSoft
 * @package     CleverShopByBrand
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverShopByBrand\Model\ResourceModel;

use Magento\Framework\Model\AbstractModel;

/**
 * cleversoft_shopbybrand mysql resource
 */
class Brand extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
	protected $_isPkAutoIncrement = false;
	
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('cleversoft_shopbybrand', 'id');
    }
}
