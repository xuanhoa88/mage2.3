<?php
namespace CleverSoft\CleverBuilder\Helper\RenderFields;
/**
 *
 * The common base class for text input type fields.
 */
class MediaField extends \CleverSoft\CleverBuilder\Helper\RenderFields\BaseFieldAbs {
    /*
     *
     */
    protected function get_input_classes() {
        return array( 'widefat', 'cleversoft-media-input' ,'form-control','add-click-ui-media');
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
        $host = $value ? parse_url($value)['host'] : '';
        $baseUrl = parse_url($this->_storeManager->getStore()->getBaseUrl())['host'];
        if ($host != $baseUrl) {
            $value = str_replace($host, $baseUrl, $value);
        }
        ?>
        <div class="cs-image-selector" onload="cleverMediabrowserUtility.attachIconLoaded('<?php echo $id?>')">
            <div id="current-image-cleverbuilder-image-<?php echo $id?>" class="current-image" style="display:none;height: 300px;width: 300px;background-size: cover; padding-bottom: 15px">
            </div>
            <button id="remove-cleverbuilder-image-<?php echo $id?>" type="button" class="action-remove-image btn btn-default" style="display: none" onclick="cleverMediabrowserUtility.removeImage(this,'<?php echo $id?>');var mode='<?php echo isset($this->field_options['mode']) ? $this->field_options['mode'] : ''?>';removeIconToItemPreviewContent(mode);">
                <span>Remove Image</span>
            </button>
            <button type="button" class="action-add-image btn btn-default" onclick="cleverMediabrowserUtility.openDialog('cleverbuilder/wysiwyg_images/index/target_element_id/cleverbuilder-image-<?php echo $id?>/')">
                <span>Insert Image</span>
            </button>

            <input id="cleverbuilder-image-<?php echo $id?>" type="hidden" <?php if(isset($this->field_options['code'])):?>data-code="<?=$this->field_options['code']?>"<?php endif;?> <?php if(isset($this->field_options['mode'])):?>data-mode="<?=$this->field_options['mode']?>"<?php endif;?> <?php if(isset($this->field_options['attribute_type'])):?>data-attribute="<?=$this->field_options['attribute_type']?>"<?php endif;?> data-panel="<?php echo $panelId?>" name="<?php echo $this->element_name?>" value="<?php echo $value?>" onchange="cleverMediabrowserUtility.attachIconToItemHeading(this,this.value);attachIconToItemPreviewContent(this,this.value);">
        </div>
        <style type="text/css">
            .modal-slide.magento._show {
                z-index: 999999 !important;
            }
            .modal-popup.prompt._show {
                z-index: 999999 !important;
            }
        </style>
        <script type="text/x-magento-init">
            {
                "*": {
                    "imageBrowser" : {}
                }
            }
        </script>
        <?php if($value):?>
            <script>
                cleverMediabrowserUtility.attachIconLoaded('<?php echo $id?>');
            </script>
        <?php endif;?>
        <script>
            var attachIconToItemPreviewContent = function(textEl, value) {
                    require(['jquery', 'jquery/ui'], function($){
                        var $content = value ? "url("+value+")" : '';
                        var $attribute_type = $(textEl).attr('data-attribute');
                        var $code = $(textEl).attr('data-code');
                        var $panelId = $(textEl).attr('data-panel');
                        var $class = "<?=isset($this->field_options['element_class']) ? $this->field_options['element_class'] : ''?>";
                        if($class) {
                            var previewEl = $(".cleversoft-panels-iframe-preview").contents().find("<?=$panelId?> ."+$class).first();
                        } else {
                            var previewEl = $(".cleversoft-panels-iframe-preview").contents().find("<?=$panelId?> .global-element").first();
                        }
                        if ($attribute_type && $code) {
                            switch($attribute_type) {
                                case 'style':
                                    if ($(textEl).attr('data-mode') == 'hover') {
                                        previewEl.hover(function(){
                                            if ($content) {
                                                $(this).css($code,$content);
                                            }
                                        }, function(){
                                            var normalStyle = $('input[type="hidden"][data-code="'+$code+'"][data-panel="'+$panelId+'"][data-mode="normal"]').val() ? "url("+$('input[type="hidden"][data-code="'+$code+'"][data-panel="'+$panelId+'"][data-mode="normal"]').val()+")" : '';
                                            $(this).css($code,normalStyle);
                                        });
                                    } else {
                                        previewEl.css($code,$content);
                                    }
                                    break;
                                case 'attribute':
                                    previewEl.attr($code,value);
                                    break;    
                                default:

                            }
                        }
                    });
                }
                var removeIconToItemPreviewContent = function(mode, value) {
                    require(['jquery', 'jquery/ui'], function($){
                        var $panelId = "<?php echo $panelId?>";
                        var $attribute_type = "<?php echo isset($this->field_options['attribute_type']) ? $this->field_options['attribute_type'] : ''?>";
                        var $code = "<?php echo isset($this->field_options['code']) ? $this->field_options['code'] : ''?>";
                        var $mode = mode;
                        var $class = "<?=isset($this->field_options['element_class']) ? $this->field_options['element_class'] : ''?>";
                        if($class) {
                            var previewEl = $(".cleversoft-panels-iframe-preview").contents().find("<?=$panelId?> ."+$class).first();
                        } else {
                            var previewEl = $(".cleversoft-panels-iframe-preview").contents().find("<?=$panelId?> .global-element").first();
                        }

                        if ($attribute_type && $code) {
                            switch($attribute_type) {
                                case 'style':
                                    if ($mode == 'hover') {
                                        previewEl.hover(function(){
                                            var normalStyle = $('input[type="hidden"][data-code="'+$code+'"][data-panel="'+$panelId+'"][data-mode="normal"]').val() ? "url("+$('input[type="hidden"][data-code="'+$code+'"][data-panel="'+$panelId+'"][data-mode="normal"]').val()+")" : '';
                                            if (normalStyle) {
                                                $(this).css($code,normalStyle);
                                            } else {
                                                $(this).css($code,'unset');
                                            }
                                        }, function(){
                                        });
                                    } else {
                                        previewEl.css($code,'unset');
                                        previewEl.hover(function(){
                                            var normalStyle = $('input[type="hidden"][data-code="'+$code+'"][data-panel="'+$panelId+'"][data-mode="hover"]').val() ? "url("+$('input[type="hidden"][data-code="'+$code+'"][data-panel="'+$panelId+'"][data-mode="hover"]').val()+")" : '';
                                            if (normalStyle) {
                                                $(this).css($code,normalStyle);
                                            } else {
                                                $(this).css($code,'unset');
                                            }
                                        }, function(){
                                            previewEl.css($code,'unset');
                                        });
                                    }
                                    break;
                                case 'attribute':
                                    previewEl.attr($code,"<?=$this->getViewFileUrl("CleverSoft_CleverBuilder::images/placeholder.png")?>");
                                    break;    
                                default:

                            }
                        }
                    });
                }
            </script>
    <?php
    }

    protected function sanitize_field_input( $value, $instance ) {
        $sanitized_value = ( $value );
        return $sanitized_value;
    }
}
