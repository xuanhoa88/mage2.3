<?php
/**
 * @category    CleverSoft
 * @package     CleverInstagram
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverInstagram\Model\Config\Source;

class ImgResolution implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        return [
            ['value' => 'thumbnail', 'label' => __('Thumbnail - 150x150')],
            ['value' => 'low_resolution', 'label' => __('Medium - 306x306')],
            ['value' => 'standard_resolution', 'label' => __('Large - 640x640')],
        ];
    }
}