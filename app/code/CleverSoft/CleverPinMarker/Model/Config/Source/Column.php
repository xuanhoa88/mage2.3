<?php
/**
 * @category    CleverSoft
 * @package     CleverPinMarker
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverPinMarker\Model\Config\Source;

class Column implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray(){
        return [
            1  => __('1 Column'),
            2 => __('2 Columns'),
            3 => __('3 Columns'),
            4 => __('4 Columns'),
            5 => __('5 Columns')
        ];
    }
}