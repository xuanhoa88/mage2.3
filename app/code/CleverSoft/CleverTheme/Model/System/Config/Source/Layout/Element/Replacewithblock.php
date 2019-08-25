<?php
/**
 * @category    CleverSoft
 * @package     CleverTheme
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */
 
namespace CleverSoft\CleverTheme\Model\System\Config\Source\Layout\Element;
class Replacewithblock implements \Magento\Framework\Option\ArrayInterface{

    public function toOptionArray()
    {
        $types = [
            ['value' => '0', 'label' => __('Disable Completely')],
            ['value' => '1', 'label' => __('Don\'t Replace With Static Block')],
            ['value' => '2', 'label' => __('If Empty, Replace With Static Block')],
            ['value' => '3', 'label' => __('Replace With Static Block')]
        ];

        return $types;
    }

}