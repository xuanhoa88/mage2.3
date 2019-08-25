<?php
/**
 * @category    CleverSoft
 * @package     CleverLayeredNavigation
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverLayeredNavigation\Model\Source;

class CategoryPage implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        return [
            ['value' => 'category_current', 'label' => __('Keep current URL')],
            ['value' => 'category_pure', 'label' => __('URL Without Filters')],
            ['value' => 'category_brand_filter', 'label' => __('Brand Filter Only')],
            ['value' => 'category_first_attribute', 'label' => __('First Attribute Value')],
            ['value' => 'category_cut_off_get', 'label' => __('Current URL without Get parameters')],
        ];
    }
}