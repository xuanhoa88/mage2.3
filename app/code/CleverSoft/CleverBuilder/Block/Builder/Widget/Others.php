<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CleverSoft\CleverBuilder\Block\Builder\Widget;

/**
 * Cms page content block
 */
class Others extends \CleverSoft\CleverBuilder\Block\Builder\Builder {

    protected $id_base = 'clever-widget-other';
    /*
     * generate html for editor widget
     */
    public function widgetHtml( $args , $instance ){

        $args = $this->_dataHelper->parse_args( $args, array(
            'before_widget' => '',
            'after_widget' => '',
            'before_title' => '',
            'after_title' => '',
        ) );

        if( !empty( $instance['title'] ) ) echo $args['before_title'] . ($instance['title']) . $args['after_title'];

        $data = array();

        foreach($instance as $key=>$value) {
            if($key != 'panels_info') {
                $data[$key] = $value;
            }
        }
        $template_html = $this->getLayout()->createBlock($instance['panels_info']['type'])->setData($data)->toHtml();

        $wrapper_classes =  array( 'cs-widget-' . $this->id_base, 'cs-widget-'.$this->_cssName );

        $wrapper_id = '';
        if (isset($instance['panels_info']['style'])) {
            if (isset($instance['panels_info']['style']['class'])) {
                $wrapper_classes[] = $instance['panels_info']['style']['class'];
            }
            if (isset($instance['panels_info']['style']['id'])) {
                $wrapper_id = $instance['panels_info']['style']['id'];
            }
        }
        
        // $wrapper_classes = array_map( array( $this->_dataHelper, 'sanitize_html_class' ), $wrapper_classes );

        return $this->getHtml( $args, $template_html, $wrapper_classes, '', $wrapper_id );
    }
    /*
     * return options of widget
     */
    public function getWidgetFormFields($type) {
        $_this = $this->_dataHelper->getWidgetFactory()->getWidgetByClassType($type);
        if(!$_this['parameters']) return array();
        else return $_this['parameters'];

    }
    /**
     * Display the widget form.
     *
     * @param array $instance
     * @param string $form_type Which type of form we're using
     *
     * @return string|void
     */
    public function form( $instance , $the_widget) {
        $_post = $this->_request->getParams();
        // `more_entropy` adds a period to the id.
        $id = str_replace( '.', '', uniqid( rand(), true ) );
        $form_id = 'cleversoft_widget_form_' . md5( $id );
        $class_name = str_replace( '\\', '-', strtolower( $the_widget['class']));

        if( empty( $instance['_sow_form_id'] ) ) {
            $instance['_sow_form_id'] = $id;
        }
        ob_start();
        $fieldNames = array();
        ?>
        <div data-widget-id="<?php echo isset($_post['widget_id']) ? $_post['widget_id'] : '' ?>" data-id="{$id}" class="cleversoft-widget-form widget-form-tab active cleversoft-widget-form-main cleversoft-widget-form-main-<?php echo ($class_name) ?>" id="<?php echo $form_id ?>" data-tab="content" data-class="<?php echo ( $the_widget['class'] ) ?>" data-mage-init='
            {
            "CleverSoft_CleverBuilder/js/dependency":{
            }}'>
            <?php
            foreach( $this->getWidgetFormFields($the_widget['type']) as $field_name => $field_options ) {
                /* @var $field */
                $fieldNames[] = $field_name;
                $field = $this->create_field( $field_name, $field_options, $this );
                $field->render( isset( $instance[$field_name] ) ? $instance[$field_name] : null, $instance );
            }
            ?>
        </div>
        <input type="hidden" name="field-name-{$id}" value="<?php echo implode(',',$fieldNames) ; ?>">
        <?php
        $form = ob_get_clean();
        return $form;
    }
}