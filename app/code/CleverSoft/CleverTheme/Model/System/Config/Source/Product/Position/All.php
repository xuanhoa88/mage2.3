<?php
/**
 * @category    CleverSoft
 * @package     CleverTheme
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverTheme\Model\System\Config\Source\Product\Position;

class All
{
    public function toOptionArray()
    {
        return [
            ['value' => 'primCol_1',            'label' => __('Primary Column, Position 1')],
            ['value' => 'primCol_2',            'label' => __('Primary Column, Position 2')],

            ['value' => 'secCol_1',             'label' => __('Secondary Column, Position 1')],
            ['value' => 'secCol_2',             'label' => __('Secondary Column, Position 2')],

            ['value' => 'lowerPrimCol_1',       'label' => __('Lower Primary Column')],

            ['value' => 'lowerSecCol_1',        'label' => __('Lower Secondary Column')],
        ];
    }
}
