<?php
/**
 * @category    CleverSoft
 * @package     CleverTheme
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverTheme\Model\System\Config\Source\Mainmenu;

class Menuleftanimation implements \Magento\Framework\Option\ArrayInterface{

    public function toOptionArray()
    {
        $types = [
            ['value' => 'show', 'label' => __('Show/Hide')],
            ['value' => 'slide', 'label' => __('Slide')],
            ['value' => 'slideWidth', 'label' => __('Slide Width')],
            ['value' => 'fade', 'label' => __('Fade')]
        ];

        return $types;
    }

}
