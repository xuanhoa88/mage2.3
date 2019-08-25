<?php
/**
 * @category    CleverSoft
 * @package     CleverTheme
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverTheme\Model\Export;
class ExportTypes implements \Magento\Framework\Option\ArrayInterface {
    public function toOptionArray(){

        $types = [
            ['value' => 'pages', 'label' => __('CMS Pages')],
            ['value' => 'blocks', 'label' => __('CMS Blocks')],
            ['value' => 'theme_setting', 'label' => __('Theme Configurations')],
        ];

        return $types;
    }
}