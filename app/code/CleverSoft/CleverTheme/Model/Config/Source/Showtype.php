<?php
/**
 * @category    CleverSoft
 * @package     CleverTheme
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverTheme\Model\Config\Source;

/**
 * @api
 * @since 100.0.2
 */
class Showtype implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
        	['value' => 1, 'label' => __('Timeout after page loaded')],
        	['value' => 2, 'label' => __('User scroll the page')],
        	['value' => 3, 'label' => __('User click on the page')]
        ];
    }
}
