<?php
/**
 * @category    CleverSoft
 * @package     CleverPageBuilder
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author      ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */
namespace CleverSoft\CleverBuilder\Helper;

class Settings extends \Magento\Framework\App\Helper\AbstractHelper {

    static $_settings;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context
    ) {
        parent::__construct($context);
        self::$_settings = $this->defaultSettings();
    }

    public function getDefaultSettings() {
        return self::$_settings;
    }

    public function get($key) {
        return self::$_settings[$key];
    }

    protected function defaultSettings() {
        $defaults['home_page']         = false;
        $defaults['home_page_default'] = false;
        $defaults['home_template']     = 'home-panels.php';
        $defaults['affiliate_id']      =  false ;
        $defaults['display_teaser']    = true;
        $defaults['display_learn']     = true;
        $defaults['load_on_attach']    = false;

        // The general fields
        $defaults['post_types']             = array( 'page', 'post' );
        $defaults['live_editor_quick_link'] = true;
        $defaults['admin_widget_count']     = false;
        $defaults['parallax_motion']        = '';
        $defaults['sidebars_emulator']      = true;

        // Widgets fields
        $defaults['title_html']          = '<h3 class="widget-title">{{title}}</h3>';
        $defaults['add_widget_class']    = true;
        $defaults['bundled_widgets']     = false ;
        $defaults['recommended_widgets'] = true;

        // The layout fields
        $defaults['responsive']             = true;
        $defaults['tablet_layout']          = false;
        $defaults['legacy_layout']          = 'auto';
        $defaults['tablet_width']           = 1024;
        $defaults['mobile_width']           = 780;
        $defaults['margin_bottom']          = 30;
        $defaults['margin_bottom_last_row'] = false;
        $defaults['margin_sides']           = 30;
        $defaults['full_width_container']   = 'body';

        // Content fields
        $defaults['copy-content'] = true;
        $defaults['copy-styles'] = false;

        return $defaults;
    }
}