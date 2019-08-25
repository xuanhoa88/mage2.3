<?php
namespace CleverSoft\CleverBuilder\Helper\RenderFields;
/**
 *
 * The common base class for text input type fields.
 */
class SelectField extends \CleverSoft\CleverBuilder\Helper\RenderFields\BaseFieldAbs {

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
        $storeId = $this->_storeManager->getStore()->getId();
        $panelId = '#panel-'.$storeId.'-'.$instance['widget_id'];
        ?>

            <select <?php if(isset($this->field_options['active_icon']) && $this->field_options['active_icon']) : ?> data-icon="true" <?php endif; ?> <?php if(isset($this->field_options['depends'])) : ?>data-depends="<?php echo htmlspecialchars(json_encode($this->field_options['depends']), ENT_QUOTES, 'UTF-8'); ?>" <?php endif; ?> <?php if(isset($this->field_options['indepence'])) : ?> data-indepence="<?php echo htmlspecialchars(json_encode($this->field_options['indepence']), ENT_QUOTES, 'UTF-8') ?>" <?php endif; ?> name="<?php echo ( $this->element_name ) ?>" id="<?php echo $id ?>"
                        class="panel-select form-control cleversoft-widget-input<?php if ( ! empty( $this->input_css_classes ) ) echo ' ' . implode( ' ', $this->input_css_classes ) ?> <?php echo (isset($this->field_options['events'])) ? $this->field_options['events'] : ''; ?>"
                    <?php if( ! empty( $this->multiple ) ) echo 'multiple' ?>>
                    <?php if ( empty( $this->multiple ) && isset( $this->prompt ) ) : ?>
                        <option value="default" disabled="disabled" selected="selected"><?php echo ( $this->prompt ) ?></option>
                    <?php endif; ?>

                    <?php if( isset( $this->options ) && !empty( $this->options ) ) : ?>
                        <?php foreach( $this->options as $key => $val ) : ?>
                            <?php
                            if( is_array( $value ) ) {
                                $selected = $this->selected( true, in_array( $key, $value ), false );
                            }
                            else {
                                $selected = $this->selected( $value, $val['value'], false );
                            } ?>
                            <option value="<?php echo ( $val['value'] ) ?>" <?php echo $selected ?>><?php echo ( $val['label'] ) ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
                <script>
                    require(['jquery', 'jquery/ui', 'cleverSelect2'], function($){ 
                        function formatState (state) {
                            if (!state.id) {
                                return state.text;
                            }
                            var $state = $(
                                '<span><i class="margin-0 fa '+state.id.toLowerCase()+'"></i> '+state.text+'</span>'
                            );
                            return $state;
                        };
                        $(".panel-select").select2({
                            templateSelection: formatState,
                            templateResult: formatState
                        });

                        $('#<?php echo $id?>').on('change', function (e) {
                            var $content = $(this).val();
                            var $value = "<?=is_array($value) ? '' : $value?>";
                            var $attribute_type = "<?=isset($this->field_options['attribute_type']) ? $this->field_options['attribute_type'] : ''?>";
                            var $class = "<?=isset($this->field_options['element_class']) ? $this->field_options['element_class'] : ''?>";
                            var $data_code = "<?=isset($this->field_options['code']) ? $this->field_options['code'] : ''?>";
                            if($class) {
                                var classEl = $(".cleversoft-panels-iframe-preview").contents().find("<?=$panelId?> ."+$class).first();
                            } else {
                                var classEl = $(".cleversoft-panels-iframe-preview").contents().find("<?=$panelId?> .global-element").first();
                            }

                            if ($attribute_type) {
                                switch($attribute_type) {
                                    case 'style':
                                        <?php if(isset($this->field_options['code'])):?>
                                        classEl.css('<?=$this->field_options['code']?>',$content);
                                        <?php endif;?>
                                        break;
                                    case 'class':
                                       <?php if ((isset($this->field_options['unit']) && $this->field_options['unit']) || (isset($this->field_options['prefix']) && $this->field_options['prefix'])):?>
                                          <?php if ((isset($this->field_options['unit']) && $this->field_options['unit']) && (isset($this->field_options['prefix']) && $this->field_options['prefix'])):?>
                                            $value = '<?=$this->field_options['prefix']?>'+$value+'<?=$this->field_options['unit']?>';
                                          <?php else:?>
                                            <?php if (isset($this->field_options['prefix']) && $this->field_options['prefix']):?>
                                              $value = '<?=$this->field_options['prefix']?>'+$value;
                                            <?php endif;?>
                                            <?php if (isset($this->field_options['unit']) && $this->field_options['unit']):?>
                                                $value = $value+'<?=$this->field_options['unit']?>';
                                            <?php endif;?>
                                          <?php endif;?>
                                        <?php endif;?>
                                        classEl.removeClass($value);
                                        if (window.class<?php echo $id?>) {
                                          classEl.removeClass(window.class<?php echo $id?>);
                                        }
                                        <?php if ((isset($this->field_options['unit']) && $this->field_options['unit']) || (isset($this->field_options['prefix']) && $this->field_options['prefix'])):?>
                                              <?php if ((isset($this->field_options['unit']) && $this->field_options['unit']) && (isset($this->field_options['prefix']) && $this->field_options['prefix'])):?>
                                                $content = '<?=$this->field_options['prefix']?>'+$content+'<?=$this->field_options['unit']?>';
                                              <?php else:?>
                                                <?php if (isset($this->field_options['prefix']) && $this->field_options['prefix']):?>
                                                  $content = '<?=$this->field_options['prefix']?>'+$content;
                                                <?php endif;?>
                                                <?php if (isset($this->field_options['unit']) && $this->field_options['unit']):?>
                                                    $content = $content+'<?=$this->field_options['unit']?>';
                                                <?php endif;?>
                                              <?php endif;?>
                                          <?php endif;?>
                                          classEl.addClass($content);
                                          window.class<?php echo $id?> = $content;

                                        break;
                                    case 'attribute':
                                        classEl.attr($data_code,$content);
                                        break;
                                    default:

                                }
                            }
                            
                        });
                    });
                </script>
                <style>
                    .select2-container{box-sizing:border-box;display:inline-block;margin:0;position:relative;vertical-align:middle;}.select2-container .select2-selection--single{box-sizing:border-box;cursor:pointer;display:block;height:28px;user-select:none;-webkit-user-select:none;}.select2-container .select2-selection--single .select2-selection__rendered{display:block;padding-left:8px;padding-right:20px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}.select2-container[dir="rtl"] .select2-selection--single .select2-selection__rendered{padding-right:8px;padding-left:20px;}.select2-container .select2-selection--multiple{box-sizing:border-box;cursor:pointer;display:block;min-height:32px;user-select:none;-webkit-user-select:none;}.select2-container .select2-selection--multiple .select2-selection__rendered{display:inline-block;overflow:hidden;padding-left:8px;text-overflow:ellipsis;white-space:nowrap;}.select2-container .select2-search--inline{float:left;}.select2-container .select2-search--inline .select2-search__field{box-sizing:border-box;border:none;font-size:100%;margin-top:5px;}.select2-container .select2-search--inline .select2-search__field::-webkit-search-cancel-button{-webkit-appearance:none;}.select2-dropdown{background-color:white;border:1px solid #aaa;border-radius:4px;box-sizing:border-box;display:block;position:absolute;left:-100000px;width:100%;z-index:1051;}.select2-results{display:block;}.select2-results__options{list-style:none;margin:0;padding:0;}.select2-results__option{padding:6px;user-select:none;-webkit-user-select:none;}.select2-results__option[aria-selected]{cursor:pointer;}.select2-container--open .select2-dropdown{left:0;}.select2-container--open .select2-dropdown--above{border-bottom:none;border-bottom-left-radius:0;border-bottom-right-radius:0;}.select2-container--open .select2-dropdown--below{border-top:none;border-top-left-radius:0;border-top-right-radius:0;}.select2-search--dropdown{display:block;padding:4px;}.select2-search--dropdown .select2-search__field{padding:4px;width:100%;box-sizing:border-box;}.select2-search--dropdown .select2-search__field::-webkit-search-cancel-button{-webkit-appearance:none;}.select2-search--dropdown.select2-search--hide{display:none;}.select2-close-mask{border:0;margin:0;padding:0;display:block;position:fixed;left:0;top:0;min-height:100%;min-width:100%;height:auto;width:auto;opacity:0;z-index:99;background-color:#fff;filter:alpha(opacity=0);}.select2-hidden-accessible{border:0;clip:rect(0 0 0 0);height:1px;margin:-1px;overflow:hidden;padding:0;position:absolute;width:1px;}.select2-container--default .select2-selection--single{background-color:#fff;border:1px solid #aaa;border-radius:4px;}.select2-container--default .select2-selection--single .select2-selection__rendered{color:#444;line-height:28px;}.select2-container--default .select2-selection--single .select2-selection__clear{cursor:pointer;float:right;font-weight:bold;}.select2-container--default .select2-selection--single .select2-selection__placeholder{color:#999;}.select2-container--default .select2-selection--single .select2-selection__arrow{height:26px;position:absolute;top:1px;right:1px;width:20px;}.select2-container--default .select2-selection--single .select2-selection__arrow b{border-color:#888 transparent transparent transparent;border-style:solid;border-width:5px 4px 0 4px;height:0;left:50%;margin-left:-4px;margin-top:-2px;position:absolute;top:50%;width:0;}.select2-container--default[dir="rtl"] .select2-selection--single .select2-selection__clear{float:left;}.select2-container--default[dir="rtl"] .select2-selection--single .select2-selection__arrow{left:1px;right:auto;}.select2-container--default.select2-container--disabled .select2-selection--single{background-color:#eee;cursor:default;}.select2-container--default.select2-container--disabled .select2-selection--single .select2-selection__clear{display:none;}.select2-container--default.select2-container--open .select2-selection--single .select2-selection__arrow b{border-color:transparent transparent #888 transparent;border-width:0 4px 5px 4px;}.select2-container--default .select2-selection--multiple{background-color:white;border:1px solid #aaa;border-radius:4px;cursor:text;}.select2-container--default .select2-selection--multiple .select2-selection__rendered{box-sizing:border-box;list-style:none;margin:0;padding:0 5px;width:100%;}.select2-container--default .select2-selection--multiple .select2-selection__placeholder{color:#999;margin-top:5px;float:left;}.select2-container--default .select2-selection--multiple .select2-selection__clear{cursor:pointer;float:right;font-weight:bold;margin-top:5px;margin-right:10px;}.select2-container--default .select2-selection--multiple .select2-selection__choice{background-color:#e4e4e4;border:1px solid #aaa;border-radius:4px;cursor:default;float:left;margin-right:5px;margin-top:5px;padding:0 5px;}.select2-container--default .select2-selection--multiple .select2-selection__choice__remove{color:#999;cursor:pointer;display:inline-block;font-weight:bold;margin-right:2px;}.select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover{color:#333;}.select2-container--default[dir="rtl"] .select2-selection--multiple .select2-selection__choice,.select2-container--default[dir="rtl"] .select2-selection--multiple .select2-selection__placeholder{float:right;}.select2-container--default[dir="rtl"] .select2-selection--multiple .select2-selection__choice{margin-left:5px;margin-right:auto;}.select2-container--default[dir="rtl"] .select2-selection--multiple .select2-selection__choice__remove{margin-left:2px;margin-right:auto;}.select2-container--default.select2-container--focus .select2-selection--multiple{border:solid black 1px;outline:0;}.select2-container--default.select2-container--disabled .select2-selection--multiple{background-color:#eee;cursor:default;}.select2-container--default.select2-container--disabled .select2-selection__choice__remove{display:none;}.select2-container--default.select2-container--open.select2-container--above .select2-selection--single,.select2-container--default.select2-container--open.select2-container--above .select2-selection--multiple{border-top-left-radius:0;border-top-right-radius:0;}.select2-container--default.select2-container--open.select2-container--below .select2-selection--single,.select2-container--default.select2-container--open.select2-container--below .select2-selection--multiple{border-bottom-left-radius:0;border-bottom-right-radius:0;}.select2-container--default .select2-search--dropdown .select2-search__field{border:1px solid #aaa;}.select2-container--default .select2-search--inline .select2-search__field{background:transparent;border:none;outline:0;}.select2-container--default .select2-results>.select2-results__options{max-height:200px;overflow-y:auto;}.select2-container--default .select2-results__option[role=group]{padding:0;}.select2-container--default .select2-results__option[aria-disabled=true]{color:#999;}.select2-container--default .select2-results__option[aria-selected=true]{background-color:#ddd;}.select2-container--default .select2-results__option .select2-results__option{padding-left:1em;}.select2-container--default .select2-results__option .select2-results__option .select2-results__group{padding-left:0;}.select2-container--default .select2-results__option .select2-results__option .select2-results__option{margin-left:-1em;padding-left:2em;}.select2-container--default .select2-results__option .select2-results__option .select2-results__option .select2-results__option{margin-left:-2em;padding-left:3em;}.select2-container--default .select2-results__option .select2-results__option .select2-results__option .select2-results__option .select2-results__option{margin-left:-3em;padding-left:4em;}.select2-container--default .select2-results__option .select2-results__option .select2-results__option .select2-results__option .select2-results__option .select2-results__option{margin-left:-4em;padding-left:5em;}.select2-container--default .select2-results__option .select2-results__option .select2-results__option .select2-results__option .select2-results__option .select2-results__option .select2-results__option{margin-left:-5em;padding-left:6em;}.select2-container--default .select2-results__option--highlighted[aria-selected]{background-color:#5897fb;color:white;}.select2-container--default .select2-results__group{cursor:default;display:block;padding:6px;}.select2-container--classic .select2-selection--single{background-color:#f6f6f6;border:1px solid #aaa;border-radius:4px;outline:0;background-image:-webkit-linear-gradient(top, #ffffff 50%, #eeeeee 100%);background-image:-o-linear-gradient(top, #ffffff 50%, #eeeeee 100%);background-image:linear-gradient(to bottom, #ffffff 50%, #eeeeee 100%);background-repeat:repeat-x;filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#ffffff', endColorstr='#eeeeee', GradientType=0);}.select2-container--classic .select2-selection--single:focus{border:1px solid #5897fb;}.select2-container--classic .select2-selection--single .select2-selection__rendered{color:#444;line-height:28px;}.select2-container--classic .select2-selection--single .select2-selection__clear{cursor:pointer;float:right;font-weight:bold;margin-right:10px;}.select2-container--classic .select2-selection--single .select2-selection__placeholder{color:#999;}.select2-container--classic .select2-selection--single .select2-selection__arrow{background-color:#ddd;border:none;border-left:1px solid #aaa;border-top-right-radius:4px;border-bottom-right-radius:4px;height:26px;position:absolute;top:1px;right:1px;width:20px;background-image:-webkit-linear-gradient(top, #eeeeee 50%, #cccccc 100%);background-image:-o-linear-gradient(top, #eeeeee 50%, #cccccc 100%);background-image:linear-gradient(to bottom, #eeeeee 50%, #cccccc 100%);background-repeat:repeat-x;filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#eeeeee', endColorstr='#cccccc', GradientType=0);}.select2-container--classic .select2-selection--single .select2-selection__arrow b{border-color:#888 transparent transparent transparent;border-style:solid;border-width:5px 4px 0 4px;height:0;left:50%;margin-left:-4px;margin-top:-2px;position:absolute;top:50%;width:0;}.select2-container--classic[dir="rtl"] .select2-selection--single .select2-selection__clear{float:left;}.select2-container--classic[dir="rtl"] .select2-selection--single .select2-selection__arrow{border:none;border-right:1px solid #aaa;border-radius:0;border-top-left-radius:4px;border-bottom-left-radius:4px;left:1px;right:auto;}.select2-container--classic.select2-container--open .select2-selection--single{border:1px solid #5897fb;}.select2-container--classic.select2-container--open .select2-selection--single .select2-selection__arrow{background:transparent;border:none;}.select2-container--classic.select2-container--open .select2-selection--single .select2-selection__arrow b{border-color:transparent transparent #888 transparent;border-width:0 4px 5px 4px;}.select2-container--classic.select2-container--open.select2-container--above .select2-selection--single{border-top:none;border-top-left-radius:0;border-top-right-radius:0;background-image:-webkit-linear-gradient(top, #ffffff 0%, #eeeeee 50%);background-image:-o-linear-gradient(top, #ffffff 0%, #eeeeee 50%);background-image:linear-gradient(to bottom, #ffffff 0%, #eeeeee 50%);background-repeat:repeat-x;filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#ffffff', endColorstr='#eeeeee', GradientType=0);}.select2-container--classic.select2-container--open.select2-container--below .select2-selection--single{border-bottom:none;border-bottom-left-radius:0;border-bottom-right-radius:0;background-image:-webkit-linear-gradient(top, #eeeeee 50%, #ffffff 100%);background-image:-o-linear-gradient(top, #eeeeee 50%, #ffffff 100%);background-image:linear-gradient(to bottom, #eeeeee 50%, #ffffff 100%);background-repeat:repeat-x;filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#eeeeee', endColorstr='#ffffff', GradientType=0);}.select2-container--classic .select2-selection--multiple{background-color:white;border:1px solid #aaa;border-radius:4px;cursor:text;outline:0;}.select2-container--classic .select2-selection--multiple:focus{border:1px solid #5897fb;}.select2-container--classic .select2-selection--multiple .select2-selection__rendered{list-style:none;margin:0;padding:0 5px;}.select2-container--classic .select2-selection--multiple .select2-selection__clear{display:none;}.select2-container--classic .select2-selection--multiple .select2-selection__choice{background-color:#e4e4e4;border:1px solid #aaa;border-radius:4px;cursor:default;float:left;margin-right:5px;margin-top:5px;padding:0 5px;}.select2-container--classic .select2-selection--multiple .select2-selection__choice__remove{color:#888;cursor:pointer;display:inline-block;font-weight:bold;margin-right:2px;}.select2-container--classic .select2-selection--multiple .select2-selection__choice__remove:hover{color:#555;}.select2-container--classic[dir="rtl"] .select2-selection--multiple .select2-selection__choice{float:right;}.select2-container--classic[dir="rtl"] .select2-selection--multiple .select2-selection__choice{margin-left:5px;margin-right:auto;}.select2-container--classic[dir="rtl"] .select2-selection--multiple .select2-selection__choice__remove{margin-left:2px;margin-right:auto;}.select2-container--classic.select2-container--open .select2-selection--multiple{border:1px solid #5897fb;}.select2-container--classic.select2-container--open.select2-container--above .select2-selection--multiple{border-top:none;border-top-left-radius:0;border-top-right-radius:0;}.select2-container--classic.select2-container--open.select2-container--below .select2-selection--multiple{border-bottom:none;border-bottom-left-radius:0;border-bottom-right-radius:0;}.select2-container--classic .select2-search--dropdown .select2-search__field{border:1px solid #aaa;outline:0;}.select2-container--classic .select2-search--inline .select2-search__field{outline:0;}.select2-container--classic .select2-dropdown{background-color:white;border:1px solid transparent;}.select2-container--classic .select2-dropdown--above{border-bottom:none;}.select2-container--classic .select2-dropdown--below{border-top:none;}.select2-container--classic .select2-results>.select2-results__options{max-height:200px;overflow-y:auto;}.select2-container--classic .select2-results__option[role=group]{padding:0;}.select2-container--classic .select2-results__option[aria-disabled=true]{color:grey;}.select2-container--classic .select2-results__option--highlighted[aria-selected]{background-color:#3875d7;color:white;}.select2-container--classic .select2-results__group{cursor:default;display:block;padding:6px;}.select2-container--classic.select2-container--open .select2-dropdown{border-color:#5897fb;}
                </style>
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
