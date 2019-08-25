<?php
/**
 * @category    CleverSoft
 * @package     CleverTheme
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverTheme\Model\System\Config\Header;
class MaxWidthSelections implements \Magento\Framework\Option\ArrayInterface {
    public function toOptionArray()
    {

        $types = [
            ['value' => '1200', 'label' => __('1200px')],
            ['value' => '1440', 'label' => __('1440px')],
            ['value' => '1680', 'label' => __('1680px')],
            ['value' => '1920', 'label' => __('1920px')],
            ['value' => 'fullwidth', 'label' => __('Full width')]
        ];

        return $types;
    }
}