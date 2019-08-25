<?php
/**
 * @category    CleverSoft
 * @package     CleverCookieLaw
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverCookieLaw\Model\Config\Box;

class Position implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        return [
            ['value' => 'v-top-left', 'label' => __('Top left')],
            ['value' => 'v-top-right', 'label' => __('Top right')],
            ['value' => 'v-bottom', 'label' => __('Bottom')],
            ['value' => 'v-bottom-left', 'label' => __('Bottom left')],
            ['value' => 'v-bottom-right', 'label' => __('Bottom right')],
        ];
    }
}