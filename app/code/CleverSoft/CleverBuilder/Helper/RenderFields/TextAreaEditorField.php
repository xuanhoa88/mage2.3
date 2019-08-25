<?php
namespace CleverSoft\CleverBuilder\Helper\RenderFields;

class TextAreaEditorField extends \CleverSoft\CleverBuilder\Helper\RenderFields\TextField {
    /**
     * The number of visible rows in the textarea.
     *
     * @access protected
     * @var int
     */
    protected $rows = 10;
    /**
     * The editor initial height. Overrides rows if it is set.
     *
     * @access protected
     * @var int
     */
    protected $editor_height;
    /**
     * The editor to be displayed initially.
     *
     * @access protected
     * @var string
     */
    protected $default_editor = 'tinymce';
    /**
     * The last editor selected by the user.
     *
     * @access protected
     * @var string
     */
    protected $selected_editor;


    protected function get_default_options() {
        return array();
    }

    protected function get_input_classes() {
        $classes = parent::get_input_classes();
        $classes[] = 'wp-editor-area';
        return $classes;
    }

    protected function render_before_field( $value, $instance ) {
        $selected_editor_name = $this->get_selected_editor_field_name( $this->base_name );
        if( ! empty( $instance[ $selected_editor_name ] ) ) {
            $this->selected_editor = $instance[ $selected_editor_name ];
        }
        else {
            $this->selected_editor = $this->default_editor;
        }
        parent::render_before_field( $value, $instance );
    }

    protected function render_field( $value, $instance ) {
        $id = uniqid();
        $data = array(
            'content'=> $value,
            'attributes' => $this->render_data_attributes( $this->get_input_data_attributes() ),
            'name' => $this->element_name,
            'element_class'=> isset($this->field_options['element_class']) ? $this->field_options['element_class'] : '',
            'widget_id' => isset($instance['widget_id']) ? $instance['widget_id'] : '',
            'id' => $id,
            'rows' => $this->rows,
            'classes' => $this->render_CSS_classes( $this->get_input_classes() ),
            'hidden_field_name' => $this->for_widget->so_get_field_name( $this->base_name . '_selected_editor', $this->parent_container) ,
            'hidden_field_value' => $this->selected_editor
        );
        echo $this->_layout->createBlock('\Magento\Framework\View\Element\Template')->setData($data)->setTemplate('CleverSoft_CleverBuilder::widget/text-editor.phtml')->toHtml();
        $this->render_after_field( $value, $instance);
    }

    protected function sanitize_field_input( $value, $instance ) {
        $sanitized_value = ( $value );
        return $sanitized_value;
    }

    public function get_selected_editor_field_name( $base_name ) {
        $v_name = $base_name;
        if( strpos($v_name, '][') !== false ) {
            // Remove this splitter
            $v_name = substr( $v_name, strrpos($v_name, '][') + 2 );
        }
        return $v_name . '_selected_editor';
    }

    protected function render_data_attributes( $data_attributes ) {
        $attr_string = '';
        foreach ( $data_attributes as $name => $value ) {
            $attr_string = ' data-' . ( $name ) . '="' . ( $value ) . '"';
        }
        return $attr_string;
    }
}
