<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CleverSoft\CleverBuilder\Block\Builder\Widget;

class Row extends \CleverSoft\CleverBuilder\Block\Builder\Builder {

    protected $id_base = 'clever-row';

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
        if(!isset($instance['text']) || empty ($instance['text'])) return '';

        if( !empty( $instance['title'] ) ) echo $args['before_title'] . ($instance['title'] ? $instance['title']  : '') . $args['after_title'];

        $template_html = '<div class="cleversoft-widget-tinymce textwidget">'.($instance['text'] ? $instance['text'] : '').'</div>';

        $wrapper_classes =  array( 'cs-widget-' . $this->id_base, 'cs-widget-'.$this->_cssName );

        $wrapper_classes = array_map( array( $this->_dataHelper, 'sanitize_html_class' ), $wrapper_classes );

        return $this->getHtml( $args, $template_html, $wrapper_classes, '' );
    }

}
