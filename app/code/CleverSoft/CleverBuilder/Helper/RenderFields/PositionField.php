<?php
namespace CleverSoft\CleverBuilder\Helper\RenderFields;
/**
 *
 * The common base class for text input type fields.
 */
class PositionField extends \CleverSoft\CleverBuilder\Helper\RenderFields\BaseFieldAbs {
    /*
     *
     */
    protected function get_input_classes() {
        return array( 'widefat', 'cleversoft-position-input' ,'form-control','add-click-ui-position');
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
        $storeId = $this->_storeManager->getStore()->getId();
        $panelId = '#panel-'.$storeId.'-'.$instance['widget_id'];
        ?>
        <div class="style-field style-field-measurement">
            <div class="style-input-wrapper">
                <div class="measurement-inputs">
                    <div class="measurement-wrapper">
                        <input type="text" class="<?php echo $id?>-value <?php echo $id?>-top" placeholder="Top" value="<?php echo isset(explode(' ',$value)[0]) ? explode(' ',$value)[0] : ''?>">
                    </div>
                    <div class="measurement-wrapper">
                        <input type="text" class="<?php echo $id?>-value <?php echo $id?>-right" placeholder="Right" value="<?php echo isset(explode(' ',$value)[1]) ? explode(' ',$value)[1] : ''?>">
                    </div>
                    <div class="measurement-wrapper">
                        <input type="text" class="<?php echo $id?>-value <?php echo $id?>-bottom" placeholder="Bottom" value="<?php echo isset(explode(' ',$value)[2]) ? explode(' ',$value)[2] : ''?>">
                    </div>
                    <div class="measurement-wrapper">
                        <input type="text" class="<?php echo $id?>-value <?php echo $id?>-left" placeholder="Left" value="<?php echo isset(explode(' ',$value)[3]) ? explode(' ',$value)[3] : ''?>">
                    </div>
                </div>
                <select class="<?php echo $id?>-unit <?php echo $id?>-unit-multiple">
                    <option value="px">px</option>
                    <option value="%">%</option>
                    <option value="in">in</option>
                    <option value="cm">cm</option>
                    <option value="mm">mm</option>
                    <option value="em">em</option>
                    <option value="ex">ex</option>
                    <option value="pt">pt</option>
                    <option value="pc">pc</option>
                    <option value="rem">rem</option>
                </select>
                <input type="hidden" id=<?php echo $id?> name="<?php echo $this->element_name?>" value="<?php echo $value?>">
            </div>
        </div>
        <script>
            require([
                'jquery'
            ], function($) {
                var unit = $('.<?php echo $id?>-unit').val();
                var top = '<?php echo isset(explode(' ',$value)[0]) ? explode(' ',$value)[0] : ''?>'.replace(unit,'');
                var right = '<?php echo isset(explode(' ',$value)[1]) ? explode(' ',$value)[1] : ''?>'.replace(unit,'');
                var bottom = '<?php echo isset(explode(' ',$value)[2]) ? explode(' ',$value)[2] : ''?>'.replace(unit,'');
                var left = '<?php echo isset(explode(' ',$value)[3]) ? explode(' ',$value)[3] : ''?>'.replace(unit,'');
                $('.<?php echo $id?>-top').val(top);
                $('.<?php echo $id?>-right').val(right);
                $('.<?php echo $id?>-bottom').val(bottom);
                $('.<?php echo $id?>-left').val(left);
                function changeVal() {
                    var unit = $('.<?php echo $id?>-unit').val();
                    var top = $('.<?php echo $id?>-top').val() + unit;
                    var right = $('.<?php echo $id?>-right').val() + unit;
                    var bottom = $('.<?php echo $id?>-bottom').val() + unit;
                    var left = $('.<?php echo $id?>-left').val() + unit;
                    var margin = top + ' ' + right + ' ' + bottom + ' ' + left;
                    $('#<?php echo $id?>').val(margin);

                    var $content = margin;
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
                                if($data_code){
                                    <?php if (isset($this->field_options['unit']) && $this->field_options['unit']):?>
                                        classEl.css('<?=isset($this->field_options['code']) ? $this->field_options['code'] : ''?>',$content+'<?=$this->field_options['unit']?>');
                                    <?php else:?>
                                        classEl.css('<?=isset($this->field_options['code']) ? $this->field_options['code'] : ''?>',$content);
                                    <?php endif;?>
                                }
                                break;
                            case 'class':
                                if (window.class<?php echo $id?>) {
                                  classEl.removeClass(window.class<?php echo $id?>);
                                }
                                classEl.addClass($content);
                                window.class<?php echo $id?> = $content;
                                break;
                            case 'attribute':
                                classEl.attr($data_code,$content);
                            case 'html':
                                classEl.html($content);
                                break;
                            default:

                        }
                    }
                }
                $('.<?php echo $id?>-value').change(function () {
                    changeVal();
                });
                $('.<?php echo $id?>-unit').change(function () {
                    changeVal();
                });
            });
        </script>
        <style>
            .measurement-inputs {
                overflow: auto;
                margin: 0 -3px 4px;
            }
            .measurement-wrapper {
                box-sizing: border-box;
                float: left;
                width: 25%;
                padding: 0 3px;
            }
            .<?php echo $id?>-top {
                box-shadow: inset 0 3px 1px rgba(10, 10, 10, 0.8);
            }
            .<?php echo $id?>-right {
                box-shadow: inset -3px 0 2px rgba(10, 10, 10, 0.8);
            }
            .<?php echo $id?>-bottom {
                box-shadow: inset 0 -3px 1px rgba(10, 10, 10, 0.8);
            }
            .<?php echo $id?>-left {
                box-shadow: inset 3px 0 2px rgba(10, 10, 10, 0.8);
            }
            .<?php echo $id?>-value {
                font-size: 12px;
                border-width: 1px;
                display: block;
                max-width: 100%;
            }

        </style>
    <?php
    }

    protected function sanitize_field_input( $value, $instance ) {
        $sanitized_value = ( $value );
        return $sanitized_value;
    }
}
