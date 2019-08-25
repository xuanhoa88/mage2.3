<?php
/**
 * @category    CleverSoft
 * @package     CleverLayeredNavigation
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverLayeredNavigation\Model\ResourceModel\FilterSetting;


class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init('CleverSoft\CleverLayeredNavigation\Model\FilterSetting', 'CleverSoft\CleverLayeredNavigation\Model\ResourceModel\FilterSetting');
    }
}
