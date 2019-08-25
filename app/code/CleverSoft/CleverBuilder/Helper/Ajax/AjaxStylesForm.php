<?php
/**
 * @category    CleverSoft
 * @package     CleverLayeredNavigation
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author      ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */
namespace CleverSoft\CleverBuilder\Helper\Ajax;

class AjaxStylesForm extends \CleverSoft\CleverBuilder\Helper\Styles
{

    /* Get current customer */

    /* Check to visible panel button */
    public function getHtml() {
        $post = $this->_getRequest()->getParams();
        $type = $post['type'];
        if ( ! in_array( $type, array( 'row', 'cell', 'widget' ) ) ) {
            exit();
        }

        $current = isset( $post['style'] ) ? $post['style'] : array();
        $post_id = empty( $post['postId'] ) ? 0 : $post['postId'];
        $widget_id = isset( $post['widget_id'] ) ? $post['widget_id'] : '';
        $args = ! empty( $post['args'] ) ? json_decode( stripslashes( $post['args'] ), true ) : array();

        switch ( $type ) {
            case 'row':
                $this->renderStylesFields( 'row', '<h3>' . __( 'Row Styles') . '</h3>', '', $current, $post_id, $args, $widget_id);
                break;
            case 'widget':
                $this->renderStylesFields( 'widget', '<h3>' . __( 'Widget Styles') . '</h3>', '', $current, $post_id, $args, $widget_id);
                break;
        }

        die();
    }

    protected function sort_fields( $a, $b ) {
        return ( ( isset( $a['priority'] ) ? $a['priority'] : 10 ) > ( isset( $b['priority'] ) ? $b['priority'] : 10 ) ) ? 1 : - 1;
    }

    public function renderStylesFields( $section, $before = '', $after = '', $current = array(), $post_id = 0, $args = array(), $widget_id) {
        $fields = array();
        $function = $section . '_style_fields';
        $fields = $this->$function($fields);
        if ( empty( $fields ) ) {
            return false;
        }

        $groups = array(
            'advanced' => array(
                'name'     => __( 'Advanced'),
                'code'     => __( 'advanced'),
                'priority' => 1
            ),
            'color' => array(
                'name'     => __( 'Color'),
                'code'     => __( 'color'),
                'priority' => 2
            ),
            'background' => array(
                'name'     => __( 'Background'),
                'code'     => __( 'background'),
                'priority' => 3
            ),
            'border'     => array(
                'name'     => __( 'Border'),
                'code'     => __( 'border'),
                'priority' => 4
            ),
            'responsive'     => array(
                'name'     => __( 'Responsive'),
                'code'     => __( 'responsive'),
                'priority' => 5
            ),
            'customcss'     => array(
                'name'     => __( 'Custom CSS'),
                'code'     => __( 'customcss'),
                'priority' => 6
            ),
            'rowstyle'     => array(
                'name'     => __( 'Row Styles'),
                'code'     => __( 'rowstyle'),
                'priority' => 7
            ),
            'cellstyle'     => array(
                'name'     => __( 'Cell Styles'),
                'code'     => __( 'cellstyle'),
                'priority' => 8
            )
        );

        // Check if we need a default group
        foreach ( $fields as $field_id => $field ) {
            if ( empty( $field['group'] ) || $field['group'] == 'theme' ) {
                if ( empty( $groups['theme'] ) ) {
                    $groups['theme'] = array(
                        'name'     => __( 'Theme'),
                        'priority' => 10
                    );
                }
                $fields[ $field_id ]['group'] = 'theme';
            }
        }

        // Sort the style fields and groups by priority
        uasort( $fields, array( $this, 'sort_fields' ) );
        uasort( $groups, array( $this, 'sort_fields' ) );

        echo $before;

        $group_counts = array();
        foreach ( $fields as $field_id => $field ) {
            if ( empty( $group_counts[ $field['group'] ] ) ) {
                $group_counts[ $field['group'] ] = 0;
            }
            $group_counts[ $field['group'] ] ++;
        }

        foreach ( $groups as $group_id => $group ) {

            if ( empty( $group_counts[ $group_id ] ) ) {
                continue;
            }

            ?>
            <div class="style-section-wrapper">
                <div class="style-section-head">
                    <h4><?php echo $group['name'] ?></h4>
                </div>
   
                <div class="style-section-fields" style="display: none">
                    <?php if($group['code'] == 'background' || $group['code'] == 'border' || $group['code'] == 'color'):?>
                        <div class="style-section-background">
                            <div class="tab active" data-mode="normal">
                                <div class="control-content">
                                    Normal
                                </div>
                            </div>
                            <div class="tab" data-mode="hover">
                                <div class="control-content">
                                    Hover
                                </div>
                            </div>
                        </div>
                    <?php endif;?> 
                    <?php
                    foreach ( $fields as $field_id => $field ) {
                        $default = isset( $field[ 'default' ] ) ? $field[ 'default' ] : false;

                        if ( $field['group'] == $group_id ) {
                            ?>
                            <div class="style-field-wrapper" <?php echo isset($field['mode']) ? 'data-mode="'.$field['mode'].'"' : ''?> <?php echo isset($field['device']) ? 'data-device="'.$field['device'].'"' : ''?> <?php echo isset($field['device']) || isset($field['mode']) ? 'style="display:none"' : ''?> >
                                <label><?php echo $field['name'] ?></label>
                                <div
                                    class="style-field style-field-<?php echo $field['type'] ?>">
                                    <?php $this->renderStyleField( $field, isset( $current[ $field_id ] ) ? $current[ $field_id ] : $default, $field_id, $widget_id) ?>
                                </div>
                            </div>
                        <?php

                        }

                    }
                    ?>
                    <?php if($group['code'] == 'advanced' || $group['code'] == 'customcss'):?>
                        <a class="config-editor-mode" title="Toggle mobile mode" data-mode="mobile">
                            <span class="dashicons dashicons-smartphone"></span>
                        </a>
                        <a class="config-editor-mode" title="Toggle tablet mode" data-mode="tablet">
                            <span class="dashicons dashicons-tablet"></span>
                        </a>
                        <a class="config-editor-mode" title="Toggle desktop mode" data-mode="desktop">
                            <span class="dashicons dashicons-desktop"></span>
                        </a>
                    <?php endif;?>
                    <script>
                        require(['jquery', 'jquery/ui'], function($){ 
                            var device_active = $('.cs-sidebar-tools').find('a.cs-active').attr('data-mode');
                            $('.config-editor-mode[data-mode="'+device_active+'"]').addClass('cs-active');
                            $('.style-section-wrapper .style-field-wrapper[data-device="'+device_active+'"]').show();


                            $('.config-editor-mode').click(function(e) {
                                var button = $(e.currentTarget);
                                $('.config-editor-mode').not(button).removeClass('cs-active');
                                button.addClass('cs-active');

                                $('.live-editor-'+button.data('mode')).trigger('click');
                                $('.style-section-wrapper .style-field-wrapper[data-device]').hide();
                                $('.style-section-wrapper .style-field-wrapper[data-device="'+button.data('mode')+'"]').show();
                            });

                            var mode_active = $('.style-section-wrapper .style-section-background').find('.active').attr('data-mode');
                            $('.style-section-wrapper .style-field-wrapper[data-mode="'+mode_active+'"]').show();

                            $('.style-section-wrapper .style-section-background .tab').click(function(e) {
                                var tab = $(e.currentTarget);
                                tab.closest('.style-section-fields').find('.style-section-background .tab').not(tab).removeClass('active');
                                tab.addClass('active');
                                tab.closest('.style-section-fields').find('.style-field-wrapper[data-mode]').hide();
                                tab.closest('.style-section-fields').find('.style-field-wrapper[data-mode="'+tab.data('mode')+'"]').show();
                            });
                        });
                    </script>
                </div>
            </div>
        <?php
        }

        echo $after;
    }

    function renderStyleField( $field, $current, $field_id, $widget_id) {
        $storeId = $this->_storeManager->getStore()->getId();
        $panelId = '#panel-'.$storeId.'-'.$widget_id;
        $field_name = 'style[' . $field_id . ']';
        $unit = 'px';
        if (isset(explode(' ',$current)[0])) {
            $unit = preg_replace('/\d+/u', '', explode(' ',$current)[0]);
        }
        echo '<div class="style-input-wrapper">';
        switch ( $field['type'] ) {
            case 'measurement' :

                if ( ! empty( $field['multiple'] ) ) {
                    ?>
                    <div class="measurement-inputs">
                        <div class="measurement-wrapper">
                            <input type="text" class="measurement-value measurement-top"
                                   placeholder="<?php echo __( 'Top') ?>"/>
                        </div>
                        <div class="measurement-wrapper">
                            <input type="text" class="measurement-value measurement-right"
                                   placeholder="<?php echo __( 'Right') ?>"/>
                        </div>
                        <div class="measurement-wrapper">
                            <input type="text" class="measurement-value measurement-bottom"
                                   placeholder="<?php echo __( 'Bottom') ?>"/>
                        </div>
                        <div class="measurement-wrapper">
                            <input type="text" class="measurement-value measurement-left"
                                   placeholder="<?php echo __( 'Left') ?>"/>
                        </div>
                    </div>
                <?php
                } else {
                    ?><input type="text" class="measurement-value measurement-value-single"/><?php
                }

                ?>
                <select
                    class="measurement-unit measurement-unit-<?php echo ! empty( $field['multiple'] ) ? 'multiple' : 'single' ?>">
                    <?php foreach ( $this->_dataHelper->measurements_list as $measurement ): ?>
                        <option
                            value="<?php echo $measurement  ?>" <?php echo $unit == $measurement ? "selected" :''?>><?php echo $measurement ?></option>
                    <?php endforeach ?>
                </select>
                <input type="hidden" <?php if(isset($field['code'])):?>data-code="<?=$field['code']?>"<?php endif;?> <?php if(isset($field['attribute_type'])):?>data-attribute="<?=$field['attribute_type']?>"<?php endif;?> <?php if(isset($field['mode'])):?>data-mode="<?=$field['mode']?>"<?php endif;?> data-panel="<?php echo $panelId?>" name="<?php echo __( $field_name ) ?>"
                       value="<?php echo __( $current ) ?>"/>
                <script>
                    require([
                        'jquery',
                    ], function($) {
                        $(document).ready(function () {
                            $('.measurement-unit option[selected]').attr('selected','selected');
                        });
                    });
                </script>
                <?php
                break;

            case 'color' :
                $id = uniqid();
                ?>
                <input id="<?php echo $id ?>" type="text" <?php if(isset($field['code'])):?>data-code="<?=$field['code']?>"<?php endif;?> <?php if(isset($field['mode'])):?>data-mode="<?=$field['mode']?>"<?php endif;?> <?php if(isset($field['attribute_type'])):?>data-attribute="<?=$field['attribute_type']?>"<?php endif;?> data-panel="<?php echo $panelId?>" name="<?php echo __( $field_name ) ?>" value="<?php echo __( $current ) ?>" class="mColorPicker">
                <script>
                    require([
                        'jquery',
                        "colorPickerLib",
                    ], function($, coP) {
                        $(document).ready(function () {
                            $('.style-field-color').each(function () {
                                var $$ = $(this);
                                var text = $$.find('input[type="text"]');

                                text.change(function() {
                                    var input_el = $(this);
                                    var panelId = input_el.attr('data-panel');
                                    var previewEl = $(".cleversoft-panels-iframe-preview").contents().find(panelId+" "+".global-element").first();
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
                            });
                            $('#<?=$id?>' ).attr("data-hex", true).width("250px").mColorPicker({imageFolder: "<?= $this->getViewFileUrl('CleverSoft_CleverBuilder::js/lib/mcolorpicker/images')?>/"});
                        });
                    });
                </script>
                <?php
                break;

            case 'image' :
                $image = false;
                $id = uniqid();
                if ( ! empty( $current ) ) {
                    $image = $current;
                }
                $host = $image ? parse_url($image)['host'] : '';
                $baseUrl = parse_url($this->_storeManager->getStore()->getBaseUrl())['host'];
                if ($host != $baseUrl) {
                    $image = str_replace($host, $baseUrl, $image);
                }
                ?>
                <div class="cs-image-selector">
                    <div id="current-image-cleverbuilder-image-<?php echo $id ?>" class="current-image" <?php if ( ! empty( $image ) ) {
                        echo 'style="background-image: url(' .  $image  . ');"';
                    } ?>>
                    </div>
                    <style type="text/css">
                        .modal-slide.magento._show {
                            z-index: 999999 !important;
                        }
                        .modal-popup.prompt._show {
                            z-index: 999999 !important;
                        }
                    </style>
                    <div class="select-image" onclick="cleverMediabrowserUtility.openDialog('<?php echo $this->_urlBuilder->getUrl('cleverbuilder/wysiwyg_images/index/target_element_id', ['_secure' => true]) ?>target_element_id/cleverbuilder-image-<?php echo $id  ?>/target_element/cleverbuilder-image')">
                        <?php echo __( 'Select Image') ?>
                    </div>
                    <input id="cleverbuilder-image-<?php echo $id ?>" type="hidden" <?php if(isset($field['code'])):?>data-code="<?=$field['code']?>"<?php endif;?> <?php if(isset($field['mode'])):?>data-mode="<?=$field['mode']?>"<?php endif;?> <?php if(isset($field['attribute_type'])):?>data-attribute="<?=$field['attribute_type']?>"<?php endif;?> data-panel="<?php echo $panelId?>"  name="<?php echo __( $field_name ) ?>"
                           value="<?php echo $image ?>" onchange="cleverMediabrowserUtility.attachIconToItemHeading(this,this.value);attachIconToItemPreview(this,this.value);"/>
                               
                </div>
                <a href="#" onclick="cleverMediabrowserUtility.removeImage($('#cleverbuilder-image-<?php echo $id ?>'),'<?php echo $id ?>'); var mode='<?php echo isset($field['mode']) ? $field['mode'] : ''?>';removeIconToItemPreview(mode);" class="remove-image"><?php echo __( 'Remove') ?></a>
                <script>
                    var attachIconToItemPreview = function(textEl, value) {
                        require(['jquery', 'jquery/ui'], function($){
                            var $content = value ? "url("+value+")" : '';
                            var $attribute_type = $(textEl).attr('data-attribute');
                            var $code = $(textEl).attr('data-code');
                            var $panelId = $(textEl).attr('data-panel');
                            var previewEl = $(".cleversoft-panels-iframe-preview").contents().find($panelId+" "+".global-element").first();
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
                                    default:

                                }
                            }
                        });
                    }
                    var removeIconToItemPreview = function(mode, value) {
                        require(['jquery', 'jquery/ui'], function($){
                            var $panelId = "<?php echo $panelId?>";
                            var $attribute_type = "<?php echo isset($field['attribute_type']) ? $field['attribute_type'] : ''?>";
                            var $code = "<?php echo isset($field['code']) ? $field['code'] : ''?>";
                            var $mode = mode;
                            var previewEl = $(".cleversoft-panels-iframe-preview").contents().find($panelId+" "+".global-element").first();

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
                                    default:

                                }
                            }
                        });
                    }
                </script>  
                <?php
                break;

            case 'url' :
            case 'text' :
                ?><input type="text" <?php if(isset($field['code'])):?>data-code="<?=$field['code']?>"<?php endif;?> <?php if(isset($field['unit'])):?>data-unit="<?=$field['unit']?>"<?php endif;?> <?php if(isset($field['attribute_type'])):?>data-attribute="<?=$field['attribute_type']?>"<?php endif;?> data-panel="<?php echo $panelId?>" name="<?php echo __( $field_name ) ?>"
                         value="<?php echo __( $current ) ?>" class="widefat" /><?php
                break;

            case 'checkbox' :
                $current = (bool) $current;
                ?>
                <label class="cs-checkbox-label">
                    <input type="checkbox" name="<?php echo __( $field_name ) ?>" <?php $this->_dataHelper->checked( $current ) ?> />
                    <?php echo isset( $field['label'] ) ? $field['label'] : __( 'Enabled') ?>
                </label>
                <?php
                break;

            case 'toggle':
                $id = uniqid();
                ?>
                <input name="<?php echo __( $field_name ) ?>" id="toggle-ios-<?php echo $id?>" class="toggle toggle-ios" type="checkbox" value="<?php echo $current?>" <?php echo $current ? 'checked' : ''?>/>
                <label class="toggle-btn" for="toggle-ios-<?php echo $id?>"></label>
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
                <?php
                break;   

            case 'select' :
                ?>
                <select <?php if(isset($field['code'])):?>data-code="<?=$field['code']?>"<?php endif;?> <?php if(isset($field['attribute_type'])):?>data-attribute="<?=$field['attribute_type']?>"<?php endif;?> <?php if(isset($field['mode'])):?>data-mode="<?=$field['mode']?>"<?php endif;?> data-panel="<?php echo $panelId?>" name="<?php echo __( $field_name ) ?>">
                    <?php foreach ( $field['options'] as $k => $v ) : ?>
                        <option
                            value="<?php echo __( $k ) ?>" <?php $this->_dataHelper->selected( $current, $k ) ?>><?php echo  $v  ?></option>
                    <?php endforeach; ?>
                </select>
                <?php
                break;

            case 'textarea' :
            case 'code' :
                ?><textarea type="text" name="<?php echo __( $field_name ) ?>"
                            class="widefat <?php if ( $field['type'] == 'code' ) {
                                echo 'cs-field-code';
                            } ?>" rows="4"><?php echo $this->_dataHelper->esc_textarea( $current ) ?></textarea><?php
                break;
        }

        echo '</div>';

        if ( ! empty( $field['description'] ) ) {
            ?><p class="cs-description"><?php echo $field['description'] ?></p><?php
        }
    }
}

