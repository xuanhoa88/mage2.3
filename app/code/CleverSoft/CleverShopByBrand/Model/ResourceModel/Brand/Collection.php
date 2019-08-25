<?php
/**
 * @category    CleverSoft
 * @package     CleverShopByBrand
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */
namespace CleverSoft\CleverShopByBrand\Model\ResourceModel\Brand;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('CleverSoft\CleverShopByBrand\Model\Brand', 'CleverSoft\CleverShopByBrand\Model\ResourceModel\Brand');
    }

    /**
     * Returns pairs id - title
     *
     * @return array
     */
    public function toOptionArray()
    {
        return $this->_toOptionArray('id', 'brand_label');
    }
}
