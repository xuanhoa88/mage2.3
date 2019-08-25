<?php
/**
 * @category    CleverSoft
 * @package     CleverMegaMenus
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverMegaMenus\Model\ResourceModel\Megamenu;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection {
	protected function _construct(){
		$this->_init('CleverSoft\CleverMegaMenus\Model\Megamenu','CleverSoft\CleverMegaMenus\Model\ResourceModel\Megamenu');
	}
}
