<?php
/**
 * @category    CleverSoft
 * @package     CleverBuilder
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverBuilder\Model\ResourceModel\PanelsTemplate;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection {
	protected function _construct(){
		$this->_init('CleverSoft\CleverBuilder\Model\PanelsTemplate','CleverSoft\CleverBuilder\Model\ResourceModel\PanelsTemplate');
	}
}
