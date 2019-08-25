<?php
namespace CleverSoft\CleverBuilder\Helper\RenderFields;
/**
 *
 * The common base class for text input type fields.
 */
class InnerrowLayoutField extends \CleverSoft\CleverBuilder\Helper\RenderFields\BaseFieldAbs {
    /*
     *
     */
    protected $placeholder;
    protected $readonly;
    protected $input_type;
    /*
     *
     */
    protected function get_input_classes() {
        return array( 'widefat', 'cleversoft-widget-input' ,'form-control','innerrow-layout-field');
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
        ?>
        <div class="clever-innerrow layout-group">
            <div class="add-shortcode-presets">
                <ul>
                    <?php foreach($instance['layouts'] as $k=>$layout) :  ?>
                        <li class="<?php echo $k==0 ? 'active' : '';   ?>" style="">
                            <button data-layout="<?php echo ($k) ?>" type="button" title="<?php echo __('Innerrow'). ($k+1) ?>" ng-click="" class="with-thumbnail" data-value-id="#<?php echo ( $this->element_id ) ?>">
                                <img data-layout="<?php echo ($k) ?>" src="<?php  echo $this->_assetRepo->getUrl('CleverSoft_CleverBuilder::images/grid_layouts/grid-'.($k+1).'.svg');?>" alt="<?php echo __('Innerrow'). ($k+1) ?>">
                                <span class="title"><?php echo __('Innerrow'). ($k+1) ?></span>
                            </button>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
        <input <?php if(isset($this->field_options['depends'])) : ?> data-depends="<?php echo htmlspecialchars(json_encode($this->field_options['depends']), ENT_QUOTES, 'UTF-8') ?>" <?php endif; ?> <?php if(isset($this->field_options['indepence'])) : ?> data-indepence="<?php echo htmlspecialchars(json_encode($this->field_options['indepence']), ENT_QUOTES, 'UTF-8') ?>" <?php endif; ?> type="hidden"
                                                                                                                                                                                                      name="<?php echo ( $this->element_name ) ?>"
                                                                                                                                                                                                      id="<?php echo ( $this->element_id ) ?>"
                                                                                                                                                                                                      value="<?php echo ( $value ) ?>"
            <?php $this->render_data_attributes( $this->get_input_data_attributes() ) ?>
                                                                                                                                                                                                      class="<?php echo $this->render_CSS_classes( $this->get_input_classes() ) ?>"
            <?php if ( ! empty( $this->placeholder ) ) echo 'placeholder="' . ( $this->placeholder ) . '"' ?>
            <?php if( ! empty( $this->readonly ) ) echo 'readonly' ?> />
        <?php $this->render_after_field( $value, $instance); ?>

    <?php
    }

    protected function sanitize_field_input( $value, $instance ) {
        $sanitized_value = ( $value );
        return $sanitized_value;
    }
}
