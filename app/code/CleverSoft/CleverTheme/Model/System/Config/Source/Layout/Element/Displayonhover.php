<?php
/**
 * @category    CleverSoft
 * @package     CleverTheme
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverTheme\Model\System\Config\Source\Layout\Element;
class Displayonhover implements \Magento\Framework\Option\ArrayInterface{

    public function toOptionArray()
    {
        $types = [
            ['value' => 'remove', 'label' => __('Disable')],
            ['value' => 'visible', 'label' => __('Enable')]
        ];

        return $types;
    }

}