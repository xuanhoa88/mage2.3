<?php
/**
 * @category    CleverSoft
 * @package     CleverBuilder
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverBuilder\Model\ResourceModel;

class PanelsTemplate extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb{
    protected function _construct(){
        $this->_init('cleversoft_panels_template','id');
    }

}
