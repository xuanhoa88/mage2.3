<?php
namespace CleverSoft\CleverBuilder\Helper\RenderFields;
/**
 *
 * The common base class for text input type fields.
 */
class CalendarField extends \CleverSoft\CleverBuilder\Helper\RenderFields\BaseFieldAbs {
    /*
     *
     */
    protected $placeholder;
    protected $readonly;
    protected $input_type;
    /*
     *
     */
    protected function get_input_classes() {
        return array( 'widefat', 'cleversoft-widget-input' ,'form-control', 'datepicker');
    }

    /**
     * The data attributes to be added to the input element.
     */
    protected function get_input_data_attributes() {
        return array();
    }
/*
 * return string of field's attributes
 */
    protected function render_data_attributes( $data_attributes ) {
        $attr_string = '';
        foreach ( $data_attributes as $name => $value ) {
            $attr_string = ' data-' . ( $name ) . '="' . ( $value ) . '"';
        }
        echo $attr_string;
    }
/*
 * return html of the field
 */
    protected function render_field( $value, $instance ) {
        $id = uniqid();
        ?>

            <input <?php if(isset($this->field_options['depends'])) : ?> data-depends="<?php echo htmlspecialchars(json_encode($this->field_options['depends']), ENT_QUOTES, 'UTF-8') ?>" <?php endif; ?> <?php if(isset($this->field_options['indepence'])) : ?> data-indepence="<?php echo htmlspecialchars(json_encode($this->field_options['indepence']), ENT_QUOTES, 'UTF-8') ?>" <?php endif; ?> type="<?php echo ( $this->input_type ) ?>"
                   name="<?php echo ( $this->element_name ) ?>"
                   id="<?php echo $id ?>"
                   value="<?php echo ( $value ) ?>"
                <?php $this->render_data_attributes( $this->get_input_data_attributes() ) ?>
                   class="<?php echo $this->render_CSS_classes( $this->get_input_classes() ) ?>"
                <?php if ( ! empty( $this->placeholder ) ) echo 'placeholder="' . ( $this->placeholder ) . '"' ?>
                <?php if( ! empty( $this->readonly ) ) echo 'readonly' ?> />
            <?php $this->render_after_field( $value, $instance); ?>
            <script>
                require(['jquery', 'jquery/ui' , 'mage/translate', 'mage/calendar'], function($, $t){
                    $('.datepicker').datetimepicker({
                        prevText: '&#x3c;zurück', prevStatus: '',
                        prevJumpText: '&#x3c;&#x3c;', prevJumpStatus: '',
                        nextText: 'Vor&#x3e;', nextStatus: '',
                        nextJumpText: '&#x3e;&#x3e;', nextJumpStatus: '',
                        showMonthAfterYear: false,
                        showSecond: true,
                        minDate: new Date()
                    });

                });
            </script>

    <?php
    }

    protected function sanitize_field_input( $value, $instance ) {
        $sanitized_value = ( $value );
        return $sanitized_value;
    }
}
