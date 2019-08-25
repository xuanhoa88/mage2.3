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
class AssignAttribute extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('eav_attribute_option', 'option_id');
    }
}
