<?php

namespace CleverSoft\CleverBuilder\Helper\RenderFields;

/**
 *
 * The common base class for text input type fields.
 */
class ButtonGroupField extends \CleverSoft\CleverBuilder\Helper\RenderFields\BaseFieldAbs {
    /*
     *
     */
    protected function get_input_classes() {
        return array( 'widefat', 'cleversoft-widget-input' ,'form-control','button-group');
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
        <div class="clever-button-group">
            <?php
            if ( is_array($this->field_options['values'])) {
                foreach($this->field_options['values'] as $key=>$val) {
                    ?>
                    <button data-id="#<?php echo $id ?>" type="button" class="btn btn-default <?php echo $val['value'] ?> update-value-input-hidden-clicked <?php echo $value == $val['value'] ? 'selected' : '' ?>" value="<?php echo $val['value']; ?>"><?php
                        if (isset($val['icon']))  {
                            ?>
                            <span class="<?php echo $val['icon']; ?>"></span>
                            <?php
                        } else  echo __($val['label']);

                        ?> </button>
                    <?php
                }
            }
            ?>
        </div>

        <input <?php if(isset($this->field_options['depends'])) : ?> data-depends="<?php echo htmlspecialchars(json_encode($this->field_options['depends']), ENT_QUOTES, 'UTF-8') ?>" <?php endif; ?> <?php if(isset($this->field_options['indepence'])) : ?> data-indepence="<?php echo htmlspecialchars(json_encode($this->field_options['indepence']), ENT_QUOTES, 'UTF-8') ?>" <?php endif; ?> type="hidden" name="<?php echo ( $this->element_name ) ?>" id="<?php echo $id ?>" value="<?php echo ( $value ) ?>"
            <?php $this->render_data_attributes( $this->get_input_data_attributes() ) ?> />
        <?php $this->render_after_field( $value, $instance); ?>
        <script>
            require(['jquery', 'jquery/ui'], function($){ 
                $('button[data-id="#<?php echo $id ?>"]').on('click', function (e) {
                    $('button[data-id="#<?php echo $id ?>"]').removeClass('selected');
                    $(this).addClass('selected');
                    var $content = $(this).val();
                    $('#<?=$id?>').val($content);
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
                                break;
                            default:

                        }
                    }
                    
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
