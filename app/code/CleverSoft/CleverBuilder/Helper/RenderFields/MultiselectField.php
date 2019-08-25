<?php
namespace CleverSoft\CleverBuilder\Helper\RenderFields;
/**
 *
 * The common base class for text input type fields.
 */
class MultiselectField extends \CleverSoft\CleverBuilder\Helper\RenderFields\BaseFieldAbs {

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
        if (isset($field_options['source_model'])) {
            $options = $this->_objectManager->get($field_options['source_model'])->toOptionArray();
        } else if (!empty($field_options['values'])){
            $options = $field_options['values'];
        }

        $this->options = $options ;
        parent::prepareFieldVariables($field_options);
    }

    /*
     * set value for local variables
     */
    public function setSelectLocalVariable($key,$val) {
        $this->$key = $val;
    }

    protected function render_field( $value, $instance ) {
        if(!empty( $this->options )) {
        $id = uniqid();
        ?>

                <select multiple <?php if(isset($this->field_options['depends'])) : ?>data-depends="<?php echo htmlspecialchars(json_encode($this->field_options['depends']), ENT_QUOTES, 'UTF-8'); ?>" <?php endif; ?> <?php if(isset($this->field_options['indepence'])) : ?> data-indepence="<?php echo htmlspecialchars(json_encode($this->field_options['indepence']), ENT_QUOTES, 'UTF-8') ?>" <?php endif; ?> name="<?php echo ( $this->element_name ) ?>" id="<?php echo $id ?>"
                        class="form-control cleversoft-widget-input<?php if ( ! empty( $this->input_css_classes ) ) echo ' ' . implode( ' ', $this->input_css_classes ) ?> <?php echo (isset($this->field_options['events'])) ? $this->field_options['events'] : ''; ?>"
                    <?php if( ! empty( $this->multiple ) ) echo 'multiple' ?>>
                    <?php if ( empty( $this->multiple ) && isset( $this->prompt ) ) : ?>
                        <option value="default" disabled="disabled" selected="selected"><?php echo ( $this->prompt ) ?></option>
                    <?php endif; ?>

                    <?php if( isset( $this->options ) && !empty( $this->options ) ) : ?>
                        <?php foreach( $this->options as $key => $val ) : ?>
                            <?php
                            if( is_array($value) && in_array($val['value'], $value)) {
                                $selected = "selected";
                            } else {
                                $selected = "";
                            } ?>
                            <option value="<?php echo ( $val['value'] ) ?>" <?php echo $selected ?>><?php echo ( $val['label'] ) ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
                <?php $this->render_after_field( $value, $instance); ?>

    <?php
        } else {
            echo "<div class='form-control'>".__("You don't have any " . $this->field_options['label'])."</div>";
        }
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
