<?php
/**
 * @category    CleverSoft
 * @package     CleverMegaMenus
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverMegaMenus\Model\ResourceModel;

class Megamenu extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb{
	protected function _construct(){
		$this->_init('cleversoft_megamenus','id');
	}
	
}
