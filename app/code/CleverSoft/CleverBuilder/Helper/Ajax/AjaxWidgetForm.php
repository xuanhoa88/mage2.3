<?php
/**
 * @category    CleverSoft
 * @package     CleverLayeredNavigation
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */
namespace CleverSoft\CleverBuilder\Helper\Ajax;

class AjaxWidgetForm extends \CleverSoft\CleverBuilder\Helper\Panels\Panels {

    /*
     * render form html
     */
    public function getHtml(){
        $_post = $this->_getRequest()->getParams();
        if ( empty( $_post['widget'] ) ) {
            die();
        }
        $instance = ! empty( $_post['instance'] ) ? json_decode( $_post['instance'], true ) : array();
        if(!isset($instance['widget_id'])) $instance['widget_id'] = isset($_post['widget_id']) ? $_post['widget_id'] : false;
        $form = $this->render_form( $_post['widget'], $instance, $_post['raw'] == 'true' ,'{$id}', isset($_post['type']) ? $_post['type'] : false );
        echo $form;
        die();
    }

    /**
     * Render a widget form with all the Page Builder specific fields
     *
     * @param string $widget The class of the widget
     * @param array $instance Widget values
     * @param bool $raw
     * @param string $widget_number
     *
     * @return mixed|string The form
     */
    function render_form( $widget, $instance = array(), $raw = false, $widget_number = '{$id}' , $type = false) {
        global $wp_widget_factory;

        // This is a chance for plugins to replace missing widgets
        $the_widget = ! empty( $wp_widget_factory[ $widget ] ) ? $wp_widget_factory[ $widget ] : $wp_widget_factory[$type];
        if ( !( $the_widget ) || empty ($the_widget)) {
            $form =
                '<div class="panels-missing-widget-form"><p>' .
                preg_replace(
                    array(
                        '/1\{ *(.*?) *\}/',
                        '/2\{ *(.*?) *\}/',
                    ),
                    array(
                        '<strong>$1</strong>',
                        '<a href="#" target="_blank" rel="noopener noreferrer">$1</a>'
                    ),
                    sprintf(
                        __( 'The widget 1{%1$s} is not available. Please try locate and install the missing plugin. Post on the 2{support forums} if you need help.'),
                        ( $widget )
                    )
                ) .
                '</p></div>';
        } else {
            $render = $this->_objectManager->get($the_widget['class']);
            $render->setPropertyValue('id','temp');
            $render->setPropertyValue('number',$widget_number);

            ob_start();
            echo $render->form( $instance , $the_widget );
            $form = ob_get_clean();

//         Convert the widget field naming into ones that Page Builder uses
            $exp  = preg_quote( $this->_objectManager->get($the_widget['class'])->get_field_name( '____' ) );
            $exp  = str_replace( '____', '(.*?)', $exp );
            $form = preg_replace( '/' . $exp . '/', 'widgets[' . preg_replace( '/\$(\d)/', '\\\$$1', $widget_number ) . '][$1]', $form );
        }
//         Add all the information fields
        return $form;
    }
}

