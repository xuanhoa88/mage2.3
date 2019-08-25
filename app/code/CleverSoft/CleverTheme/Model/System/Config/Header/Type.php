<?php
/**
 * @category    CleverSoft
 * @package     CleverTheme
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverTheme\Model\System\Config\Header;
class Type implements \Magento\Framework\Option\ArrayInterface {
    public function toOptionArray()
    {
        $types = [
            ['value' => '1', 'label' => __('Header Layout 1')],
			['value' => '2', 'label' => __('Header Layout 2')],
			['value' => '3', 'label' => __('Header Layout 3')],
            ['value' => '4', 'label' => __('Header Layout 4')],
            ['value' => '5', 'label' => __('Header Layout 5')],
			['value' => '6', 'label' => __('Header Layout 6')],
			['value' => '7', 'label' => __('Header Layout 7')],
			['value' => '8', 'label' => __('Header Layout 8')],
			['value' => '9', 'label' => __('Header Layout 9')],
			['value' => '10', 'label' => __('Header Layout 10')],
			['value' => '11', 'label' => __('Header Layout 11')]
        ];
        return $types;
    }
}