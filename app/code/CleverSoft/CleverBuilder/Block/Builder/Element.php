<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CleverSoft\CleverBuilder\Block\Builder;

/**
 * Cms page content block
 */
class Element extends \CleverSoft\CleverBuilder\Block\Builder\Builder {

    protected $id_base = 'clever-element-editor';
    /*
     * generate html for editor widget
     */
    public function widgetHtml( $args , $instance ){
        $post = $this->getRequest()->getParams();

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
        /*
         * set items data
         *
         */
        if(isset($instance['panels_info']['items'])) $data['items'] = $instance['panels_info']['items'];
        else $data['items'] = array();
        if(isset($instance['panels_info']['panelsItems'])) $data['panelsItems'] = $instance['panels_info']['panelsItems'];
        else $data['panelsItems'] = array();
        /*
         * set widget id
         */
        if(isset($instance['panels_info']['widget_id'])) $data['widget_id'] = $instance['panels_info']['widget_id'];
        else $data['widget_id'] = '';
        /*
         *
         */
        if(!empty($post)){
            if (isset($post['action'])) {
                if($post['action'] == 'so_panels_update_database') {
                    $temp = $instance;
                    unset($temp['panels_info']);
                    $temp = array_filter($temp, function($t){
                        return !is_array($t);
                    });
                    $template_html = $this->_builderWidget->getWidgetDeclaration($instance['panels_info']['type'], $temp, true);
                } else {
                    $template_html = $this->getLayout()->createBlock($instance['panels_info']['type'])->setData($data)->toHtml();
                }
            } else {
                $template_html = $this->getLayout()->createBlock($instance['panels_info']['type'])->setData($data)->toHtml();
            }
        } else {
            $template_html = $this->getLayout()->createBlock($instance['panels_info']['type'])->setData($data)->toHtml();
        }
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
        return $this->getHtml( $args, $template_html, $wrapper_classes, '' , $wrapper_id);
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

        <?php if(isset($the_widget['content_fields']) && count($the_widget['content_fields'])):?>
            <div data-widget-id="<?php echo isset($instance['widget_id']) ? $instance['widget_id'] : '' ?>" data-id="{$id}" class="mage-init-dependency cleversoft-widget-form widget-form-tab active cleversoft-widget-form-main cleversoft-widget-form-main-<?php echo ($class_name) ?>" id="content_<?php echo $form_id ?>" data-tab="content" data-class="<?php echo ( $the_widget['class'] ) ?>" data-mage-init='
            {
            "CleverSoft_CleverBuilder/js/dependency":{
            }}'
                >

                <?php
                foreach( $the_widget['content_fields'] as $field_name => $field_options ) {
                    /* @var $field */
                    $fieldNames[] = $field_options['name'];
                    $field = $this->create_field( $field_options['name'], $field_options, $this );
                    if(isset($the_widget['layouts'])) $instance['layouts'] = $the_widget['layouts'];
                    $field->render( isset( $instance[$field_options['name']] ) ? $instance[$field_options['name']] : null, $instance );
                }
                ?>
            </div>
        <?php endif;?>
        <?php if(isset($the_widget['style_fields']) && count($the_widget['style_fields'])):?>
            <div data-widget-id="<?php echo isset($instance['widget_id']) ? $instance['widget_id'] : '' ?>" data-id="{$id}" class="mage-init-dependency cleversoft-widget-form widget-form-tab cleversoft-widget-form-main cleversoft-widget-form-main-<?php echo ($class_name) ?>" id="style_<?php echo $form_id ?>" data-tab="style" style="display: none;" data-class="<?php echo ( $the_widget['class'] ) ?>" data-mage-init='
            {
            "CleverSoft_CleverBuilder/js/dependency":{
            }}'
                >

                <?php
                foreach( $the_widget['style_fields'] as $field_name => $field_options ) {
                    /* @var $field */
                    $fieldNames[] = $field_options['name'];
                    $field = $this->create_field( $field_options['name'], $field_options, $this );
                    if(isset($the_widget['layouts'])) $instance['layouts'] = $the_widget['layouts'];
                    $field->render( isset( $instance[$field_options['name']] ) ? $instance[$field_options['name']] : null, $instance );
                }
                ?>
            </div>
        <?php endif;?>
        <input type="hidden" name="field-name-{$id}" value="<?php echo implode(',',$fieldNames) ; ?>">

        <?php
        $form = ob_get_clean();
        return $form;
    }
}