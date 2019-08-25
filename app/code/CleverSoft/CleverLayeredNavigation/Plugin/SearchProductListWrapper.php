<?php
/**
 * @category    CleverSoft
 * @package     CleverLayeredNavigation
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverLayeredNavigation\Plugin;


class SearchProductListWrapper
{
    public function afterToHtml(\Magento\CatalogSearch\Block\Result $subject, $result)
    {
        $loader = '<span class="zoo-loading" style="opacity: 1; visibility: visible; top: 10%;"></span>';
        $style  = '
        style="
            background-color: #FFFFFF;
            height: 100%;
            left: 0;
            opacity: 0.5;
            filter: alpha(opacity=50);
            position: absolute;
            top: 0;
            width: 100%;
            z-index: 555;
            display:none;
        "
        ';
        return '<div id="cleversoft-shopby-product-list">'.$result.'<div id="cleversoft-shopby-overlay" '.$style.'>' . $loader . '</div></div>';
    }
}
