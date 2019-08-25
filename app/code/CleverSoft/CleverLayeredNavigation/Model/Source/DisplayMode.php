<?php
/**
 * @category    CleverSoft
 * @package     CleverLayeredNavigation
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverLayeredNavigation\Model\Source;


class DisplayMode implements \Magento\Framework\Option\ArrayInterface
{
    const MODE_DEFAULT = 0;
    const MODE_DROPDOWN = 1;
    const MODE_SLIDER  = 2;
    const MODE_VISUAL_LABEL  = 4;
    const MODE_VISUAL  = 5;
    const MODE_FROMTOONLY  = 6;


    const ATTRUBUTE_DEFAULT = 'default';
    const ATTRUBUTE_DECIMAL = 'decimal';
    const ATTRUBUTE_INT = 'int';

    protected $attributeType = self::ATTRUBUTE_DEFAULT;



    public function setAttributeType($attributeType)
    {
        $this->attributeType = $attributeType;
        return $this;
    }

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
            self::MODE_DROPDOWN => __('Dropdown')
        ];
        switch($this->attributeType) {
            case self::ATTRUBUTE_DECIMAL:
                $options[self::MODE_SLIDER] = __('Slider');
//                $options[self::MODE_FROMTOONLY] = __('From-To Only');
                unset($options[self::MODE_DROPDOWN]);
                break;
            case self::ATTRUBUTE_INT:
                $options[self::MODE_VISUAL_LABEL] = __('Visual Swatch & Label');
                $options[self::MODE_VISUAL] = __('Visual Swatch');
                break;
            default:
                break;
        }

        $options[self::MODE_DEFAULT] = __('Text Swatch');

        return $options;
    }
}
