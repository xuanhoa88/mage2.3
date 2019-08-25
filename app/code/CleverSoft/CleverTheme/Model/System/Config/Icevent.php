<?php
/**
 * @category    CleverSoft
 * @package     CleverTheme
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverTheme\Model\System\Config;
class Icevent implements \Magento\Framework\Option\ArrayInterface {
    public function toOptionArray()
    {

        $types = [
            ['value' => 'none', 'label' => __('Standard')],
            ['value' => 'auto', 'label' => __('Infinite Scroll')],
            ['value' => 'manual', 'label' => __('Load More Button')],
        ];

        return $types;
    }
}