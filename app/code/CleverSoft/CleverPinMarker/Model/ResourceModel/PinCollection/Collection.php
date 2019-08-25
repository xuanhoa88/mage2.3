<?php
/**
 * @category    CleverSoft
 * @package     CleverPinMarker
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverPinMarker\Model\ResourceModel\PinCollection;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('CleverSoft\CleverPinMarker\Model\PinCollection', 'CleverSoft\CleverPinMarker\Model\ResourceModel\PinCollection');
        $this->_idFieldName = 'id';
    }
}
