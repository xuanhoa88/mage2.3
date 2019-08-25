<?php
/**
 * @category    CleverSoft
 * @package     CleverCookieLaw
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverCookieLaw\Model\Config;

class Skin implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        return [
            ['value' => '', 'label' => __('Without skin')],
            ['value' => 'v-yellow-alert', 'label' => __('Yellow alert')],
            ['value' => 'v-dark-clean', 'label' => __('Dark clean')],
            ['value' => 'v-minimalist', 'label' => __('Minimalist')],
        ];
    }
}
