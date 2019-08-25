<?php
/**
 * @category    CleverSoft
 * @package     CleverMegaMenus
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverMegaMenus\Helper\Adminhtml;

class CleverIconHtml {
    protected $_general;
    protected $_icons;
    public function __construct(){
        $this->_general = ['heart-1', 'user-6', 'attachment', 'bag', 'ball', 'battery', 'briefcase', 'car', 'cpu-2', 'cpu-1', 'dress-woman', 'drill-tool', 'feeding-bottle', 'fruit', 'furniture-2', 'furniture-1', 'shoes-woman-2', 'shoes-woman-1', 'horse', 'laptop', 'lipstick', 'iron', 'perfume', 'baby-toy-2', 'baby-toy-1', 'paint-roller', 'shirt', 'shoe-man-2', 'small-diamond', 'tivi', 'smartphone', 'lights', 'microwave', 'wardrobe', 'washing-machine', 'watch-2', 'watch-1', 'slider-3', 'slider-2', 'slider-1', 'cart-15', 'cart-14', 'cart-13', 'cart-12', 'cart-11', 'cart-10', 'cart-9', 'cart-8', 'line-triangle2', 'arrow-left', 'arrow-left-1', 'arrow-left-2', 'arrow-left-3', 'arrow-right', 'arrow-right-1', 'arrow-right-2', 'arrow-right-3', 'plane-1', 'bag-black-fashion-model', 'funnel-o', 'funnel', 'grid-1', 'contract', 'expand', 'cart-7', 'quotes', 'next-arrow-1', 'prev-arrow-1', 'reload', 'truck', 'wallet', 'electric-1', 'electric-2', 'lock', 'share-1', 'check-box', 'clock', 'analytics-laptop', 'code-design', 'competitive-chart', 'computer-monitor-and-cellphone', 'consulting-message', 'creative-process', 'customer-reviews', 'data-visualization', 'document-storage', 'download-arrow', 'download-cloud', 'email-envelope', 'file-sharing', 'finger-touch-screen', 'horizontal-tablet-with-pencil', 'illustration-tool', 'keyboard-and-hands', 'landscape-image', 'layout-squares', 'mobile-app-developing', 'online-purchase', 'online-shopping', 'online-shopping', 'optimization-clock', 'padlock-key', 'pc-monitor', 'place-localizer', 'search-results', 'search-tool', 'settings-tools', 'sharing-symbol', 'site-map', 'smartphone-with-double-arrows', 'tablet-with-double-arrow', 'thin-expand-arrows', 'upload-information', 'upload-to-web', 'volume-off', 'volume-on', 'web-code', 'web-development-1', 'web-development-2', 'web-development', 'web-home', 'web-link', 'web-links', 'website-protection', 'work-team', 'zoom-in-symbol', 'zoom-out-button', 'arrow-1', 'arrow-bold', 'arrow-light', 'arrow-regular', 'cart-1', 'cart-2', 'cart-3', 'cart-4', 'cart-5', 'cart-6', 'chart', 'close', 'compare-1', 'compare-2', 'compare-3', 'compare-4', 'compare-5', 'compare-6', 'compare-7', 'down', 'grid', 'hand', 'layout-1', 'layout', 'light', 'line-triangle', 'list', 'mail-1', 'mail-2', 'mail-3', 'mail-4', 'mail-5', 'map-1', 'map-2', 'map-3', 'map-4', 'map-5', 'menu-1', 'menu-2', 'menu-3', 'menu-4', 'menu-5', 'menu-6', 'minus', 'next', 'phone-1', 'phone-2', 'phone-3', 'phone-4', 'phone-5', 'phone-6', 'picture', 'pin', 'plus', 'prev', 'quickview-1', 'quickview-2', 'quickview-3', 'quickview-4', 'refresh', 'rounded-triangle', 'search-1', 'search-2', 'search-3', 'search-4', 'search-5', 'support', 'tablet', 'triangle', 'up', 'user-1', 'user-2', 'user-3', 'user-4', 'user-5', 'user', 'vector', 'wishlist'];
        $this->_icons = [['name' => __('Web Icons'), 'icons' => $this->_general]];
    }

    /*
     * get template
     */
    public function iconTemplateHelper(){
        $iconH = '';
        foreach ($this->_icons as $key=>$ics) {
            $innerHtml = '';
            foreach ($ics['icons'] as $i=>$ic) {
                $innerHtml .='
                    <div class="col-xs-2"><a href="javascript:void(0)" onclick="Icons.insertCleverIcon(\''.$ic.'\',\'cs-font clever-icon-\');return false;"><span class="cs-font clever-icon-'.$ic.'"></span><span>'.$ic.'</span></div>
                ';
            }
            $iconH .= '
                <div class="menu-icons-wrapper menu-clever-icons-wrapper">
                <p class="icon-label">'.$ics['name'].'</p>
                <div class="row">
                '.$innerHtml.'
                </div>
            </div>
            ';
        }
        return $iconH;
    }
}