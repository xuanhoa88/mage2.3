<?php
/**
 * @category    CleverSoft
 * @package     CleverLayeredNavigation
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverLayeredNavigation\Model\Source;

class AllProductPage implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        return [
            ['value' => 'root_current', 'label' => __('Keep current URL')],
            ['value' => 'root_pure', 'label' => __('URL Key Only')],
            ['value' => 'root_first_attribute', 'label' => __('First Attribute Value')],
            ['value' => 'root_cut_off_get', 'label' => __('Current URL without Get parameters')],
        ];
    }
}