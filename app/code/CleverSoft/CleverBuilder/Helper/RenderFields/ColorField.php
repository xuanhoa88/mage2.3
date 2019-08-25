<?php

namespace CleverSoft\CleverBuilder\Helper\RenderFields;
/**
 *
 * The common base class for text input type fields.
 */
class ColorField extends \CleverSoft\CleverBuilder\Helper\RenderFields\BaseFieldAbs
{
    /*
     *
     */
    protected function get_input_classes()
    {
        return array('widefat', 'cleversoft-color-input', 'form-control', 'add-click-ui-color');
    }

    /**
     * The data attributes to be added to the input element.
     */
    protected function get_input_data_attributes()
    {
        return array();
    }

    /*
     * return string of field's attributes
     */
    protected function render_data_attributes($data_attributes)
    {
        $attr_string = '';
        foreach ($data_attributes as $name => $value) {
            $attr_string = ' data-' . ($name) . '="' . ($value) . '"';
        }
        echo $attr_string;
    }

    /*
     * return html of the field
     */
    protected function render_field($value, $instance)
    {
        $id = uniqid();
        $storeId = $this->_storeManager->getStore()->getId();
        $panelId = '#panel-'.$storeId.'-'.$instance['widget_id'];
        ?>
        <input <?php if(isset($this->field_options['depends'])) : ?> data-depends="<?php echo htmlspecialchars(json_encode($this->field_options['depends']), ENT_QUOTES, 'UTF-8') ?>" <?php endif; ?> <?php if(isset($this->field_options['indepence'])) : ?> data-indepence="<?php echo htmlspecialchars(json_encode($this->field_options['indepence']), ENT_QUOTES, 'UTF-8') ?>" <?php endif; ?> id="<?php echo $id ?>" type="text" <?php if(isset($this->field_options['code'])):?>data-code="<?=$this->field_options['code']?>"<?php endif;?> <?php if(isset($this->field_options['mode'])):?>data-mode="<?=$this->field_options['mode']?>"<?php endif;?> <?php if(isset($this->field_options['attribute_type'])):?>data-attribute="<?=$this->field_options['attribute_type']?>"<?php endif;?> data-panel="<?php echo $panelId?>" name="<?php echo ( $this->element_name ) ?>" value="<?php echo __( $value ) ?>" class="mColorPicker">
        <script>
            require([
                'jquery',
                "colorPickerLib",
            ], function($, coP) {
                $(document).ready(function () {
                    $(document).on("change" , "#<?php echo $id ?>" , function(){
                        var input_el = $(this);
                        var panelId = input_el.attr('data-panel');
                        var $class = "<?=isset($this->field_options['element_class']) ? $this->field_options['element_class'] : ''?>";
                        var $data_code = "<?=isset($this->field_options['code']) ? $this->field_options['code'] : ''?>";
                        if($class) {
                            var previewEl = $(".cleversoft-panels-iframe-preview").contents().find("<?=$panelId?> ."+$class).first();
                        } else {
                            var previewEl = $(".cleversoft-panels-iframe-preview").contents().find("<?=$panelId?> .global-element").first();
                        }
                        if (input_el.attr('data-code')) {
                            if (input_el.attr('data-mode') == 'hover') {
                                previewEl.hover(function(){
                                    previewEl.css(input_el.attr('data-code'),input_el.val());
                                }, function(){ 
                                    var normalStyle = $('input[data-code="'+input_el.attr('data-code')+'"][data-panel="'+panelId+'"][data-mode="normal"]').val();
                                    previewEl.css(input_el.attr('data-code'),normalStyle);
                                });  
                            } else {
                                previewEl.css(input_el.attr('data-code'),input_el.val());
                                previewEl.hover(function(){
                                    var normalStyle = $('input[data-code="'+input_el.attr('data-code')+'"][data-panel="'+panelId+'"][data-mode="hover"]').val();
                                    if (normalStyle) {
                                        previewEl.css(input_el.attr('data-code'),normalStyle);
                                    } else {
                                        previewEl.css(input_el.attr('data-code'),input_el.val());
                                    }
                                }, function(){ 
                                    previewEl.css(input_el.attr('data-code'),input_el.val());
                                });
                            }
                        }
                    });
                    $('#<?=$id?>' ).attr("data-hex", true).width("250px").mColorPicker({imageFolder: "<?= $this->getViewFileUrl('CleverSoft_CleverBuilder::js/lib/mcolorpicker/images')?>/"});
                });
            });
        </script>
        <?php
    }

    protected function sanitize_field_input($value, $instance)
    {
        $sanitized_value = ($value);
        return $sanitized_value;
    }
}
