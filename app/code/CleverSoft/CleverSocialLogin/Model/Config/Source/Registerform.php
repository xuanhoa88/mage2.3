<?php
/**
 * @category    CleverSoft
 * @package     CleverSocialLogin
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverSocialLogin\Model\Config\Source;

class Registerform implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        return [
            ['value' => 'top', 'label' => __('Top customer registration form')],
            ['value' => 'bottom', 'label' => __('Bottom customer registration form')],
            ['value' => 'left', 'label' => __('Left customer registration form')],
            ['value' => 'right', 'label' => __('Right customer registration form')],
        ];
    }
}