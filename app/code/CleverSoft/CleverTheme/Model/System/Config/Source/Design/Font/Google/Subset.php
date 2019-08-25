<?php
/**
 * @category    CleverSoft
 * @package     CleverTheme
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverTheme\Model\System\Config\Source\Design\Font\Google;

class Subset
{
	public function toOptionArray()
	{
		return [
			['value' => 'cyrillic',			'label' => __('Cyrillic')],
			['value' => 'cyrillic-ext',		'label' => __('Cyrillic Extended')],
			['value' => 'greek',				'label' => __('Greek')],
			['value' => 'greek-ext',			'label' => __('Greek Extended')],
			['value' => 'khmer',				'label' => __('Khmer')],
			['value' => 'latin',				'label' => __('Latin')],
			['value' => 'latin-ext',			'label' => __('Latin Extended')],
			['value' => 'vietnamese',			'label' => __('Vietnamese')],
		];
	}
}