<?php
/**
 * @category    CleverSoft
 * @package     CleverMegaMenus
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverMegaMenus\Model\Source;
class IsActive implements \Magento\Framework\Data\OptionSourceInterface
{
	public function toOptionArray()
	{
        $options = [['label' => __('Enabled'), 'value' => 1],['label' => __('Disabled'), 'value' => 0]];
		return $options;
	}
}