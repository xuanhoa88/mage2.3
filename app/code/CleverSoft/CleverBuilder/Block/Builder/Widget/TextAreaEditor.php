<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CleverSoft\CleverBuilder\Block\Builder\Widget;

class TextAreaEditor extends \CleverSoft\CleverBuilder\Block\Builder\Builder {

    protected $id_base = 'clever-editor';
    /*
     * return form fields
     */
    public function getWidgetFormFields($type = false) {
        return array(
            'text' => array(
                'type' => 'textarea',
                'rows' => 20 // Let the editor handle it's own processing.
            )
        );
    }
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
        if(!isset($instance['text']) || empty ($instance['text'])) $instance['text'] = 'Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam erat volutpat.';

        if( !empty( $instance['title'] ) ) echo $args['before_title'] . ($instance['title'] ? $instance['title']  : '') . $args['after_title'];



        $style_editor_weight = isset($instance["weight"]) && $instance["weight"] ? 'font-weight: '.$instance["weight"].';' : '';
        $style_editor_transform = isset($instance["transform"]) && $instance["transform"] ? 'text-transform: '.$instance["transform"].';' : 'text-transform: inherit;';
        $style_editor_style = isset($instance["style"]) && $instance["style"] ? 'font-style: '.$instance["style"].';' : 'font-style: inherit;';
        $style_editor_line_height = isset($instance["line-height"]) && $instance["line-height"] ? 'line-height: '.$instance["line-height"].'px;' : '';
        $style_editor_letter_spacing = isset($instance["letter-spacing"]) && $instance["letter-spacing"] ? 'letter-spacing: '.$instance["letter-spacing"].'px;' : '';
        $style_editor_css = $style_editor_weight.$style_editor_transform.$style_editor_style.$style_editor_line_height.$style_editor_letter_spacing;


        $template_html = '<div style="'.$style_editor_css.'" class="cleversoft-widget-tinymce textwidget global-element">'.($instance['text'] ? $instance['text'] : '').'</div>';

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
        $class_name = str_replace( '\\', '-', strtolower( $the_widget['class'] ) );

        if( empty( $instance['_sow_form_id'] ) ) {
            $instance['_sow_form_id'] = $id;
        }
        ob_start();
        ?>
            <div data-widget-id="<?php echo isset($_post['widget_id']) ? $_post['widget_id'] : '' ?>" class="cleversoft-widget-form widget-form-tab active cleversoft-widget-form-main cleversoft-widget-form-main-<?php echo ($class_name) ?>" id="<?php echo $form_id ?>" data-tab="content" data-class="<?php echo ( $the_widget['class'] ) ?>">
                <?php
                foreach( $this->getWidgetFormFields('textarea') as $field_name => $field_options ) {
                    /* @var $field  */
                    $field = $this->create_field( $field_name, $field_options, $this );
                    $field->render( isset( $instance[$field_name] ) ? $instance[$field_name] : null, $instance );
                }
                ?>
            </div>
            <div data-widget-id="<?php echo isset($_post['widget_id']) ? $_post['widget_id'] : '' ?>" class="cleversoft-widget-form widget-form-tab cleversoft-widget-form-main cleversoft-widget-form-main-<?php echo ($class_name) ?>" id="<?php echo $form_id ?>" data-tab="style" data-class="<?php echo ( $the_widget['class'] ) ?>" style="display: none;">
                <?php
                $styleFields = [
                    [
                        "type" => "select",
                        "name" => "weight",
                        "code" => "font-weight",
                        "attribute_type" => "style",
                        "required" => "",
                        "values" => [
                            [
                                "value" => 100,
                                "label" => "100"
                            ],
                            [
                                "value" => 200,
                                "label" => "200"
                            ],
                            [
                                "value" => 300,
                                "label" => "300"
                            ],
                            [
                                "value" => 400,
                                "label" => "400"
                            ],
                            [
                                "value" => 500,
                                "label" => "500"
                            ],
                            [
                                "value" => 600,
                                "label" => "600"
                            ],
                            [
                                "value" => 700,
                                "label" => "700"
                            ],
                            [
                                "value" => 800,
                                "label" => "800"
                            ],
                            [
                                "value" => 900,
                                "label" => "900"
                            ],
                            [
                                "value" => "",
                                "label" => "Default"
                            ],
                            [
                                "value" => "normal",
                                "label" => "Normal"
                            ],
                            [
                                "value" => "bold",
                                "label" => "Bold"
                            ]
                        ],
                        "visible" => "1",
                        "label" => "Weight"
                    ],
                    [
                        "type" => "select",
                        "name" => "transform",
                        "code" => "text-transform",
                        "attribute_type" => "style",
                        "required" => "",
                        "values" => [
                            [
                                "value" => "initial",
                                "label" => "Default"
                            ],
                            [
                                "value" => "uppercase",
                                "label" => "Uppercase"
                            ],
                            [
                                "value" => "lowercase",
                                "label" => "Lowercase"
                            ],
                            [
                                "value" => "capitalize",
                                "label" => "Capitalize"
                            ]
                        ],
                        "visible" => "1",
                        "label" => "Transform"
                    ],
                    [
                        "type" => "select",
                        "name" => "style",
                        "code" => "font-style",
                        "attribute_type" => "style",
                        "required" => "",
                        "values" => [
                            [
                                "value" => "normal",
                                "label" => "Normal"
                            ],
                            [
                                "value" => "italic",
                                "label" => "Italic"
                            ],
                            [
                                "value" => "oblique",
                                "label" => "Oblique"
                            ]
                        ],
                        "visible" => "1",
                        "label" => "Style"
                    ],
                    [
                        "type" => "slider",
                        "name" => "line-height",
                        "code" => "line-height",
                        "attribute_type" => "style",
                        "unit" => "px",
                        "required" => "",
                        "min" => "1",
                        "max" => "100",
                        "value" => "30",
                        "visible" => "1",
                        "label" => "Line-Height(px)"
                    ],
                    [
                        "type" => "slider",
                        "name" => "letter-spacing",
                        "code" => "letter-spacing",
                        "attribute_type" => "style",
                        "unit" => "px",
                        "required" => "",
                        "min" => "-5",
                        "max" => "10",
                        "value" => "2",
                        "visible" => "1",
                        "label" => "Letter Spacing(px)"
                    ]
                ];
                foreach( $styleFields as $field_name => $field_options ) {
                    /* @var $field */
                    $field = $this->create_field( $field_options['name'], $field_options, $this );
                    $field->render( isset( $instance[$field_options['name']] ) ? $instance[$field_options['name']] : null, $instance );
                }
                ?>
                </div>
            </div>
            <style>
                .clearlooks2 {z-index: 999999999999999999!important;}
            </style>
        <?php
        $form = ob_get_clean();
        return $form;
    }

}
