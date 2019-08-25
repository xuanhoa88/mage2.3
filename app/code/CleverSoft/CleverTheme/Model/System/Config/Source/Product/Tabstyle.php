<?php
/**
 * @category    CleverSoft
 * @package     CleverTheme
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverTheme\Model\System\Config\Source\Product;

class Tabstyle implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        return [
            ['value' => '', 'label' => __('Horizontal')], 
            ['value' => 'vertical', 'label' => __('Vertical')], 
            ['value' => 'accordion', 'label' => __('Accordion')]
        ];
    }

    public function toArray()
    {
        return [
            '' => __('Horizontal'), 
            'vertical' => __('Vertical'), 
            'accordion' => __('Accordion')
        ];
    }
}
