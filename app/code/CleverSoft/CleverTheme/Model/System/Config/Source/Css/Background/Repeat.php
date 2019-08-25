<?php
/**
 * @category    CleverSoft
 * @package     CleverTheme
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverTheme\Model\System\Config\Source\Css\Background;
class Repeat implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
		return array(
			array('value' => 'no-repeat',	'label' => __('no-repeat')),
            array('value' => 'repeat',		'label' => __('repeat')),
            array('value' => 'repeat-x',	'label' => __('repeat-x')),
			array('value' => 'repeat-y',	'label' => __('repeat-y'))
        );
    }
}