<?php
/**
 * @category    CleverSoft
 * @package     CleverLayeredNavigation
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverLayeredNavigation\Model\Source;

class Subcatview implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        return [
            ['value' => '1', 'label' => __('Folding')],
            ['value' => '2', 'label' => __('Fly-out')],
        ];
    }
}