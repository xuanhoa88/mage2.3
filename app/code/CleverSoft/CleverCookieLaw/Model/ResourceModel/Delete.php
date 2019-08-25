<?php
/**
 * @category    CleverSoft
 * @package     CleverCookieLaw
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverCookieLaw\Model\ResourceModel;

use Magento\Framework\Model\AbstractModel;

/**
 * cleversoft_cookielaw_delete mysql resource
 */
class Delete extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('cleversoft_cookielaw_delete', 'id');
    }
}
