<?php
/**
 * @category    CleverSoft
 * @package     CleverLayeredNavigation
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverLayeredNavigation\Model\Source;


class FilterPlacedBlock implements \Magento\Framework\Option\ArrayInterface
{
    const POSITION_SIDEBAR = 1;
    const POSITION_TOP = 2;
    const POSITION_BOTH = 3;

    public function toOptionArray()
    {
        return [
            [
                'value' => self::POSITION_SIDEBAR,
                'label' => __('Sidebar')
            ],
            [
                'value' => self::POSITION_TOP,
                'label' => __('Top')
            ],
            /*[
                'value' => self::POSITION_BOTH,
                'label' => __('Both')
            ]*/
        ];
    }
}
