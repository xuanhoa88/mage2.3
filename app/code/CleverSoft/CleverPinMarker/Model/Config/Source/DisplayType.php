<?php
/**
 * @category    CleverSoft
 * @package     CleverPinMarker
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverPinMarker\Model\Config\Source;

class DisplayType implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray(){
        return [
            '' => __('Default'),
            'masonry'  => __('Masonry'),
            'slider' => __('Slider')
        ];
    }
}