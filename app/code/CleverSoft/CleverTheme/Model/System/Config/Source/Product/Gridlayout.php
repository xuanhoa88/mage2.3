<?php
/**
 * @category    CleverSoft
 * @package     CleverTheme
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverTheme\Model\System\Config\Source\Product;

class Gridlayout implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        return [
            ['value' => 'product_grid_style_1',            'label' => __('Style 1')],
            ['value' => 'product_grid_style_2',            'label' => __('Style 2')],
            ['value' => 'product_grid_style_3',            'label' => __('Style 3')],
            ['value' => 'product_grid_style_4',            'label' => __('Style 4')],
            ['value' => 'product_grid_style_5',            'label' => __('Style 5')],
            ['value' => 'product_grid_style_6',            'label' => __('Style 6')],
            ['value' => 'product_grid_style_7',            'label' => __('Style 7')],
            ['value' => 'product_grid_style_8',            'label' => __('Style 8')],
            ['value' => 'product_grid_style_9',            'label' => __('Style 9')],
            ['value' => 'product_grid_style_10',            'label' => __('Style 10')],
        ];
    }
}
