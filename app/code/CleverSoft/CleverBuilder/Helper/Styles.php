<?php
/**
 * @category    CleverSoft
 * @package     CleverLayeredNavigation
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author      ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */
namespace CleverSoft\CleverBuilder\Helper;

use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;
use CleverSoft\CleverBuilder\Helper\Settings as Settings;
use CleverSoft\CleverBuilder\Helper\Data as DataHelper;

class Styles extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $_panelSettings;
    protected $_dataHelper;
    protected $_assetRepo;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\View\Element\Context $_context,
        \Magento\Framework\Filesystem $fileSystem,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        Settings $settings,
        DataHelper $data
    ) {
        $this->_storeManager = $storeManager;
        $this->_fileSystem = $fileSystem;
        $this->scopeConfig = $context->getScopeConfig();
        $this->_panelSettings = $settings;
        $this->_dataHelper = $data;
        $this->_urlBuilder = $context->getUrlBuilder();
        $this->_assetRepo = $_context->getAssetRepository();

        $this->_generatedCssFolder = 'css/_config/pagebuilder/';
        $this->_generatedCssDir = $this->_fileSystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath('cleversoft/web') . '/' . $this->_generatedCssFolder;

        parent::__construct($context);
    }
    /**
     * These are general styles that apply to all elements
     *
     * @param $label
     *
     * @return array
     */
    public function get_general_style_fields( $id, $label ) {
        $fields = array();

        // Advanced fields

        $fields['mobile_margin'] = array(
            'name'        => __( 'Margin'),
            'code'        => 'margin', 
            'type'        => 'measurement',
            'attribute_type' => 'style',
            'group'       => 'advanced',
            'description' => 'Margin for mobile',
            'device'        => 'mobile',
            'priority'    => 1,
            'multiple'    => true
        );

        $fields['mobile_padding'] = array(
            'name'        => __( 'Padding'),
            'code'        => 'padding',
            'type'        => 'measurement',
            'attribute_type' => 'style',
            'group'       => 'advanced',
            'description' => 'Padding for mobile',
            'device'        => 'mobile',
            'priority'    => 2,
            'multiple'    => true
        );

        $fields['tablet_margin'] = array(
            'name'        => __( 'Margin'),
            'code'        => 'margin',
            'type'        => 'measurement',
            'attribute_type' => 'style',
            'group'       => 'advanced',
            'description' => 'Margin for tablet',
            'device'        => 'tablet',
            'priority'    => 3,
            'multiple'    => true
        );

        $fields['tablet_padding'] = array(
            'name'        => __( 'Padding'),
            'code'        => 'padding',
            'type'        => 'measurement',
            'attribute_type' => 'style',
            'group'       => 'advanced',
            'description' => 'Padding for tablet',
            'device'        => 'tablet',
            'priority'    => 4,
            'multiple'    => true
        );

        $fields['desktop_margin'] = array(
            'name'        => __( 'Margin'),
            'code'        => 'margin', 
            'type'        => 'measurement',
            'attribute_type' => 'style',
            'group'       => 'advanced',
            'description' => 'Margin for desktop',
            'device'        => 'desktop',
            'priority'    => 5,
            'multiple'    => true
        );

        $fields['desktop_padding'] = array(
            'name'        => __( 'Padding'),
            'code'        => 'padding',
            'type'        => 'measurement',
            'attribute_type' => 'style',
            'group'       => 'advanced',
            'description' => 'Padding for desktop',
            'device'        => 'desktop',
            'priority'    => 6,
            'multiple'    => true
        );

        $fields['z-index'] = array(
            'name'        => sprintf( __( 'Z-Index'), $label ),
            'type'        => 'text',
            'code'        => 'z-index',
            'attribute_type' => 'style',
            'group'       => 'advanced',
            'description' => '',
            'priority'    => 7,
        );

        $fields['id'] = array(
            'name'        => sprintf( __( 'Custom ID'), $label ),
            'type'        => 'text',
            'attribute_type' => 'attribute',
            'code'        => 'id',
            'group'       => 'advanced',
            'description' => sprintf( __( 'A custom ID used for this %s.'), strtolower( $label ) ),
            'priority'    => 9,
        );

        $fields['class'] = array(
            'name'        => sprintf( __( 'Custom Class'), $label ),
            'type'        => 'text',
            'attribute_type' => 'class',
            'code' => 'class',
            'group'       => 'advanced',
            'description' => __( 'A CSS class'),
            'priority'    => 10,
        );

        // Color fields
        $fields['color'] = array(
            'name'        => __( 'Color'),
            'code'        => 'color',
            'attribute_type' => 'style',
            'type'        => 'color',
            'group'       => 'color',
            'mode'        => 'normal',
            'description' => sprintf( __( 'Color of the element.'), strtolower( $label ) ),
            'priority'    => 11,
        );

        $fields['color_hover'] = array(
            'name'        => __( 'Color'),
            'code'        => 'color',
            'attribute_type' => 'style',
            'type'        => 'color',
            'group'       => 'color',
            'mode'        => 'hover',
            'description' => sprintf( __( 'Color on hover of the element.'), strtolower( $label ) ),
            'priority'    => 12,
        );
        // Background fields

        $fields['background'] = array(
            'name'        => __( 'Background Color'),
            'code'        => 'background-color',
            'attribute_type' => 'style',
            'type'        => 'color',
            'group'       => 'background',
            'mode'        => 'normal',
            'description' => sprintf( __( 'Background color of the element.'), strtolower( $label ) ),
            'priority'    => 11,
        );

        $fields['background_hover'] = array(
            'name'        => __( 'Background Color'),
            'code'        => 'background-color',
            'attribute_type' => 'style',
            'type'        => 'color',
            'group'       => 'background',
            'mode'        => 'hover',
            'description' => sprintf( __( 'Background color on hover of the element.'), strtolower( $label ) ),
            'priority'    => 12,
        );

        $fields['background_image_attachment'] = array(
            'name'        => __( 'Background Image'),
            'code'        => 'background-image',
            'attribute_type' => 'style',
            'type'        => 'image',
            'group'       => 'background',
            'mode'        => 'normal',
            'description' => sprintf( __( 'Background image of the element.'), strtolower( $label ) ),
            'priority'    => 13,
        );     

        $fields['background_image_attachment_hover'] = array(
            'name'        => __( 'Background Image'),
            'code'        => 'background-image',
            'attribute_type' => 'style',
            'type'        => 'image',
            'group'       => 'background',
            'mode'        => 'hover',
            'description' => sprintf( __( 'Background image on hover of the element.'), strtolower( $label ) ),
            'priority'    => 14,
        );

        $fields['background_display'] = array(
            'name'        => __( 'Background Image Display'),
            'code'        => 'background-size',
            'attribute_type' => 'style',
            'type'        => 'select',
            'group'       => 'background',
            'mode'        => 'normal',
            'options'     => array(
                'cover'             => __( 'Cover'),
                'tile'              => __( 'Tiled Image'),
                'center'            => __( 'Centered, with original size'),
                'fixed'             => __( 'Fixed'),
                'parallax'          => __( 'Parallax'),
                'parallax-original' => __( 'Parallax (Original Size)'),
            ),
            'description' => __( 'How the background image is displayed.'),
            'priority'    => 15,
        );

        $fields['background_display_hover'] = array(
            'name'        => __( 'Background Image Display'),
            'code'        => 'background-size',
            'attribute_type' => 'style',
            'type'        => 'select',
            'group'       => 'background',
            'mode'        => 'hover',
            'options'     => array(
                'cover'             => __( 'Cover'),
                'tile'              => __( 'Tiled Image'),
                'center'            => __( 'Centered, with original size'),
                'fixed'             => __( 'Fixed'),
                'parallax'          => __( 'Parallax'),
                'parallax-original' => __( 'Parallax (Original Size)'),
            ),
            'description' => __( 'How the background image is displayed on hover.'),
            'priority'    => 16,
        );

        // Border fields
        $fields['border_type'] = array(
            'name'        => __( 'Border Type'),
            'code'        => 'border-style',
            'attribute_type' => 'style',
            'type'        => 'select',
            'group'       => 'border',
            'mode'        => 'normal',
            'options'     => array(
                ''              => __( 'None'),
                'solid'             => __( 'Solid'),
                'double'            => __( 'Double'),
                'dotted'             => __( 'Dotted'),
                'dashed'          => __( 'Dashed'),
                'groove' => __( 'Groove'),
            ),
            'description' => '',
            'priority'    => 17,
        );

        $fields['border_type_hover'] = array(
            'name'        => __( 'Border Type'),
            'code'        => 'border-style',
            'attribute_type' => 'style',
            'type'        => 'select',
            'group'       => 'border',
            'mode'        => 'hover',
            'options'     => array(
                ''              => __( 'None'),
                'solid'             => __( 'Solid'),
                'double'            => __( 'Double'),
                'dotted'             => __( 'Dotted'),
                'dashed'          => __( 'Dashed'),
                'groove' => __( 'Groove'),
            ),
            'description' => 'Border type on hover',
            'priority'    => 18,
        );
        $fields['border_width'] = array(
            'name'        => __( 'Border Width'),
            'code'        => 'border-width',
            'attribute_type' => 'style',
            'type'        => 'measurement',
            'group'       => 'border',
            'description' => '',
            'mode'        => 'normal',
            'priority'    => 19,
            'multiple'    => true
        );
        $fields['border_width_hover'] = array(
            'name'        => __( 'Border Width'),
            'code'        => 'border-width',
            'attribute_type' => 'style',
            'type'        => 'measurement',
            'group'       => 'border',
            'description' => 'Border Width On Hover',
            'mode'        => 'hover',
            'priority'    => 20,
            'multiple'    => true
        );
        $fields['border_color'] = array(
            'name'        => __( 'Border Color'),
            'code'        => 'border-color',
            'attribute_type' => 'style',
            'type'        => 'color',
            'group'       => 'border',
            'mode'        => 'normal',
            'description' => sprintf( __( 'Border color of the element.'), strtolower( $label ) ),
            'priority'    => 21,
        );

        $fields['border_color_hover'] = array(
            'name'        => __( 'Border Color'),
            'code'        => 'border-color',
            'attribute_type' => 'style',
            'type'        => 'color',
            'group'       => 'border',
            'mode'        => 'hover',
            'description' => sprintf( __( 'Border color on hover of the element.'), strtolower( $label ) ),
            'priority'    => 22,
        );
        $fields['border_radius'] = array(
            'name'        => __( 'Border Radius'),
            'code'        => 'border-radius',
            'attribute_type' => 'style',
            'type'        => 'measurement',
            'group'       => 'border',
            'description' => '',
            'mode'        => 'normal',
            'priority'    => 23,
            'multiple'    => true
        );

        $fields['border_radius_hover'] = array(
            'name'        => __( 'Border Radius'),
            'code'        => 'border-radius',
            'attribute_type' => 'style',
            'type'        => 'measurement',
            'group'       => 'border',
            'description' => 'Border radius on hover',
            'mode'        => 'hover',
            'priority'    => 24,
            'multiple'    => true
        );

        // Responsive

        $fields['hide_on_desktop'] = array(
            'name'        => __( 'Hide On Desktop'),
            'type'        => 'toggle',
            'group'       => 'responsive',
            'description' => '',
            'priority'    => 21
        );

        $fields['hide_on_tablet'] = array(
            'name'        => __( 'Hide On Tablet'),
            'type'        => 'toggle',
            'group'       => 'responsive',
            'description' => '',
            'priority'    => 22
        );

        $fields['hide_on_mobile'] = array(
            'name'        => __( 'Hide On Mobile'),
            'type'        => 'toggle',
            'group'       => 'responsive',
            'description' => '',
            'priority'    => 23
        );

        // Custom Css field
        $fields[ 'mobile_css' ] = array(
            'name'        => __( 'CSS Styles'),
            'type'        => 'code',
            'group'       => 'customcss',
            'device'      => 'mobile',
            'description' => __( 'CSS applied when in mobile view.'),
            'priority'    => 24,
        );

        $fields[ 'tablet_css' ] = array(
            'name'        => __( 'CSS Styles'),
            'type'        => 'code',
            'group'       => 'customcss',
            'device'      => 'tablet',
            'description' => __( 'CSS applied when in tablet view.'),
            'priority'    => 25,
        );

        $fields[ 'desktop_css' ] = array(
            'name'        => __( 'CSS Styles'),
            'type'        => 'code',
            'group'       => 'customcss',
            'device'      => 'desktop',
            'description' => __( 'CSS applied when in desktop view.'),
            'priority'    => 26,
        );

        return $fields;
    }
    /*
     * return object of Data Helper class
     */
     public function getDataHelper() {
         return $this->_dataHelper;
     }

     public function cell_style_fields( $fields ) {
        // Add the general fields
        $fields = $this->_dataHelper->parse_args( $fields, self::get_general_style_fields( 'cell', __( 'Cell') ) );
        return $fields;
    }

    /**
     * Sanitize style fields.
     *
     * @param $section
     * @param $styles
     *
     * @return Sanitized styles
     */
    public function sanitize_style_fields( $section, $styles )
    {
        // Use the filter to get the fields for this section.
        if (empty($fields_cache[$section])) {
            // This filter doesn't pass in the arguments $post_id and $args
            // Plugins looking to extend fields, should always add their fields if these are empty
            $fields_cache[$section] = array();
            $function = $section . '_style_fields';
            $fields_cache[$section] = $this->$function($fields_cache[$section]);
        }
        $fields = $fields_cache[$section];

        $return = array();
        foreach ($fields as $k => $field) {
            // Skip this if no field type is set
            if (empty($field['type'])) {
                continue;
            }

            // Handle the special case of a checkbox
            if ($field['type'] == 'checkbox') {
                $return[$k] = !empty($styles[$k]) ? true : '';
                continue;
            }

            // Ignore this if we don't even have a value for the style
            if (!isset($styles[$k]) || $styles[$k] == '') {
                continue;
            }

            switch ($field['type']) {
                case 'color' :
                    $color = $styles[$k];
                    if (preg_match('|^#([A-Fa-f0-9]{3,8})$|', $color)) {
                        $return[$k] = $color;
                    } else {
                        $return[$k] = '';
                    }
                    break;
                case 'image' :
                    $return[$k] = !empty($styles[$k]) ? ($styles[$k]) : false;
                    break;
                case 'url' :
                    $return[$k] = ($styles[$k]);
                    break;
                case 'measurement' :
                    $measurements = array_map('preg_quote', $this->_dataHelper->measurements_list);
                    if (!empty($field['multiple'])) {
                        if (preg_match_all('/(?:(-?[0-9\.,]+).*?(' . implode('|', $measurements) . ')+)/', $styles[$k], $match)) {
                            $return[$k] = $styles[$k];
                        } else {
                            $return[$k] = '';
                        }
                    } else {
                        if (preg_match('/([-?0-9\.,]+).*?(' . implode('|', $measurements) . ')/', $styles[$k], $match)) {
                            $return[$k] = $match[1] . $match[2];
                        } else {
                            $return[$k] = '';
                        }
                    }
                    break;
                case 'select' :
                    if (!empty($styles[$k]) && in_array($styles[$k], array_keys($field['options']))) {
                        $return[$k] = $styles[$k];
                    }
                    break;
                default:
                    // Just pass the value through.
                    $return[$k] = $styles[$k];
                    break;

            }
        }

        return $return;
    }

    /**
     * All the row styling fields
     *
     * @param $fields
     *
     * @return array
     */
    public function row_style_fields( $fields ) {
        // Add the general fields
        $fields = $this->_dataHelper->parse_args( $fields, self::get_general_style_fields( 'row', __( 'Row') ) );

        $fields['gutter'] = array(
            'name'        => __( 'Gutter (px)'),
            'type'        => 'text',
            'group'       => 'rowstyle',
            'description' => sprintf( __( 'Amount of space between cells. Default is %spx.'), $this->_panelSettings->get( 'margin_sides' ) ),
            'priority'    => 6,
        );

        $fields['row_stretch'] = array(
            'name'     => __( 'Row Layout'),
            'type'     => 'select',
            'group'    => 'rowstyle',
            'options'  => array(
                ''               => __( 'Standard'),
                'full'           => __( 'Full Width')
            ),
            'priority' => 10,
        );

        // $fields['collapse_behaviour'] = array(
        //     'name'     => __( 'Collapse Behaviour'),
        //     'type'     => 'select',
        //     'group'    => 'rowstyle',
        //     'options'  => array(
        //         ''               => __( 'Standard'),
        //         'no_collapse'    => __( 'No Collapse'),
        //     ),
        //     'priority' => 15,
        // );

        // $fields['collapse_order'] = array(
        //     'name'     => __( 'Collapse Order'),
        //     'type'     => 'select',
        //     'group'    => 'rowstyle',
        //     'options'  => array(
        //         ''          => __( 'Default'),
        //         'left-top'  => __( 'Left on Top'),
        //         'right-top' => __( 'Right on Top'),
        //     ),
        //     'priority' => 16,
        // );

        if ( $this->_panelSettings->get( 'legacy_layout' ) != 'always'  ) {
            $fields['cell_alignment'] = array(
                'name'     => __( 'Cell Vertical Alignment'),
                'type'     => 'select',
                'group'    => 'rowstyle',
                'options'  => array(
                    'flex-start' => __( 'Top'),
                    'center'     => __( 'Center'),
                    'flex-end'   => __( 'Bottom'),
                    'stretch'    => __( 'Stretch'),
                ),
                'priority' => 17,
            );
        }

        return $fields;
    }

    /**
     * @param $fields
     *
     * @return array
     */
    public function widget_style_fields( $fields ) {

        // Add the general fields
        $fields = $this->_dataHelper->parse_args( $fields, self::get_general_style_fields( 'widget', __( 'Widget') ) );

        // How lets add the design fields

        $fields['font_color'] = array(
            'name'        => __( 'Font Color'),
            'type'        => 'color',
            'group'       => 'design',
            'description' => __( 'Color of text inside this widget.'),
            'priority'    => 15,
        );

        $fields['link_color'] = array(
            'name'        => __( 'Links Color'),
            'type'        => 'color',
            'group'       => 'design',
            'description' => __( 'Color of links inside this widget.'),
            'priority'    => 16,
        );

        return $fields;
    }

    /**
     * Style attributes that apply to rows, cells and widgets
     *
     * @param $attributes
     * @param $style
     *
     * @return array $attributes
     */
    public function general_style_attributes( $attributes, $style ){
        if ( ! empty( $style['class'] ) ) {
            if( ! is_array( $style['class'] ) ) {
                $style['class'] = explode( ' ', $style[ 'class' ] );
            }
            $attributes['class'] = array_merge( $attributes['class'], $style['class'] );
        }

        if ( ! empty( $style['background_display'] ) && ! empty( $style['background_image_attachment'] ) ) {

            $url = self::get_attachment_image_src( $style['background_image_attachment'], 'full' );

            if (
                ! empty( $url ) &&
                ( $style['background_display'] == 'parallax' || $style['background_display'] == 'parallax-original' )
            ) {
//                wp_enqueue_script( 'cleversoft-parallax' );
                $parallax_args                          = array(
                    'backgroundUrl'    => $url[0],
                    'backgroundSize'   => array( $url[1], $url[2] ),
                    'backgroundSizing' => $style['background_display'] == 'parallax-original' ? 'original' : 'scaled',
                    'limitMotion'      => $this->_panelSettings->get( 'parallax_motion' ) ? floatval( $this->_panelSettings->get( 'parallax_motion' ) ) : 'auto',
                );
                $attributes['data-cleversoft-parallax'] = json_encode( $parallax_args );
            }
        }

        if ( ! empty( $style['id'] ) ) {
            $attributes['id'] = $this->_dataHelper->sanitize_html_class( $style['id'] );
        }

        return $attributes;
    }

    public function row_style_attributes( $attributes, $style ) {
        if ( ! empty( $style['row_stretch'] ) ) {
            $attributes['class'][]           = 'cleversoft-panels-stretch';
            $attributes['data-stretch-type'] = $style['row_stretch'];
        }

        return $attributes;
    }

    public function vantage_row_style_attributes( $attributes, $style ) {
        if ( isset( $style['class'] ) && $style['class'] == 'wide-grey' && ! empty( $attributes['style'] ) ) {
            $attributes['style'] = preg_replace( '/padding-left: 1000px; padding-right: 1000px;/', '', $attributes['style'] );
        }

        return $attributes;
    }

    static function filter_row_gutter( $gutter, $grid ) {
        if ( ! empty( $grid['style']['gutter'] ) ) {
            $gutter = $grid['style']['gutter'];
        }

        return $gutter;
    }

    public static function get_attachment_image_src( $image, $size = 'full' ){
        if( empty( $image ) ) {
            return false;
        }
        else if( is_numeric( $image ) ) {
            return wp_get_attachment_image_src( $image, $size );
        }
        else if( is_string( $image ) ) {
            preg_match( '/(.*?)\#([0-9]+)x([0-9]+)$/', $image, $matches );
            return ! empty( $matches ) ? $matches : false;
        }
    }

    /**
     * Get automatically generated CSS directory
     *
     * @return string
     */
    public function getGeneratedCssDir()
    {
        return $this->_generatedCssDir;
    }

    /**
     * Get file path: CSS design
     *
     * @return string
     */
    public function getWidgetStyleFile()
    {
        $meidaUrl = $this->_storeManager->getStore()->getBaseUrl(
            \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
        );
        $url = $meidaUrl . 'cleversoft/web/css/_config/pagebuilder/clever_visualpagebuilder_style_' . $this->_dataHelper->getPageId() . '.css';
        return $this->removeProtocol($url);
    }
    public function getRootStyleFile()
    {
        
        return $this->getGeneratedCssDir().'clever_visualpagebuilder_style_' . $this->_dataHelper->getPageId() . '.css';
    }
    protected function removeProtocol($url){
        $remove = array("http:","https:");
        return str_replace($remove,"",$url);
    }

    public function getViewFileUrl($fileId, array $params = []) {
        try {
            $params = array_merge(['_secure' => $this->_getRequest()->isSecure()], $params);
            return $this->_assetRepo->getUrlWithParams($fileId, $params);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->_logger->critical($e);
            return $this->_getNotFoundUrl();
        }
    }
}

