<?php
/**
 * @category    CleverSoft
 * @package     CleverBuilder
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverBuilder\Model;

class PanelsData extends \Magento\Framework\Model\AbstractModel {
    protected function _construct(){
        $this->_cacheTag = 'panelsx';
        $this->_eventPrefix = 'panels';
        $this->_init('CleverSoft\CleverBuilder\Model\ResourceModel\PanelsData');
    }
}
