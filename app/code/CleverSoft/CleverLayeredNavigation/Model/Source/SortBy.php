<?php
/**
 * @category    CleverSoft
 * @package     CleverLayeredNavigation
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverLayeredNavigation\Model\Source;

class SortBy implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        return [
            ['value' => 'name', 'label' => __('Name')],
            ['value' => 'position', 'label' => __('Position')],
        ];
    }
}