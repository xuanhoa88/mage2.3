<?php
/**
 * @category    CleverSoft
 * @package     CleverTheme
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author      ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverTheme\Model\System\Config\Source\Product;

class Pagelayout implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        return [
            ['value' => 'vertical_thumb', 'label' => __('Product V1')],
            ['value' => 'horizontal_thumb', 'label' => __('Product V2')],
            ['value' => 'carousel_thumb', 'label' => __('Product V3')],
            ['value' => 'grid_thumb', 'label' => __('Product V4')],
            ['value' => 'sticky_layout', 'label' => __('Product V5')],
            ['value' => 'accordion_tabs_horizontal', 'label' => __('Product V6')],
            ['value' => 'accordion_tabs_vertical', 'label' => __('Product V7')]
        ];
    }

    public function toArray()
    {
        return [
            'default' => __('Default'),
            'sticky_2' => __('Sticky 2')
        ];
    }
}
