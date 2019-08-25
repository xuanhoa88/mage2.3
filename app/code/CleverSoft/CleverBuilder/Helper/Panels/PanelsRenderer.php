<?php
/**
 * @category    CleverSoft
 * @package     CleverPageBuilder
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author      ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */
namespace CleverSoft\CleverBuilder\Helper\Panels;
use CleverSoft\CleverBuilder\Helper\Data as Data;
use CleverSoft\CleverBuilder\Helper\CssBuilder as CssBuilder;
use CleverSoft\CleverBuilder\Helper\Styles as Styles;
use CleverSoft\CleverBuilder\Helper\Settings as Settings;

class PanelsRenderer extends \Magento\Framework\App\Helper\AbstractHelper {
    private $inline_css;
    protected $_dataHelper;
    protected $_cssBuilder;
    protected $_stylesHelper;
    protected $_settingsHelper;
    protected $_assetRepo;
    protected $_objectManager;
    /*
     *
     */
    protected $_abstractBlock;

    public function __construct(\Magento\Framework\App\Helper\Context $context, \Magento\Store\Model\StoreManagerInterface $storeManager, Data $data , CssBuilder $cssBuilder, Styles $styles, Settings $settings, \Magento\Framework\View\Element\Context $_context, \Magento\Framework\ObjectManagerInterface $objectManagerInterface, \Magento\Framework\Registry $registry) {
        parent::__construct($context);
        $this->inline_css = null;
        $this->_assetRepo = $_context->getAssetRepository();
        $this->_stylesHelper = $styles;
        $this->_settingsHelper = $settings;
        $this->_cssBuilder = $cssBuilder;
        $this->_objectManager = $objectManagerInterface;
        $this->_dataHelper = $data;
        $this->_registry = $registry;
        $this->_storeManager = $storeManager;
    }
    /**
     * Add CSS that needs to go inline.
     *
     * @param $post_id
     * @param $css
     */
    public function add_inline_css( $post_id, $css ) {
        $this->inline_css[ $post_id ] = $css;
    }

    /**
     * Process panels data to make sure everything is properly formatted
     *
     * @param array $panels_data
     *
     * @return array
     */
    public function processPanelsData( $panels_data ) {

        // Process all widgets to make sure that panels_info is properly represented
        if ( ! empty( $panels_data['widgets'] ) && is_array( $panels_data['widgets'] ) ) {

            $last_gi = 0;
            $last_ci = 0;
            $last_wi = 0;

            foreach ( $panels_data['widgets'] as &$widget ) {
                // Transfer legacy content
                if ( empty( $widget['panels_info'] ) && ! empty( $widget['info'] ) ) {
                    $widget['panels_info'] = $widget['info'];
                    unset( $widget['info'] );
                }

                // Filter the widgets to add indexes
                if ( $widget['panels_info']['grid'] != $last_gi ) {
                    $last_gi = $widget['panels_info']['grid'];
                    $last_ci = $widget['panels_info']['cell'];
                    $last_wi = 0;
                } elseif ( $widget['panels_info']['cell'] != $last_ci ) {
                    $last_ci = $widget['panels_info']['cell'];
                    $last_wi = 0;
                }
                $widget['panels_info']['cell_index'] = $last_wi ++;
            }

            foreach ( $panels_data['grids'] as &$grid ) {
                if ( ! empty( $grid['style'] ) && is_string( $grid['style'] ) ) {
                    $grid['style'] = array();
                }
            }
        }

        return $panels_data;
    }

    /**
     * Render the panels.
     *
     * @param int|string|bool $post_id The Post ID or 'home'.
     * @param bool $enqueue_css Should we also enqueue the layout CSS.
     * @param array|bool $panels_data Existing panels data. By default load from settings or post meta.
     * @param array $layout_data Reformatted panels_data that includes data about the render.
     *
     * @return string
     */
    public function render( $post_id = false, $enqueue_css = true, $panels_data = false, & $layout_data = array() ) {
        if ( empty( $post_id ) ) {
            $post_id = $this->_dataHelper->getPageId();
        }

        global $cleversoft_panels_current_post;
        $old_current_post = $cleversoft_panels_current_post;
        $cleversoft_panels_current_post = $post_id;

        // Try get the cached panel from in memory cache.
        global $cleversoft_panels_cache;
        if ( ! empty( $cleversoft_panels_cache ) && ! empty( $cleversoft_panels_cache[ $post_id ] ) ) {
            return $cleversoft_panels_cache[ $post_id ];
        }

        if ( empty( $panels_data ) ) {
            $panels_data = $this->get_panels_data_for_post( $post_id );
            if ( $panels_data === false ) {
                return false;
            }
        }

        $panels_data = $this->processPanelsData($panels_data);;
        if ( empty( $panels_data ) || empty( $panels_data['grids'] ) ) {
            return '';
        }

        $layout_data = $this->get_panels_layout_data( $panels_data );

        ob_start();

        // Add the panel layout wrapper
        $layout_classes = array( 'panel-layout' );
        if ( $this->_dataHelper->isRtl() ) {
            $layout_classes[] = 'panel-is-rtl';
        }
        $layout_attributes = array(
            'id'    => 'pl-' . $post_id,
            'class' => implode( ' ', $layout_classes ),
        );

        $this->render_element( 'div', $layout_attributes );

        foreach ( $layout_data as $ri => & $row ) {
            $this->render_row( $post_id, $ri, $row, $panels_data );
        }

        echo '</div>';

        if($enqueue_css && !empty($this->print_inline_css)) {
            echo <<<HTML
<style>
{$this->print_inline_css()}
</style>
HTML;
        }

        $html = ob_get_clean();

        // Reset the current post
        $cleversoft_panels_current_post = $old_current_post;

        return $html;
    }

    /**
     * Fix class names that have been incorrectly escaped
     *
     * @param $class
     *
     * @return mixed
     */
    public function fix_namespace_escaping( $class ){
        return preg_replace( '/\\\\+/', '\\', $class );
    }

    /**
     * Render the widget.
     *
     * @param array $widget_info The widget info.
     * @param array $instance The widget instance
     * @param int $grid_index The grid index.
     * @param int $cell_index The cell index.
     * @param int $widget_index The index of this widget.
     * @param bool $is_first Is this the first widget in the cell.
     * @param bool $is_last Is this the last widget in the cell.
     * @param bool $post_id
     * @param string $style_wrapper The start of the style wrapper
     */
    protected function the_widget( $widget_info, $instance, $grid_index, $cell_index, $widget_index, $is_first, $is_last, $post_id = false, $style_wrapper = '', $echo = true ) {

        global $wp_widget_factory;

        // Set widget class to $widget
        $widget_class = $widget_info['class'];
//        $widget_class = apply_filters( 'cleversoft_panels_widget_class', $widget_class );
        $widget_class = $this->fix_namespace_escaping( $widget_class );

        // Load the widget from the widget factory and give themes and plugins a chance to provide their own
        $the_widget = ! empty( $wp_widget_factory[ $widget_class ] ) ? $wp_widget_factory[ $widget_class ] : (! empty( $wp_widget_factory[ $widget_info['type'] ] ) ? $wp_widget_factory[ $widget_info['type'] ] : false);

        if ( empty( $post_id ) ) {
            $post_id = $this->_dataHelper->getPageId();
        }

        $classes = array( 'cs-panel' );
        if ( $this->_settingsHelper->get( 'add_widget_class' ) ) {
            $classes[] = 'widget';
        }
        if ( ! empty( $the_widget ) && ! empty( $the_widget['id_base'] ) ) {
            $classes[] = 'widget_' . $the_widget['id_base'];
        }
        if ( ! empty( $the_widget ) && is_array( $the_widget['code'] ) && ! empty( $the_widget['code'] ) ) {
            $classes[] = $the_widget['code'];
        }
        if ( $is_first ) {
            $classes[] = 'panel-first-child';
        }
        if ( $is_last ) {
            $classes[] = 'panel-last-child';
        }
        
        $storeId = $this->_storeManager->getStore()->getId();
        $id = 'panel-' . $storeId . '-' . $widget_info['widget_id'];

        // Filter and sanitize the classes
        $classes = explode( ' ', implode( ' ', $classes ) );
        $classes = array_filter( $classes );
        $classes = array_unique( $classes );
        $classes = array_map( array($this->_dataHelper,'sanitize_html_class'), $classes );

        $title_html = $this->_settingsHelper->get( 'title_html' );
        if ( strpos( $title_html, '{{title}}' ) !== false ) {
            list( $before_title, $after_title ) = explode( '{{title}}', $title_html, 2 );
        } else {
            $before_title = '<h3 class="widget-title">';
            $after_title = '</h3>';
        }

        // Attributes of the widget wrapper
        $attributes = $this->_dataHelper->widget_attributes( array(
            'id'         => $id,
            'class'      => implode( ' ', $classes ),
            'data-widget-id' => $widget_info['widget_id'],
            'data-index' => isset($widget_info['widget_index']) ? $widget_info['widget_index'] : $widget_index ,
        ), $widget_info );

        $this->_registry->unregister($widget_info['widget_id']);
        // $this->_registry->register($widget_info['widget_id'], $widget_info['style']);

        $before_widget = '<div ';
        foreach ( $attributes as $k => $v ) {
            $before_widget .= ( $k ) . '="' . ( $v ) . '" ';
        }
        $before_widget .= '>';

        $args = array(
            'before_widget' => $before_widget,
            'after_widget'  => '</div>',
            'before_title'  => $before_title,
            'after_title'   => $after_title,
            'widget_id'     => 'widget-' . $grid_index . '-' . $cell_index . '-' . $widget_index
        );

        // Let other themes and plugins change the arguments that go to the widget class.

        // If there is a style wrapper, add it.
        if ( ! empty( $style_wrapper ) ) {
            $args['before_widget'] = $args['before_widget'] . $style_wrapper;
            $args['after_widget'] = '</div>' . $args['after_widget'];
        }

        //build html for child items if have
        if(!empty($instance['panels_info']['items'])) {
            foreach ($instance['panels_info']['items'] as $k=>&$item) {
                if(empty($item)) {
                    continue;
                }
                $item = array_filter($item, function($value) { return !empty($value) ; });
                $tempItem = $item;
                if(isset($item['panels_info']))$tempItem = array($item);

                foreach ($tempItem as $i=>&$it) {
                    if(empty($it) || !isset($it['panels_info'])) {
                        continue;
                    }

                    $it['item_index'] = $k;
                    $html = $this->the_widget(
                        $it['panels_info'],
                        $it,
                        $grid_index,
                        $cell_index,
                        $widget_index,
                        $k == 0,
                        $k == count( $instance['panels_info']['items'] ) - 1,
                        $post_id,
                        '',
                        false
                    );
                    if(isset($item['panels_info'])) $instance['panels_info']['items'][$k]['html'] = $html;
                    else $instance['panels_info']['items'][$k][$i]['html'] = $html;
                }
//                krsort($item);
            }
        }

        if (!$echo) {
            return $this->_objectManager->get($the_widget['class'])->widgetHtml( $args, $instance );
        }
        // This gives other plugins the chance to take over rendering of widgets
        echo $this->_objectManager->get($the_widget['class'])->widgetHtml( $args, $instance );
    }

    /**
     * Print inline CSS in the header and footer.
     */
    protected function print_inline_css() {
        if ( ! empty( $this->inline_css ) ) {
            $the_css = '';
            foreach ( $this->inline_css as $post_id => $css ) {
                if ( empty( $css ) ) {
                    continue;
                }
                $the_css .= '/* Layout ' . ( $post_id ) . ' */ ';
                $the_css .= $css;
            }

            // Reset the inline CSS
            $this->inline_css = null;

            // Allow third party developers to change the inline styles or remove them completely.

            if ( ! empty( $the_css ) ) {
                ?>
                <style type="text/css" media="all"
                       id="cleversoft-panels-layouts-<?php echo ( $this->_dataHelper->getPageId() ) ?>"><?php echo $the_css ?></style><?php
            }
        }
    }

    /**
     * Retrieve panels data for a post or a prebuilt layout or the home page layout.
     *
     * @param string $post_id
     *
     * @return array
     */
    private function get_panels_data_for_post( $post_id ) {
        $panels_data = $this->_dataHelper->getPanelsData();
        return $panels_data;
    }

    /**
     * Transform flat panels data into a hierarchical structure.
     *
     * @param array $panels_data Flat panels data containing `grids`, `grid_cells`, and `widgets`.
     *
     * @return array Hierarchical structure of rows => cells => widgets.
     */
    public function get_panels_layout_data( $panels_data ) {
        $layout_data = array();
        foreach ( $panels_data['grids'] as $grid ) {
            $layout_data[] = array(
                'style'           => ! empty( $grid['style'] ) ? $grid['style'] : array(),
                'ratio'           => ! empty( $grid['ratio'] ) ? $grid['ratio'] : '',
                'fullpage'        => ! empty( $grid['fullpage'] ) ? $grid['fullpage'] : '',
                'ratio_direction' => ! empty( $grid['ratio_direction'] ) ? $grid['ratio_direction'] : '',
                'color_label'     => ! empty( $grid['color_label'] ) ? $grid['color_label'] : '',
                'label'           => ! empty( $grid['label'] ) ? $grid['label'] : '',
                'cells'           => array()
            );
        }

        foreach ( $panels_data['grid_cells'] as $cell ) {
            $layout_data[ $cell['grid'] ]['cells'][] = array(
                'widgets' => array(),
                'style'   => ! empty( $cell['style'] ) ? $cell['style'] : array(),
                'weight'  => floatval( $cell['weight'] ),
            );
        }

        foreach ( $panels_data['widgets'] as $i => $widget ) {
            $widget['panels_info']['widget_index'] = $i;
            $row_index = intval( $widget['panels_info']['grid'] );
            $cell_index = intval( $widget['panels_info']['cell'] );
            $layout_data[ $row_index ]['cells'][ $cell_index ]['widgets'][] = $widget;
        }

        return $layout_data;
    }

    /**
     * Outputs the given HTML tag with the given attributes.
     *
     * @param string $tag The HTML element to render.
     * @param array $attributes The attributes for the HTML element.
     *
     */
    private function render_element( $tag, $attributes ) {

        echo '<' . $tag;
        foreach ( $attributes as $name => $value ) {
            if ( $value ) {
                echo ' ' . $name . '="' . ( $value ) . '" ';
            }
        }
        echo '>';

    }

    /**
     * Add the row data attributes
     *
     * @param $attributes
     * @param $row
     *
     * @return mixed
     */
    public function row_attributes( $attributes, $row ){
        // if( ! empty( $row['style'] ) ) {
        //     $attributes[ 'data-style' ] = json_encode( $row['style'] );
        // }
        if( ! empty( $row['ratio'] ) ) {
            $attributes[ 'data-ratio' ] = floatval( $row['ratio'] );
        }
        if( ! empty( $row['fullpage'] ) ) {
            $attributes[ 'data-fullpage' ] = floatval( $row['fullpage'] );
        }
        if( ! empty( $row['ratio_direction'] ) ) {
            $attributes[ 'data-ratio-direction' ] = $row['ratio_direction'];
        }
        if( ! empty( $row['color_label'] ) ) {
            $attributes[ 'data-color-label' ] = intval( $row['color_label'] );
        }
        if( ! empty( $row['label'] ) ) {
            $attributes[ 'data-label' ] = $row['label'];
        }

        return $attributes;
    }

    /**
     * Render everything for the given row, including:
     *  - filters before and after row,
     *  - row style wrapper,
     *  - row element wrapper with attributes,
     *  - child cells
     *
     * @param string $post_id The ID of the post containing this layout.
     * @param int $ri The index of this row.
     * @param array $row The model containing this row's data and child cells.
     * @param array $panels_data A copy of panels_data for filters.
     *
     */
    private function render_row( $post_id, $ri, & $row, & $panels_data ) {
        $row_classes = array( 'panel-grid' );
        $row_classes[] = 'panel-has-style';

        $row_attributes = $this->row_attributes( array(
            'id'    => 'pg-' . $post_id . '-' . $ri,
            'class' => implode( ' ', $row_classes ),
        ), $row );

        // This allows other themes and plugins to add html before the row

        $this->render_element( 'div', $row_attributes );
        if (count($row['cells']) > 1) {
            echo '<div class="panel-row-style">';
        }
        foreach ( $row['cells'] as $ci => & $cell ) {
            $this->render_cell( $post_id, $ri, $ci, $cell, $row['cells'], $panels_data );
        }
        if (count($row['cells']) > 1) {
            echo '</div>';
        }
        echo '</div>';

    }

    /**
     * @param $attributes
     * @param $cell
     *
     * @return mixed
     */
    public function cell_attributes( $attributes, $cell ){
        // if( ! empty( $cell['style'] ) ) {
        //     $attributes[ 'data-style' ] = json_encode( $cell['style'] );
        // }

        $attributes[ 'data-weight' ] = $cell['weight'];
        $floatRemainWeight = floatval(1 - $cell['weight']);
        $floatPercentWeight = floatval($cell['weight'])*100;
        $attributes[ 'style' ] = "width: calc(".$floatPercentWeight."% - (".$floatRemainWeight." * 30px ) );";
        
        return $attributes;
    }

    /**
     *
     * Render everything for the given cell, including:
     *  - filters before and after cell,
     *  - cell element wrapper with attributes,
     *  - style wrapper,
     *  - child widgets
     *
     * @param string $post_id The ID of the post containing this layout.
     * @param int $ri The index of this cell's parent row.
     * @param int $ci The index of this cell.
     * @param array $cell The model containing this cell's data and child widgets.
     * @param array $cells The array of cells containing this cell.
     * @param array $panels_data A copy of panels_data for filters
     */
    private function render_cell( $post_id, $ri, $ci, & $cell, $cells, & $panels_data ) {

        $cell_classes = array( 'panel-grid-cell' );

        if ( empty( $cell['widgets'] ) ) {
            $cell_classes[] = 'panel-grid-cell-empty';
        }

        if ( $ci == count( $cells ) - 2 && count( $cells[ $ci + 1 ]['widgets'] ) == 0 ) {
            $cell_classes[] = 'panel-grid-cell-mobile-last';
        }

        // Themes can add their own styles to cells

        // Legacy filter, use `cleversoft_panels_cell_classes` instead

        $cell_attributes = $this->cell_attributes( array(
            'id'    => 'pgc-' . $post_id . '-' . $ri . '-' . $ci,
            'class' => implode( ' ', $cell_classes ),
        ), $cell );

        // Legacy filter, use `cleversoft_panels_cell_attributes` instead

        $this->render_element( 'div', $cell_attributes );

        $grid = $panels_data['grids'][ $ri ];

        if ( empty( $cell['style']['class'] ) && ! empty( $grid['style']['cell_class'] ) ) {
            $cell['style']['class'] = $grid['style']['cell_class'];
        }

        $cell_style = ! empty( $cell['style'] ) ? $cell['style'] : array();

        foreach ( $cell['widgets'] as $wi => & $widget ) {
            $is_last = ( $wi == count( $cell['widgets'] ) - 1 );
//            if(count($widget) ==  1  && isset($widget['panels_info'])) continue;
            $this->render_widget( $post_id, $ri, $ci, $wi, $widget, $is_last );
        }

        echo '</div>';
    }

    /**
     *
     * Gets the style wrapper for this widget and passes it through to `the_widget` along with other required parameters.
     *
     * @param string $post_id The ID of the post containing this layout.
     * @param int $ri The index of this widget's ancestor row.
     * @param int $ci The index of this widget's parent cell.
     * @param int $wi The index of this widget.
     * @param array $widget The model containing this widget's data.
     * @param bool $is_last Whether this is the last widget in the parent cell.
     *
     */
    private function render_widget( $post_id, $ri, $ci, $wi, & $widget, $is_last ) {

        $widget_style_wrapper = '';

        $this->the_widget(
            $widget['panels_info'],
            $widget,
            $ri,
            $ci,
            $wi,
            $wi == 0,
            $is_last,
            $post_id,
            $widget_style_wrapper
        );

    }

    public function front_css_url() {
        return $this->getViewFileUrl('CleverSoft_CleverBuilder::css/front-flex.min.css');

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

    protected function _getNotFoundUrl($route = '', $params = ['_direct' => 'core/index/notFound'])
    {
        return $this->_getUrl($route, $params);
    }
}