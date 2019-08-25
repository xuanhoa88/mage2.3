<?php
/**
 * @category    CleverSoft
 * @package     CleverTheme
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author      ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverTheme\Model\ResourceModel;

use Magento\Framework\Model\AbstractModel;

/**
 * cleversoft_header_builder mysql resource
 */
class Block extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
	protected $_isPkAutoIncrement = false;
	
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('clever_header_builder', 'id');
    }
}
