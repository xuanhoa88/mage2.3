<?php
/**
 * @category    CleverSoft
 * @package     CleverLayeredNavigation
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverLayeredNavigation\Model\Source;

class MultipleValuesLogic implements \Magento\Framework\Option\ArrayInterface
{
    const LOGIC_OR = 0;
    const LOGIC_AND = 1;

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];
        foreach ($this->_getOptions() as $optionValue=>$optionLabel) {
            $options[] = ['value'=>$optionValue, 'label'=>$optionLabel];
        }
        return $options;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return $this->_getOptions();
    }

    protected function _getOptions()
    {
        $options = [
            self::LOGIC_OR => __('Show products with ANY value'),
            self::LOGIC_AND => __('Show products with ALL values only')
        ];

        return $options;
    }
}
