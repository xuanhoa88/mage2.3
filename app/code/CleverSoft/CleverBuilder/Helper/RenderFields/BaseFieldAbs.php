<?php

/**
 * The base class for all form fields.
 */
namespace CleverSoft\CleverBuilder\Helper\RenderFields;

use Magento\Framework\View\Asset\Repository;

class BaseFieldAbs extends \Magento\Framework\App\Helper\AbstractHelper {
    /**
     * The base name for this field. It is used in the generation of HTML element id and name attributes.
     *
     * @access protected
     * @var string
     */
    protected $base_name;
    protected $element_id;
    protected $element_name;
    protected $field_options = array();
    protected $javascript_variables;
    protected $type;
    protected $label;
    protected $default = '';
    protected $description;
    protected $optional;
    protected $required;
    protected $sanitize;
    protected $for_widget;
    protected $parent_container;
    protected $is_container;
    protected $input_css_classes;
    protected $state_emitter;
    protected $state_handler;
    protected $placeholder;
    protected $state_handler_initial;
    protected $_layout;
    protected $_objectManager;
    protected $_storeManager;
    protected $_assetRepo;

    /*
     *
     */
    public function __construct( \Magento\Framework\App\Helper\Context $context , \Magento\Store\Model\StoreManagerInterface $storeManager, \Magento\Framework\View\LayoutInterface $layoutInterface , \Magento\Framework\ObjectManagerInterface $objectManagerInterface, Repository $repository) {
        parent::__construct($context);
        $this->_layout = $layoutInterface;
        $this->_objectManager = $objectManagerInterface;
        $this->_storeManager = $storeManager;
        $this->_assetRepo = $repository;
//        $this->init();
    }

    public function initParams($base_name, $element_id, $element_name, $field_options, $for_widget = null, $parent_container = array()) {
        if( isset( $field_options['type'] ) ) {
            $this->type = $field_options['type'];
        }
        else {
            throw new \InvalidArgumentException( 'BaseFieldAbs::__construct: $field_options must contain a \'type\' field.' );
        }

        $this->base_name = $base_name;
        $this->element_id = $element_id;
        $this->element_name = $element_name;
        $this->field_options = $field_options;
        $this->javascript_variables = array();
        $this->placeholder = isset($field_options['placeholder']) ? $field_options['placeholder'] : '';
        $this->default = isset($field_options['default']) ? $field_options['default'] : '';

        $this->for_widget = $for_widget;
        $this->parent_container = $parent_container;
    }

    public function getOptions($key) {
        return $this->$key;
    }

    protected function get_default_options() {
        //Stub: This function may be overridden by subclasses to have default field options.
        return null;
    }

    /*
     * prepare value of some variables.
     */
    public function prepareFieldVariables($field_options) {
        foreach ($field_options as $key=>$val) {
            if( isset($this->$key) || is_null($this->$key)) {
                $this->$key = $val;
            }
        }
    }

    /**
     * The CSS classes to be applied to the default label.
     * This function should be overridden by subclasses when they want to add custom CSS classes to the HTML input label.
     *
     * @return array The array of label CSS classes.
     */
    public function get_label_classes( $value, $instance ) {
        return array( 'control-label cleversoft-widget-field-label' );
    }

    /**
     * The CSS classes to be applied to the default description.
     * This function should be overridden by subclasses when they want to add custom CSS classes to the description text.
     *
     * @return array The modified array of description text CSS classes.
     */
    protected function get_description_classes() {
        return array( 'cleversoft-widget-description' );
    }

    /**
     * This function is called by the containing when rendering it's form.
     *
     * @param $value mixed The current instance value of the field.
     * @param $instance array Optionally pass in the widget instance, if rendering of additional values is required.
     */
    public function render( $value, $instance = array() ) {
        if ( is_null( $value ) && isset( $this->default ) ) {
            $value = $this->default;
        }
        
        $wrapper_attributes = array(
            'class' => array(
                'cleversoft-widget-field',
                'cleversoft-widget-field-type-' . $this->type,
                'cleversoft-widget-field-' . $this->base_name
            )
        );

        if(isset($this->field_options['depends'])) {
            $key = key($this->field_options['depends']);
            if (isset($this->field_options['depends'][$key]['value'])) {
                $wrapper_attributes['data-dependon-'.$key] = $this->field_options['depends'][$key]['value'];
            } else {
                $wrapper_attributes['data-dependon-'.$key] = $this->field_options['depends'][$key];
            }
        }

        if(isset($this->field_options['indepence'])) {
            $key = key($this->field_options['indepence']);
            if (isset($this->field_options['indepence'][$key]['value'])) {
                $wrapper_attributes['data-indepenceon-'.$key] = $this->field_options['indepence'][$key]['value'];
            } else {
                $wrapper_attributes['data-indepenceon-'.$key] = $this->field_options['indepence'][$key];
            }
        }

        if( !empty( $this->optional ) ) $wrapper_attributes['class'][] = 'cleversoft-widget-field-is-optional';
        if( !empty( $this->required ) ) $wrapper_attributes['class'][] = 'cleversoft-widget-field-is-required';
        $wrapper_attributes['class'] = implode(' ', $wrapper_attributes['class'] );

        if( !empty( $this->state_emitter ) ) {
            // State emitters create new states for the form
            $wrapper_attributes['data-state-emitter'] = json_encode( $this->state_emitter );
        }

        if( !empty( $this->state_handler ) ) {
            // State handlers decide what to do with form states
            $wrapper_attributes['data-state-handler'] = json_encode( $this->state_handler );
        }

        if( !empty( $this->state_handler_initial ) ) {
            // Initial state handlers are only run when the form is first loaded
            $wrapper_attributes['data-state-handler-initial'] = json_encode( $this->state_handler_initial );
        }

        ?><div <?php foreach( $wrapper_attributes as $attr => $attr_val ) echo $attr.'="' . ( $attr_val ) . '" ' ?>><div class="form-group"><?php

        // Allow subclasses and to render something before and after the render_field() function is called.
        $this->render_before_field( $value, $instance );
        $this->render_field( $value, $instance );
        ?></div></div><?php
    }

    /**
     * This function is called before the main render function.
     *
     * @param $value mixed The current value of this field.
     * @param $instance array The current widget instance.
     */
    protected function render_before_field( $value, $instance ) {
        $this->render_field_label( $value, $instance );
    }

    /**
     * Default label rendering implementation. Subclasses should override if necessary to render labels differently.
     */
    protected function render_field_label( $value, $instance ) {
        if (!$this->label) return '';
        ?>
        <label for="<?php echo ( $this->element_id ) ?>" class="<?php echo $this->render_CSS_classes( $this->get_label_classes( $value, $instance ) ) ?>" >
            <?php
            echo ( __($this->label) );
            if( !empty( $this->optional ) ) {
                echo '<span class="field-optional"> ( ' . __('Optional') . ' ) </span>';
            }
            if( !empty( $this->required ) ) {
                echo '<span class="field-required"> ( ' . __('Required') . ' ) </span>';
            }
            ?>
        </label>
    <?php
    }

    /**
     * Helper function to render the HTML class attribute with the array of classes.
     */
    protected function render_CSS_classes( $CSS_classes ) {
        if( ! empty( $CSS_classes ) ) {
            return ( implode( ' ', $CSS_classes  ) );
        }
    }

    /**
     *
     * The main field rendering function. This function should be overridden by all subclasses and used to render their
     * specific form field HTML for display.
     *
     * @param $value mixed The current value of this field.
     * @param $instance array The current widget instance.
     * @return mixed Should output the desired HTML.
     */
    protected function render_field( $value, $instance ){}

    /**
     * The default sanitization function.
     *
     * @param $value mixed The value to be sanitized.
     * @param $instance array The widget instance.
     * @param $old_value mixed The old value of this field.
     *
     * @return mixed|string
     */
    public function sanitize( $value, $instance = array(), $old_value = null ) {

        $value = $this->sanitize_field_input( $value, $instance );

        if( isset( $this->sanitize ) ) {
            // This field also needs some custom sanitization
            switch( $this->sanitize ) {
                case 'url':
                    $value = ( $value );
                    break;

                case 'email':
                    $value = ( $value );
                    break;

                default:
                    // This isn't a built in sanitization. Maybe it's handled elsewhere.
                    if( is_callable( $this->sanitize ) ) {
                        $value = call_user_func( $this->sanitize, $value, $old_value );
                    }
                    else if( is_string( $this->sanitize ) ) {
                    }
                    break;
            }
        }

        return $value;
    }

    /**
     * This function is called after the main render function.
     *
     * @param $value mixed The current value of this field.
     * @param $instance array The current widget instance.
     */
    protected function render_after_field( $value, $instance ) {
        $this->render_field_description();
    }

    /**
     * Default description rendering implementation. Subclasses should override if necessary to render descriptions
     * differently.
     */
    protected function render_field_description() {
        if( ! empty( $this->description ) ) {
            ?><div <?php $this->render_CSS_classes( $this->get_description_classes() ) ?>><?php echo ( __($this->description) ) ?></div><?php
        }
    }

    /**
     *
     * The main sanitization function. This function should be overridden by all subclasses and used to sanitize the
     * input received from their HTML form field.
     *
     * @param $value mixed The current value of this field.
     * @param $instance array The widget instance.
     *
     * @return mixed The sanitized value.
     */
    protected function sanitize_field_input( $value, $instance ){}

    /**
     * There are cases where a field may affect values on the widget instance, other than it's own input. It then becomes
     * necessary to perform additional sanitization on the widget instance, which should be done here.
     *
     * @param $instance
     * @return mixed
     */
    public function sanitize_instance( $instance ) {
        //Stub: This function may be overridden by subclasses wishing to sanitize additional instance fields.
        return $instance;
    }

    /**
     * Occasionally it is necessary for a field to set a variable to be used in the front end. Override this function
     * and set any necessary values on the `javascript_variables` instance property.
     *
     * @return array
     */
    public function get_javascript_variables() {
        return $this->javascript_variables;
    }

    /**
     * Some more complex fields may require some JavaScript in the front end. Enqueue them here.
     */
    public function enqueue_scripts() {

    }

    public function __get( $name ) {
        if ( isset( $this->$name ) ) {
            return $this->$name;
        }
    }

    public function getViewFileUrl($fileId, array $params = []) {
        try {
            $params = array_merge(['_secure' => $this->_getRequest()->isSecure()], $params);
            return $this->_assetRepo->getUrlWithParams($fileId, $params);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->_logger->critical($e);
            return $this->_getNotFoundUrl();
        }
    }

    protected function _getNotFoundUrl($route = '', $params = ['_direct' => 'core/index/notFound'])
    {
        return $this->_getUrl($route, $params);
    }
}
