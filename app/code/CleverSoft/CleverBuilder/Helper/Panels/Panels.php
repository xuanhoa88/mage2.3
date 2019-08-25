<?php
/**
 * @category    CleverSoft
 * @package     CleverPageBuilder
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author      ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */
namespace CleverSoft\CleverBuilder\Helper\Panels;
use CleverSoft\CleverBuilder\Helper\Styles;
use CleverSoft\CleverBuilder\Helper\Panels\PanelsRenderer;
use CleverSoft\CleverBuilder\Helper\Panels\PanelsRendererLegacy;
use Magento\Framework\View\Page\Config;
use Magento\Framework\ObjectManagerInterface;

class Panels extends \Magento\Framework\App\Helper\AbstractHelper {
    /*
     *
     */
    protected $_dataHelper;
    /*
     *
     */
    protected $_panelsRenderer;
    /*
     *
     */
    protected $_panelsRendererLegacy;
    /*
     *
     */
    protected $_renderered;
    /*
     *
     */
    protected $_config;
    /*
     *
     */
    protected $_objectManager;
    /*
     *
     */
    protected $_stylesHelper;


    public function __construct(\Magento\Framework\App\Helper\Context $context, Styles $styles, PanelsRenderer $panelsRenderer, PanelsRendererLegacy $panelsRendererLegacy, Config $config , ObjectManagerInterface $objectManagerInterface) {
        $this->_stylesHelper = $styles;
        $this->_dataHelper = $styles->getDataHelper();
        $this->_panelsRenderer = $panelsRenderer;
        $this->_panelsRendererLegacy = $panelsRendererLegacy;;
        $this->_renderered = $this->renderer();
        $this->_config = $config;
        $this->_objectManager = $objectManagerInterface;
        parent::__construct($context);
//
        $this->bodyClass();
    }

    /**
     * Get an instance of the renderer
     *
     * @return CleverSoft_Panels_Renderer
     */
    public function renderer(){
        if( empty( $this->_renderered ) ) {
            switch( $this->_dataHelper->getPanelSettings()->get( 'legacy_layout' ) ) {
                case 'always':
                    $renderer = $this->_panelsRendererLegacy;
                    break;

                case 'never':
                    $renderer = $this->_panelsRenderer;
                    break;

                default :
                    $renderer = self::is_legacy_browser() ?
                        $this->_panelsRendererLegacy :
                        $this->_panelsRenderer;
                    break;
            }
            return $renderer;
        }
        return $this->_renderered;
    }

    /**
     * Sanitize the style fields in panels_data
     *
     * @param $panels_data array
     *
     * @return mixed
     */
    public function sanitize_all( $panels_data ) {
        if ( ! empty( $panels_data['widgets'] ) ) {
            // Sanitize the widgets
            for ( $i = 0; $i < count( $panels_data['widgets'] ); $i ++ ) {
                if ( empty( $panels_data['widgets'][ $i ]['panels_info']['style'] ) ) {
                    continue;
                }
                $panels_data['widgets'][ $i ]['panels_info']['style'] = $this->_stylesHelper->sanitize_style_fields( 'widget', $panels_data['widgets'][ $i ]['panels_info']['style'] );
            }
        }

        if ( ! empty( $panels_data['grids'] ) ) {
            // The rows
            for ( $i = 0; $i < count( $panels_data['grids'] ); $i ++ ) {
                if ( empty( $panels_data['grids'][ $i ]['style'] ) ) {
                    continue;
                }
                $panels_data['grids'][ $i ]['style'] = $this->_stylesHelper->sanitize_style_fields( 'row', $panels_data['grids'][ $i ]['style'] );
            }
        }

        if ( ! empty( $panels_data['grid_cells'] ) ) {
            // And finally, the cells
            for ( $i = 0; $i < count( $panels_data['grid_cells'] ); $i ++ ) {
                if ( empty( $panels_data['grid_cells'][ $i ]['style'] ) ) {
                    continue;
                }
                $panels_data['grid_cells'][ $i ]['style'] = $this->_stylesHelper->sanitize_style_fields( 'cell', $panels_data['grid_cells'][ $i ]['style'] );
            }
        }

        return $panels_data;
    }

    public static function is_legacy_browser(){
        $agent = ! empty( $_SERVER['HTTP_USER_AGENT'] ) ? $_SERVER['HTTP_USER_AGENT'] : '';
        if( empty( $agent ) ) return false;

        return
            // IE lte 10
            ( preg_match('/MSIE\s(?P<v>\d+)/i', $agent, $B) && $B['v'] <= 10 ) ||
            // Chrome lte 25
            ( preg_match('/Chrome\/(?P<v>\d+)/i', $agent, $B) && $B['v'] <= 25 ) ||
            // Firefox lte 21
            ( preg_match('/Firefox\/(?P<v>\d+)/i', $agent, $B) && $B['v'] <= 21 ) ||
            // Safari lte 7
            ( preg_match('/Version\/(?P<v>\d+).*?Safari\/\d+/i', $agent, $B) && $B['v'] <= 6 );
    }

    /**
     * Check if we're in the Live Editor in the frontend.
     *
     * @return bool
     */
    public function is_live_editor(){
        $is = $this->_getRequest()->getParams('cleversoft_panels_live_editor');
        return ! empty( $is );
    }

    public function previewUrl() {
        return $this->_urlBuilder->getUrl('*/*/*', ['_current' => true, '_use_rewrite' => true, '_query' => array('previewUrl'=>true,'cleversoft_panels_live_editor'=>true)]);
    }

    /*
     * return object CleverSoft\CleverBuilder\Helper\Data
     */
    public function getDataHelper(){
        return $this->_dataHelper;
    }

    /**
     * Add all the necessary body classes.
     *
     * @param $classes
     *
     * @return array
     */
    public function bodyClass(  ) {
        if( self::is_live_editor() ) $this->_config->addBodyClass('cleversoft-panels-live-editor');
        $this->_config->addBodyClass('cleversoft-panels cleversoft-panels-before-js');
    }



    public function front_css_url(){
        return $this->_renderered->front_css_url();
    }
    /**
     * Script that removes the cleversoft-panels-before-js class from the body.
     */
    public function strip_before_js(){
        ?><script>document.body.className = document.body.className.replace("cleversoft-panels-before-js","");</script><?php
    }
    /*
     *
     */
    public function post_metadata( $value, $post_id, $meta_key ) {
        $post = $this->_getRequest()->getParams();
        if(empty($post) || !isset($post['live_editor_post_ID'])) return $value;
        if (
            $meta_key == 'panels_data' &&
            $post['live_editor_post_ID'] == $post_id
        ) {
            $data = json_decode( ( $post['live_editor_panels_data'] ), true );

            if (
                ! empty( $data['widgets'] )
            ) {
                $data['widgets'] = $this->_dataHelper->process_raw_widgets( $data['widgets'], false, false );
            }

            $value = $data ;
        }

        return $value;
    }
}