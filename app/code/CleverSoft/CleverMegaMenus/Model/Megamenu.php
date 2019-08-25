<?php
/**
 * @category    CleverSoft
 * @package     CleverMegaMenus
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverMegaMenus\Model;
class Megamenu extends \Magento\Framework\Model\AbstractModel {
	protected function _construct(){
        $this->_cacheTag = 'megamenu';
        $this->_eventPrefix = 'megamenu';
		$this->_init('CleverSoft\CleverMegaMenus\Model\ResourceModel\Megamenu');
	}
}
