<?php
/**
 * @category    CleverSoft
 * @package     CleverLayeredNavigation
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverLayeredNavigation\Model\Config\Source;

/**
 * Class Position
 *
 * @author Artem Brunevski
 */


use Magento\Framework\Option\ArrayInterface;
use CleverSoft\CleverLayeredNavigation\Model\Page;

class Position  implements ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $optionArray = [];
        $arr = $this->toArray();
        foreach($arr as $value => $label){
            $optionArray[] = [
                'value' => $value,
                'label' => $label
            ];
        }
        return $optionArray;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return [
            Page::POSITION_REPLACE => __('Replace Category\'s Data'),
            Page::POSITION_AFTER => __('After Category\'s Data'),
            Page::POSITION_BEFORE => __('Before Category\'s Data'),
        ];
    }
}
