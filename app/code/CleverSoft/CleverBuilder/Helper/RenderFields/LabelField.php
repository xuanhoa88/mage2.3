<?php
namespace CleverSoft\CleverBuilder\Helper\RenderFields;
/**
 *
 * The common base class for text input type fields.
 */
class LabelField extends \CleverSoft\CleverBuilder\Helper\RenderFields\BaseFieldAbs {

    /**
     * The list of options which may be selected.
     *
     * @access protected
     * @var array
     */
    protected $options;
    /**
     * If present, this string is included as a disabled (not selectable) value at the top of the list of options. If
     * there is no default value, it is selected by default. You might even want to leave the label value blank when
     * you use this.
     *
     * @access protected
     * @var string
     */
    protected $prompt ;
    /**
     * Determines whether this is a single or multiple select field.
     *
     * @access protected
     * @var bool
     */
    protected $multiple;

    /*
     * return options for select field.
     *
     */
    public function prepareFieldVariables($field_options) {
        $this->options = $field_options['label'] ;
        parent::prepareFieldVariables($field_options);
    }

    /*
     * set value for local variables
     */
    public function setSelectLocalVariable($key,$val) {
        $this->$key = $val;
    }

    protected function render_field( $value, $instance ) {
        ?>
        <div class="label-heading">
            <?php echo $this->options?>
        </div>
        <style>
            .cleversoft-widget-field-type-label label.control-label {
                display:none;
            }
            .label-heading {
                padding: 0;
                line-height: 1.5em;
                font-size: 1.8em;
                font-weight: 400;
                border: 0;
                background-color: transparent;
            }
        </style>
        <?php $this->render_after_field( $value, $instance); ?>
    <?php
    }

    protected function sanitize_field_input( $value, $instance ) {
        $values = is_array( $value ) ? $value : array( $value );
        $keys = array_keys( $this->options );
        $sanitized_value = array();
        foreach( $values as $value ) {
            if ( !in_array( $value, $keys ) ) {
                $sanitized_value[] = isset( $this->default ) ? $this->default : false;
            }
            else {
                $sanitized_value[] = $value;
            }
        }

        return count( $sanitized_value ) == 1 ? $sanitized_value[0] : $sanitized_value;
    }
    /**
     * Outputs the html selected attribute.
     *
     * Compares the first two arguments and if identical marks as selected
     *
     * @since 1.0.0
     *
     * @param mixed $selected One of the values to compare
     * @param mixed $current  (true) The other value to compare if not just true
     * @param bool  $echo     Whether to echo or just return the string
     * @return string html attribute or empty string
     */
    protected function selected( $selected, $current = true, $echo = true ) {
        return $this->__checked_selected_helper( $selected, $current, $echo, 'selected' );
    }
    /**
     * Private helper function for checked, selected, disabled and readonly.
     *
     * Compares the first two arguments and if identical marks as $type
     *
     * @since 2.8.0
     * @access private
     *
     * @param mixed  $helper  One of the values to compare
     * @param mixed  $current (true) The other value to compare if not just true
     * @param bool   $echo    Whether to echo or just return the string
     * @param string $type    The type of checked|selected|disabled|readonly we are doing
     * @return string html attribute or empty string
     */
    protected function __checked_selected_helper( $helper, $current, $echo, $type ) {
        if ( (string) $helper === (string) $current )
            $result = " $type='$type'";
        else
            $result = '';

        if ( $echo )
            echo $result;

        return $result;
    }

}
