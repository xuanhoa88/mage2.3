<?php
/**
 * @category    CleverSoft
 * @package     CleverTheme
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverTheme\Model\System\Config;
class Salelabel implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        $types = [
            ['value' => 'sale', 'label' => __('Sale')],
            ['value' => 'imagesale', 'label' => __('Image')],
            ['value' => '', 'label' => __('No')],
        ];

        return $types;
    }
}