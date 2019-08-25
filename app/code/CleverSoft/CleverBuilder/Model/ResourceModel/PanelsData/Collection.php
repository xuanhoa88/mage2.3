<?php
/**
 * @category    CleverSoft
 * @package     CleverBuilder
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverBuilder\Model\ResourceModel\PanelsData;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection {
	protected function _construct(){
		$this->_init('CleverSoft\CleverBuilder\Model\PanelsData','CleverSoft\CleverBuilder\Model\ResourceModel\PanelsData');
	}
}
