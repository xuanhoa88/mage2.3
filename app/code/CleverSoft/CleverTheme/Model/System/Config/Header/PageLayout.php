<?php
/**
 * @category    CleverSoft
 * @package     CleverTheme
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverTheme\Model\System\Config\Header;
class PageLayout implements \Magento\Framework\Option\ArrayInterface {
    public function toOptionArray()
    {

        $types = [
            ['value' => 'wide-layout', 'label' => __('Wide Layout')],
            ['value' => 'boxed-layout', 'label' => __('Boxed')]
        ];

        return $types;
    }
}