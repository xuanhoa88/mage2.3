<?php
/**
 * @category    CleverSoft
 * @package     CleverBuilder
 * @copyright   Copyright © 2018 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverBuilder\Block\Builder\Element\Render\Products;

use Magento\Catalog\Model\Product\Attribute\Repository;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\Element\Template;
use CleverSoft\CleverBuilder\Block\Builder\Element\Render\Products;

class CarouselProducts extends Products
{
    /**
     * get collection of feature products
     * @return mixed
     */
    public function updateData() {
        $this->setData(array(
            'id'            => $this->getConfig('id'),
            'row'           => $this->getConfig('row'),
            'column_grid'   => $this->getConfig('column_grid'),
            'column'        => $this->getConfig('column'),
            'show_label'    => $this->getConfig('show_label'),
            'limit'         => $this->getData('limit'),
            'tab'           => $this->getData('tab'),
            'widget_title'  => $this->getConfig('widget_title'),
            'widget_sub_title'  => $this->getConfig('widget_sub_title'),
            'classes'       => $this->getConfig('classes'),
            'carousel'      => $this->getConfig('carousel'),
            'carouselv'      => $this->getConfig('carouselv'),
            'layout_product'        => $this->getData('layout_product'),
            'one_image_height'  => $this->getData('one_image_height'),
            'scale_product'  => $this->getData('scale_product'),
            'scale_product_container'  => $this->getData('scale_product_container'),
            'enable_border_boxshadow'  => $this->getData('enable_border_boxshadow'),
            'alt_image'  => $this->getData('alt_image'),
            'mode'          => $this->getData('mode'),
            'category_ids'  => $this->getData('category_ids'),
            'animation'     => $this->getConfig('animation'),
            'parallax'      => $this->getConfig('parallax'),
            'countdown'     => $this->getConfig('countdown'),
            'enable_countdown'     => $this->getData('countdown') ? $this->getData('countdown') : 0,
            'countdown_24h'     => $this->getConfig('countdown_24h') ? $this->getConfig('countdown_24h') : 0,
            'link_page_sales'     => $this->getConfig('link_page_sales') ? $this->getConfig('link_page_sales') : '#',
            'countdown_label'     => $this->getConfig('countdown_label') ? $this->getConfig('countdown_label') : 'Hurry up!! This offer expires in :',
            'countdown_inner'     => $this->getConfig('countdown_inner') ? $this->getConfig('countdown_inner') : 0,
            'countdown_position'     => $this->getConfig('countdown_position') ? $this->getConfig('countdown_position') : 'over_feature_img',
            'enable_progress_bar'     => $this->getConfig('enable_progress_bar') ? $this->getConfig('enable_progress_bar') : 1 ,
            'kenburns'     => $this->getConfig('kenburns'),
            'vautoplay'    => $this->getData('vautoplay') == '1' ? 'true' : 'false',
            'vautoplaytimeout'    => $this->getData('vautoplaytimeout') ? (int)$this->getData('vautoplaytimeout') : 1,
            'vautoplayhoverpause'    => $this->getData('vautoplayhoverpause') == '1' ? 'true' : 'false',
            'vnavigation_prev'    => $this->getData('vnavigation_prev') ? $this->getData('vnavigation_prev') : '&lt;',
            'vnavigation_next'    => $this->getData('vnavigation_next') ? $this->getData('vnavigation_next') : '&gt;',
            'venable_bullet'    => $this->getData('venable_bullet') == '1' ? 'true' : 'false',
            'lazyload'    => $this->getData('lazyload') == '1' ? true : false,
            'aspect_ratio'    => $this->getData('aspect_ratio') == '1' ? true : false,
            'image_width'    => $this->getConfig('image_width'),
            'widget_tab'    => $this->getConfig('widget_tab'),
            'enable_fullwidth'    => $this->getConfig('enable_fullwidth') == '1' ? true : false,
            'image_height'    => $this->getConfig('image_height'),
            'product_grid_style'    => $this->getData('product_grid_style')? $this->getData('product_grid_style') : 'product_grid_style_1',
            'display_rating'    => $this->getData('display_rating'),
            'display_addtocart'    => $this->getData('display_addtocart'),
            'display_quickview'    => $this->getData('display_quickview'),
            'display_addtowishlist'    => $this->getData('display_addtowishlist'),
            'display_addtocompare'    => $this->getData('display_addtocompare'),
            'display_swatch_attributes'    => $this->getData('display_swatch_attributes'),
            'display_productname'    => $this->getData('display_productname'),
            'display_price'    => $this->getData('display_price'),
            'height_image'    => $this->getData('height_image') ? $this->getData('height_image') : 150,
        ));
        return;
    }

    public function getConfig($name, $param=null){
        /* @var $helper Mage_Core_Helper_Data */
        $_objectManager = \Magento\Framework\App\ObjectManager::getInstance(); //instance of\Magento\Framework\App\ObjectManager
        $storeManager = $_objectManager->get('Magento\Store\Model\StoreManagerInterface');
        $currentStore = $storeManager->getStore();
        $mediaUrl = $currentStore->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        switch ($name){
            case 'countdown':
                return $this->_jsonEncoder->encode(array(
                    'enable'            => (bool) $this->getData('countdown'),
                    'yearText'          => __('years'),
                    'monthText'         => __('months'),
                    'weekText'          => __('weeks'),
                    'dayText'           => __('days'),
                    'hourText'          => __('hours'),
                    'minText'           => __('mins'),
                    'secText'           => __('secs'),
                    'yearSingularText'  => __('year'),
                    'monthSingularText' => __('month'),
                    'weekSingularText'  => __('week'),
                    'daySingularText'   => __('day'),
                    'hourSingularText'  => __('hour'),
                    'minSingularText'   => __('min'),
                    'secSingularText'   => __('sec')
                ));
                break;
            case 'kenburns':
                return $this->_jsonEncoder->encode(array(
                    'enable'    => $this->getData('background') == 'kenburns',
                    //'images'    => $this->_getKenburnsImages(),
                    'overlay'   => $this->getData('background_overlay') ? $this->getData('background_overlay') : 'none',
                    'opacity'   => $this->getData('background_overlay_o') ? $this->getData('background_overlay_o') : 0,
                    'engineSrc' => $this->getViewFileUrl('CleverSoft_CleverBuilder::js/product/kenburns.js'),
                ));
                break;
            case 'parallax':
                return $this->_jsonEncoder->encode(array(
                    'enable'    => $this->getData('background') == 'parallax',
                    'type'      => $this->getData('parallax_type'),
                    'overlay'   => $this->getData('background_overlay') ? $this->getData('background_overlay') : 'none',
                    'opacity'   => $this->getData('background_overlay_o') ? $this->getData('background_overlay_o') : 0,
                    'video'     => array(
                        'src'       => $this->getData('parallax_video_src'),
                        'volume'    => (bool) $this->getData('parallax_video_volume'),
                    ),
                    'image'     => array(
                        'src'       => $this->getData('parallax_image_src') ?
                            (
                                $mediaUrl.$this->getData('parallax_image_src')
                            ):
                            null,
                        'fit'       => $this->getData('parallax_image_fit'),
                        'repeat'    => $this->getData('parallax_image_repeat')
                    ),
                    'file'      => array(
                        'poster'    => $this->getData('parallax_video_poster') ?
                            (
                            strpos($this->getData('parallax_video_poster'), 'http') === 0 ?
                                $this->getData('parallax_video_poster') :
                                $this->getViewFileUrl($this->getData('parallax_video_poster'))
                            ):
                            null,
                        'mp4'       => $this->getData('parallax_video_mp4') ?
                            (
                            strpos($this->getData('parallax_video_mp4'), 'http') === 0 ?
                                $this->getData('parallax_video_mp4') :
                                $this->getViewFileUrl($this->getData('parallax_video_mp4'))
                            ):
                            null,
                        'webm'      => $this->getData('parallax_video_webm') ?
                            (
                            strpos($this->getData('parallax_video_webm'), 'http') === 0 ?
                                $this->getData('parallax_video_webm') :
                                $this->getViewFileUrl($this->getData('parallax_video_webm'))
                            ):
                            null,
                        'volume'    => (bool) $this->getData('parallax_video_volume')
                    )
                ));
                break;
            case 'carousel':
                $navigation_prev = "<a class='flex-prev' href='#'>Prev</a>";
                $navigation_next = "<a class='flex-next' href='#'>Next</a>";
                return $this->_jsonEncoder->encode(array(
                    'enable'        => 1,
                    'autoplay'      => ($this->getData('autoplay') == 1 ? true : false),
                    'margin'        => (int)$this->getData('margin'),
                    'autoplayTimeout'  => is_numeric($this->getData('autoplaytimeout')) ? (int) $this->getData('autoplaytimeout') : false,
                    'autoplayHoverPause'      => ($this->getData('autoplayhoverpause') == 1 ? true : false),
                    'lazyLoad'      => $this->getData('lazyload') == '1' ? 'true' : 'false',
                    'lazyEffect'    => false,
                    'responsiveClass'  =>  true,
                    'addClassActive'=> true,
                    'dots'      => ($this->getData('enable_bullet') == 1 ? true : false),
                    'rewind' => true,
                    'loop' 	=> ($this->getData('enable_loop') == 1 ? true : false),
                    'navText'=> array($navigation_prev, $navigation_next),
                    'responsive' => [
                        '0' => [
                            'items'=> 2,
                        ],
                        '374' => [
                            'items'=> 2,
                        ],
                        '480' => [
                            'items'=> $this->getData('col_480') ? $this->getData('col_480') : 2,
                        ],
                        '768' => [
                            'items'=>$this->getData('col_768') ? $this->getData('col_768') : 2,
                        ],
                        '992' => [
                            'items'=> $this->getData('col_992') ? $this->getData('col_992') : 3,
                            'nav'    => (bool) $this->getData('navigation'),
                        ],
                        '1200' => [
                            'items'=> $this->getData('column') ? $this->getData('column') : 4,
                            'nav'    => (bool) $this->getData('navigation'),
                        ],
                    ]
                ));
                break;
            case 'carouselv':
                return $this->_jsonEncoder->encode(array(
                    'enable'        => ($this->getData('layout_product') ==  'carousel-vertical' ? 1 : 0),
                    'startSlide'      => 0,
                    'minSlides'      => $this->getData('vertical_items') ? $this->getData('vertical_items') : 3,
                    'maxSlides'  => $this->getData('vertical_items') ? $this->getData('vertical_items') : 3,
                    'mode'      => 'vertical',
                    'imgWidth'      => $this->getData('image_width') ,
                    'auto'      => $this->getData('vautoplay'),
                    'pause'    => $this->getData('vautoplaytimeout'),
                    'autoHover'  =>  $this->getData('vautoplayhoverpause'),
                    'nextText'=> "<span class='cs-font clever-icon-next'></span>",
                    'prevText'      => "<span class='cs-font clever-icon-prev'></span>",
                    'pager'    => $this->getData('venable_bullet'),
                    'lazyload' => $this->getData('lazyload')
                ));
                break;
            case 'animation':
                return $this->_jsonEncoder->encode(array(
                    'enable'        => (bool) $this->getData('animate'),
                    'animationName' => $this->getData('animation'),
                    'animationDelay'=> is_numeric($this->getData('animation_delay')) ? (int) $this->getData('animation_delay') : 300,
                    'itemSelector'  => $this->getData('animate_item_selector'),
                ));
                break;
            case 'widget_title':
                return $this->escapeHtml($this->getData('widget_title'));
                break;
            case 'column_grid':
                return is_numeric($this->getData('column_grid')) ? (int) $this->getData('column_grid') : 4;
                break;
            case 'id':
                return $this->_mathRandom->getUniqueHash(is_string($param) ? $param : 'widget-');
                break;
            case 'row':
                return is_numeric($this->getData('row')) ? (int) $this->getData('row') : 1;
                break;
            case 'column':
                return is_numeric($this->getData('column')) ? (int) $this->getData('column') : 4;
                break;
            case 'image_width':
                return $this->getData('image_width') ? $this->getData('image_width') : '220';
                break;
            case 'image_height':
                return $this->getData('image_height') ? $this->getData('image_height') : '';
                break;
            case 'limit':
                return is_numeric($this->getData('limit')) ? (int) $this->getData('limit') : 1;
                break;
            case 'classes':
                return $this->getData('classes') . ($this->getData('animate') ? ' ' . $this->getData('animation') : '');
                break;
            default:
                return $this->getData($name);
        }
    }
    
    /**
     * @return string
     */
    public function getColorSwatchDetailsHtml($product)
    {
        if($product->getTypeId() != 'configurable') return '';
        $block = $this->getLayout()->createBlock('CleverSoft\CleverBuilder\Block\Product\Renderer\ConfigurableWithSwap');
        $block->setProduct($product);
        return $block->toHtml();
    }
}
