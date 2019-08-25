<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace CleverSoft\CleverBuilder\Block\Builder;

use CleverSoft\CleverBuilder\Helper\Data as Data;
use CleverSoft\CleverBuilder\Helper\RenderFields\BaseFieldAbs;
use CleverSoft\CleverBuilder\Helper\RenderFields\TextField;
use CleverSoft\CleverBuilder\Helper\RenderFields\LabelField;
use CleverSoft\CleverBuilder\Helper\RenderFields\TextAreaEditorField;
use CleverSoft\CleverBuilder\Helper\RenderFields\SelectField;
use CleverSoft\CleverBuilder\Helper\RenderFields\MultiselectField;
use Magento\Framework\DB\Select\SelectRenderer;
use CleverSoft\CleverBuilder\Helper\RenderFields\SliderField;
use CleverSoft\CleverBuilder\Helper\RenderFields\MediaField;
use CleverSoft\CleverBuilder\Helper\RenderFields\PositionField;
use CleverSoft\CleverBuilder\Helper\RenderFields\UnitField;
use CleverSoft\CleverBuilder\Helper\RenderFields\ToggleField;
use CleverSoft\CleverBuilder\Helper\RenderFields\ColorField;
use CleverSoft\CleverBuilder\Helper\RenderFields\CalendarField;

/**
 * Cms page content block
 */

/** @noinspection PhpUnnecessaryFullyQualifiedNameInspection */
class Builder extends \Magento\Framework\View\Element\Template
{
    /*
     *
     */
    protected $_cssName = 'sow-editor-base';
    protected $_dataHelper;
    protected $id_base;
    protected $number;
    protected $label = '';
    protected $element_id;
    protected $required = false ;
    protected $_baseRender;
    protected $field_ids;
    protected $_textFieldRender;
    protected $_labelFieldRender;
    protected $_textareaFieldRender;
    protected $_filterProvider;
    protected $_selectFieldRender;
    protected $_multiselectFieldRender;
    protected $_buttonGroupFieldRender;
    protected $_categoryidsFieldRender;
    protected $_sliderFieldRender;
    protected $_mediaFieldRender;
    protected $_positionFieldRender;
    protected $_unitFieldRender;
    protected $_toggleFieldRender;
    protected $_colorFieldRender;
    protected $_builderWidget;
    protected $_innerrowlayoutFieldRender;
    protected $_calendarFieldRender;

    public function __construct(
        /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection */
        \Magento\Framework\View\Element\Template\Context $context,
        Data $dataHelper, BaseFieldAbs $baseFieldAbs,
        SelectField $selectRender,
        MultiselectField $multiselectRender,
        SliderField $sliderField,
        MediaField $mediaField,
        PositionField $positionField,
        UnitField $unitField,
        ToggleField $toggleField,
        ColorField $colorField,
        /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection */
        \Magento\Cms\Model\Template\FilterProvider $filterProvider,
        TextAreaEditorField $textareaFieldRender,
        LabelField $labelField, TextField $textField,
        /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection */
        \CleverSoft\CleverBuilder\Helper\RenderFields\CategoryIdsField $categoryIdsField ,
        \CleverSoft\CleverBuilder\Helper\RenderFields\ButtonGroupField $buttonGroupField,
        \CleverSoft\CleverBuilder\Helper\RenderFields\InnerrowLayoutField $innerrowLayoutField ,
        \CleverSoft\CleverBuilder\Helper\RenderFields\CalendarField $calendarField ,
        \CleverSoft\CleverBuilder\Helper\Widget\Builder $builderWidget,
        array $data = []
    )  {
        $this->_dataHelper = $dataHelper;
        $this->_baseRender = $baseFieldAbs;
        $this->_storeManager = $context->getStoreManager();
        $this->_textareaFieldRender = $textareaFieldRender;
        $this->_filterProvider = $filterProvider;
        $this->_textFieldRender = $textField;
        $this->_labelFieldRender = $labelField;
        $this->_categoryidsFieldRender = $categoryIdsField;
        $this->_innerrowlayoutFieldRender = $innerrowLayoutField;
        $this->_buttonGroupFieldRender = $buttonGroupField;
        $this->_builderWidget =  $builderWidget;
        $this->_selectFieldRender = $selectRender;
        $this->_multiselectFieldRender = $multiselectRender;
        $this->_sliderFieldRender = $sliderField;
        $this->_mediaFieldRender = $mediaField;
        $this->_positionFieldRender = $positionField;
        $this->_unitFieldRender = $unitField;
        $this->_toggleFieldRender = $toggleField;
        $this->_colorFieldRender = $colorField;
        $this->_calendarFieldRender = $calendarField;
        $storeId = $this->_storeManager->getStore()->getId();
        $this->_blockFilter = $this->_filterProvider->getBlockFilter()->setStoreId($storeId);
        parent::__construct($context, $data);
    }

    public function getHtml( $args, $widget_html, $wrapper_classes, $wrapper_data_string , $wrapper_id = '', $clever_widget = false){
        if($clever_widget) return $widget_html;
        if ($wrapper_id) {
            return  $args['before_widget'] . '<div id="'.$wrapper_id.'" class="' .  implode( ' ', $wrapper_classes )  . '"' . $wrapper_data_string . '>'. $widget_html . '</div>' . $args['after_widget'];
        }
        return  $args['before_widget'] . '<div class="' .  implode( ' ', $wrapper_classes )  . '"' . $wrapper_data_string . '>'. $widget_html . '</div>' . $args['after_widget'];
    }
    /*
     * return object of Base Renderer Helper
     */
    public function getBaseRenderHelper(){
        return $this->_baseRender;
    }

    /**
     * Display the widget form.
     *
     * @param array $instance
     * @param string $form_type Which type of form we're using
     *
     * @return string|void
     */
    public function form( $instance, $the_widget ) {}

    /*
     *
     */
    public function getWidgetFormFields($type){
        return array();
    }
    /**
     * Constructs name attributes for use in form() fields
     *
     * This function should be used in form() methods to create name attributes for fields
     * to be saved by update()
     *
     * @since 2.8.0
     * @since 4.4.0 Array format field names are now accepted.
     *
     * @param string $field_name Field name
     * @return string Name attribute for $field_name
     */
    public function get_field_name($field_name) {
        if ( false === $pos = strpos( $field_name, '[' ) ) {
            return 'widget-' . $this->id_base . '[' . $this->number . '][' . $field_name . ']';
        } else {
            return 'widget-' . $this->id_base . '[' . $this->number . '][' . substr_replace( $field_name, '][', $pos, strlen( '[' ) );
        }
    }
    /*
     * set value for property
     *
     */
    public function setPropertyValue($key,$val) {
        $this->$key = $val;
    }
    /**
     * Utility function to get a field name for a widget field.
     *
     * @param $field_name
     * @param array $container
     * @return mixed|string
     */
    public function so_get_field_name( $field_name, $container = array() ) {
        if( empty($container) ) {
            $name = $this->get_field_name( $field_name );
        }
        else {
            // We also need to add the container fields
            $container_extras = '';
            foreach($container as $r) {
                $container_extras .= '[' . $r['name'] . ']';

                if( $r['type'] == 'repeater' ) {
                    $container_extras .= '[#' . $r['name'] . '#]';
                }
            }

            $name = $this->get_field_name( '{{{FIELD_NAME}}}' );
            $name = str_replace('[{{{FIELD_NAME}}}]', $container_extras.'[' . ($field_name) . ']', $name);
        }

        return $name;
    }

    /**
     * Get the ID of this field.
     *
     * @param $field_name
     * @param array $container
     * @param boolean $is_template
     *
     * @return string
     */
    public function so_get_field_id( $field_name, $container = array(), $is_template = false ) {
        if( empty($container) ) {
            return $this->get_field_id($field_name);
        }
        else {
            $name = array();
            foreach ( $container as $container_item ) {
                $name[] = $container_item['name'];
            }
            $name[] = $field_name;
            $field_id_base = $this->get_field_id(implode('-', $name));
            if ( $is_template ) {
                return $field_id_base . '-_id_';
            }
            if ( ! isset( $this->field_ids[ $field_id_base ] ) ) {
                $this->field_ids[ $field_id_base ] = 1;
            }
            $curId = $this->field_ids[ $field_id_base ]++;
            return $field_id_base . '-' . $curId;
        }
    }
    /**
     * Constructs id attributes for use in WP_Widget::form() fields.
     *
     * This function should be used in form() methods to create id attributes
     * for fields to be saved by WP_Widget::update().
     *
     * @since 2.8.0
     * @since 4.4.0 Array format field IDs are now accepted.
     *
     * @param string $field_name Field name.
     * @return string ID attribute for `$field_name`.
     */
    public function get_field_id( $field_name ) {
        return 'widget-' . $this->id_base . '-' . $this->number . '-' . trim( str_replace( array( '[]', '[', ']' ), array( '', '-', '' ), $field_name ), '-' );
    }

    /*
     * return instance object of the field render
     */
    public function create_field( $field_name, $field_options, $for_widget, $for_repeater = array(), $is_template = false ) {
        $element_id = $for_widget->so_get_field_id( $field_name, $for_repeater, $is_template );
        $element_name = $for_widget->so_get_field_name( $field_name, $for_repeater );
        $field_class = $this->get_field_class_name( $field_options['type'] , $field_name );
        $this->$field_class->initParams( $field_name, $element_id, $element_name, $field_options, $for_widget, $for_repeater );
        if(method_exists($this->$field_class,'prepareFieldVariables')) {
            $this->$field_class->prepareFieldVariables($field_options);
        }
        return $this->$field_class;
    }
    /*
     * return string of class name
     */
    private function get_field_class_name( $field_type , $field_name) {
        if(class_exists($field_type) || $field_type == 'conditions') {
            $field_type = str_replace('_', '' , $field_name);
        }
        $field_class = '_'.$field_type.'FieldRender';
        if ( !isset( $this->$field_class ) ) {
            $field_class = '_baseRender';
        }
        return $field_class;
    }
}