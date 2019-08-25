<?php
/**
 * @category    CleverSoft
 * @package     CleverCookieLaw
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverCookieLaw\Model\ResourceModel\Rectify;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('CleverSoft\CleverCookieLaw\Model\Rectify', 'CleverSoft\CleverCookieLaw\Model\ResourceModel\Rectify');
        $this->_idFieldName = 'id';
    }
}
