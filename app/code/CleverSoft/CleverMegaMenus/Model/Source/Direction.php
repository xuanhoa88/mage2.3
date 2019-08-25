<?php
/**
 * @category    CleverSoft
 * @package     CleverMegaMenus
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverMegaMenus\Model\Source;
class Direction implements \Magento\Framework\Data\OptionSourceInterface
{
	public function toOptionArray()
	{
        $options = [['label' => __('Horizontal'), 'value' => 0],['label' => __('Vertical'), 'value' => 1]];
		return $options;
	}
}