<?php
namespace CleverSoft\CleverBuilder\Helper\RenderFields;
/**
 *
 * The common base class for text input type fields.
 */
class UnitField extends \CleverSoft\CleverBuilder\Helper\RenderFields\BaseFieldAbs {
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
                    <input type="text" class="<?php echo $id?>-size" value="<?php echo $value?>">
                </div>
                <select class="<?php echo $id?>-unit">
                    <option value="px">px</option>
                    <option value="%">%</option>
                </select>
                <input type="hidden" id=<?php echo $id?> name="<?php echo $this->element_name?>" value="<?php echo $value?>">
            </div>
        </div>
        <script>
            require([
                'jquery'
            ], function($) {
                var unit = $('.<?php echo $id?>-unit').val();
                var value = '<?php echo $value?>'.replace(unit,'');

                $('.<?php echo $id?>-size').val(value);
                function changeVal() {
                    var unit = $('.<?php echo $id?>-unit').val();
                    var size = $('.<?php echo $id?>-size').val() + unit;
                    $('#<?php echo $id?>').val(size);

                    var $content = size;
                    var $value = "<?=$value?>";
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
                            case 'html':
                                classEl.html($content);
                                break;
                            default:

                        }
                    }
                }
                $('.<?php echo $id?>-size').change(function () {
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
        </style>
    <?php
    }

    protected function sanitize_field_input( $value, $instance ) {
        $sanitized_value = ( $value );
        return $sanitized_value;
    }
}
