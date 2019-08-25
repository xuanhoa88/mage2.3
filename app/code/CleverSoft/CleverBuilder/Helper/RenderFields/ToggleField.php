<?php
namespace CleverSoft\CleverBuilder\Helper\RenderFields;
/**
 *
 * The common base class for text input type fields.
 */
class ToggleField extends \CleverSoft\CleverBuilder\Helper\RenderFields\BaseFieldAbs {

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

    /*
     * return html of the field
     */
    protected function render_field( $value, $instance ) {
        $id = uniqid();
        ?>
        <input <?php if(isset($this->field_options['depends'])) : ?> data-depends="<?php echo htmlspecialchars(json_encode($this->field_options['depends']), ENT_QUOTES, 'UTF-8') ?>" <?php endif; ?> <?php if(isset($this->field_options['indepence'])) : ?> data-indepence="<?php echo htmlspecialchars(json_encode($this->field_options['indepence']), ENT_QUOTES, 'UTF-8') ?>" <?php endif; ?> name="<?php echo $this->element_name?>" class="toggle toggle-ios" id="<?php echo $id?>" type="checkbox" value="<?php echo $value?>" <?php echo $value ? 'checked' : ''?>/>
        <label class="toggle-btn" for="<?php echo $id?>"></label>
        <style>
            .toggle-ios {
                display: none;
            }
            .toggle-ios + .toggle-btn {
                outline: 0;
                display: block;
                width: 4em;
                height: 2em;
                position: relative;
                cursor: pointer;
                -webkit-user-select: none;
                -moz-user-select: none;
                -ms-user-select: none;
                user-select: none;
                background: #fbfbfb;
                border-radius: 2em;
                padding: 2px;
                -webkit-transition: all .4s ease;
                transition: all .4s ease;
                border: 1px solid #e8eae9;
                margin-bottom: 0;
            }
            .toggle-ios + .toggle-btn:after {
                position: relative;
                display: block;
                content: "";
                width: 50%;
                height: 100%;
                left: 0;
                border-radius: 2em;
                background: #fbfbfb;
                -webkit-transition: left 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275), padding 0.3s ease, margin 0.3s ease;
                transition: left 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275), padding 0.3s ease, margin 0.3s ease;
                box-shadow: 0 0 0 1px rgba(0, 0, 0, 0.1), 0 4px 0 rgba(0, 0, 0, 0.08);
            }
            .toggle-ios:checked + .toggle-btn {
                background: #86d993;
            }
            .toggle-ios:checked + .toggle-btn:after {
                left: 50%;
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
