<?php
/**
 * @category    CleverSoft
 * @package     CleverMegaMenus
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverMegaMenus\Controller\Adminhtml\Massaction;
use CleverSoft\CleverMegaMenus\Controller\Adminhtml\MassStatus;
class MassEnable extends MassStatus{
	const ID_FIELD = 'id';
	protected $collection = 'CleverSoft\CleverMegaMenus\Model\ResourceModel\Megamenu\Collection';
	protected $model = 'CleverSoft\CleverMegaMenus\Model\Megamenu';
	protected $status = true;
}