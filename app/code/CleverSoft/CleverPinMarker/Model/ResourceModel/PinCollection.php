<?php
/**
 * @category    CleverSoft
 * @package     CleverPinMarker
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */
namespace CleverSoft\CleverPinMarker\Model\ResourceModel;

use Magento\Framework\Model\AbstractModel;

/**
 * cleversoft_pinmarker_items mysql resource
 */
class PinCollection extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('cleversoft_pinmarker_collection', 'id');
    }
}
