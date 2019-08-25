<?php
/**
 * @category    CleverSoft
 * @package     CleverLayeredNavigation
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author      ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */
namespace CleverSoft\CleverBuilder\Helper;

use Magento\Framework\Filesystem;
use CleverSoft\CleverBuilder\Model\PanelsDataFactory as PanelsDataFactory;
use CleverSoft\CleverBuilder\Helper\Settings as Settings;
use Magento\Widget\Model\WidgetFactory as WidgetFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use CleverSoft\CleverBuilder\Helper\Elements\AbstractElement;
use Zend\Stdlib\JsonSerializable;

define( 'KB_IN_BYTES', 1024 );
define( 'MB_IN_BYTES', 1024 * KB_IN_BYTES );
define( 'GB_IN_BYTES', 1024 * MB_IN_BYTES );
define( 'TB_IN_BYTES', 1024 * GB_IN_BYTES );

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const CBROW = 'cb-row';
    const CBCOLUMN = 'cb-column';
    const CBCOLUMNINNERTEXT = 'cb-column-text';
    protected $_storeManager;
    protected $_userFactory;
    protected $_customerSession;
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;
    protected $_filesystem;
    /**
     * @var \Magento\Framework\View\Asset\Repository
     */
    protected $_file;

    protected $_modelPanelsFactory;
    protected $_customerFactory;
    protected $_assetRepo;
    protected $_userFactoryRolesFactory;
    protected $scopeConfig;
    protected $_shortCodes;
    protected $cacheTypeList;
    protected $cbControllColumn;
    protected $_urlBuilder;
    protected $_objectManager;
    protected $_abstractElement;
    /**
     * @var \Magento\Widget\Model\WidgetFactory
     */
    protected $_widgetFactory;

    protected $_blockFactory;
    protected $_page;
    protected $_panelSettings;
    public $measurements_list = array('px', '%', 'in', 'cm', 'mm', 'em', 'ex', 'pt', 'pc', 'rem' );

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Customer\Model\CustomerFactory $customerFactory ,
        \Magento\User\Model\UserFactory $userFactory ,
        \Magento\Cms\Model\BlockFactory $blockFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Cms\Model\Page $page,
        Settings $settings,
        \Magento\Framework\Registry $registry,
        WidgetFactory $widgetFactory,
        AbstractElement $abstractElement,
        \Magento\Framework\View\Asset\Repository $assetRepo,
        PanelsDataFactory $panelsDataFactory,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Filesystem\Driver\File $file,
        \Magento\Framework\Image\AdapterFactory $imageFactory,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\ObjectManagerInterface $objectManagerInterface
    ) {
        $this->scopeConfig = $context->getScopeConfig();
        $this->_storeManager = $storeManager;
        $this->_customerSession = $customerSession;
        $this->_shortCodes = array(self::CBROW=>'row',self::CBCOLUMN=>'column',self::CBCOLUMNINNERTEXT=>'columnText');
        $this->_customerFactory = $customerFactory;
        $this->_urlBuilder = $context->getUrlBuilder();
        $this->_modelPanelsFactory = $panelsDataFactory;
        $this->_objectManager = $objectManagerInterface;
        $this->cacheTypeList = $cacheTypeList;
        $this->_assetRepo = $assetRepo;
        $this->_abstractElement = $abstractElement;
        $this->_page = $page;
        $this->_coreRegistry = $registry;
        $this->_widgetFactory = $widgetFactory;
        $this->_panelSettings = $settings;
        $this->_userFactory = $userFactory;
        $this->_blockFactory = $blockFactory;
        $this->_filesystem = $filesystem;
        $this->_imageFactory = $imageFactory;
        $this->_file = $file;
        parent::__construct($context);
        global $wp_widget_factory;
        if(empty ($wp_widget_factory)) $wp_widget_factory = $this->_getAvailableWidgets();
    }


    /* Get current customer */

    /* Check to visible panel button */
    public function isEnabledBuilder() {
        if(!$this->_customerSession->isLoggedIn()) {
            if(!$this->getCustomerSessionData()->isLoggedIn()) return false;
        }
        $customer = $this->_customerSession->getCustomerData();
        if ($customer->getCustomAttribute('account_is_a_builder')) {
            $_isBulder = $customer->getCustomAttribute('account_is_a_builder')->getValue();
        } else {
            return false;
        }
        if ($this->getStoreConfig('clevervisualpagebuilder/general/is_enabled') && $_isBulder) {
            return true;
        }
        return false;
    }
    /*
     * return customer session data
     */
    protected function getCustomerSessionData(){
        return $this->_objectManager->create('Magento\Customer\Model\Session');
    }

    /* return object block
     * @$block_id
     */
    public function getCmsBlockById($block_id){
        $block = $this->_blockFactory->create();
        $block->setStoreId($this->_storeManager->getStore()->getId())->load($block_id);
        return $block;
    }

    /*
     *
     */
    public function isEnableShortcode(){
        return $this->getStoreConfig('clevervisualpagebuilder/general/is_enabled') ? 1 : 0 ;
    }

    /* Get system store config */
    public function getStoreConfig($key, $storeId = NULL){
        return $this->scopeConfig->getValue($key, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }
    /*
     * set customer session data
     */
    public function setCustomerStatusPanel($status){
        if(!$this->_customerSession->isLoggedIn()) return false;
        $this->_customerSession->setEnablePanel($status);
    }
    /*
     * get enable or not
     */
    public function getCustomerEnablePanel(){
        return ($this->_customerSession->getEnablePanel() ? 1 : 0);
    }
    /*
     *get swicth link redirect
     */
    public function getLinkSwitch(){
        return $this->_urlBuilder->getUrl('cleverbuilder/index/index', ['_secure' => true]);
    }

    /*
     * get url for enabling builder frontend
     */
    public function getPageUrl(){
        return $this->_urlBuilder->getCurrentUrl();
    }
    /*
     * return page id
     */
    public function getPageId() {
        return $this->_page->getId();
    }
    /*
     * return all available store ids of page in array
     */
    public function getPageStoreIds($pageIds){
        if ($this->_page->getData('store_id')) return $this->_page->getData('store_id');
        $pageData = $this->_page->load($pageIds);
        return $pageData->getData('store_id');
    }
    /*
     * return page content in html
     */
    public function getPageContent(){
        if($this->_page->getData('builder')) return $this->_page->getData('builder') ;
        return $this->_page->getContent();
    }
    /*
     * return model collection for Panel
     */
    public function getPanelDataCollection(){
        return $this->_modelPanelsFactory->create()->getCollection();
    }
    /*
     * return panels data
     */
    public function getPanelsData($pageId = 0, $exist = false) {
        $page_id = $pageId ? $pageId : $this->getPageId();
        $collection = $this->getPanelDataCollection();
        $collection->addFieldToFilter('page_id', $page_id);
        $panels_data = $collection->getData();
        if(!$exist) {
            if(empty($panels_data)) return '';
            return $this->convertData($panels_data[0]['setting']);
        } else {
            if(empty($panels_data)) return false;
            return $panels_data[0]['id'];
        }
    }
    /*
     *
     */
    public function convertData( $panels_data ) {
        if(!is_array($panels_data) && is_array(json_decode($panels_data, true))){
            $panels_data = json_decode($panels_data, true);
        }
        if ( empty( $panels_data ) || empty( $panels_data['grids'] ) || ! is_array( $panels_data['grids'] ) ) {
            return $panels_data;
        }

        foreach( $panels_data['grids'] as & $grid ) {
            if ( ! is_array( $grid ) || empty( $grid ) || empty( $grid['style'] ) ) {
                continue;
            }

            if ( is_string( $grid['style'] ) ) {
                $grid['style'] = array(
                    $grid['style']
                );
            }
        }

        return $panels_data;
    }
    /*
     * return SAVE url
     */
    public function getSaveElementUrl(){
        return $this->_urlBuilder->getUrl('cleverbuilder/element/save', ['_secure' => true]);
    }
    /*
     * return tabs
     */
    protected function add_widgets_dialog_tabs( $tabs ) {
        return $tabs;
    }

    /*
    * regex to parse shortcode
    */

    public function shortcodesRegexp($tagnames = array()) {
        if(empty($tagnames))$tagnames = array_keys( $this->_shortCodes );
        $tagregexp = implode( '|', array_map( 'preg_quote', $tagnames ) );
        // WARNING from shortcodes.php! Do not change this regex without changing do_shortcode_tag() and strip_shortcode_tag()
        // Also, see shortcode_unautop() and shortcode.js.
        return '\\[' // Opening bracket
        . '(\\[?)' // 1: Optional second opening bracket for escaping shortcodes: [[tag]]
        . "($tagregexp)" // 2: Shortcode name
        . '(?![\\w-])' // Not followed by word character or hyphen
        . '(' // 3: Unroll the loop: Inside the opening shortcode tag
        . '[^\\]\\/]*' // Not a closing bracket or forward slash
        . '(?:' . '\\/(?!\\])' // A forward slash not followed by a closing bracket
        . '[^\\]\\/]*' // Not a closing bracket or forward slash
        . ')*?' . ')' . '(?:' . '(\\/)' // 4: Self closing tag ...
        . '\\]' // ... and closing bracket
        . '|' . '\\]' // Closing bracket
        . '(?:' . '(' // 5: Unroll the loop: Optionally, anything between the opening and closing shortcode tags
        . '[^\\[]*+' // Not an opening bracket
        . '(?:' . '\\[(?!\\/\\2\\])' // An opening bracket not followed by the closing shortcode tag
        . '[^\\[]*+' // Not an opening bracket
        . ')*+' . ')' . '\\[\\/\\2\\]' // Closing shortcode tag
        . ')?' . ')' . '(\\]?)'; // 6: Optional second closing brocket for escaping shortcodes: [[tag]]

    }
    public function getpanelsStyles(){
        return $this->cleanPanelsOptionData( array('fullContainer'=>'body'));
    }
    /**
     * Return array of available widgets based on configuration
     *
     * @param bool $withEmptyElement
     * @return array
     */
    protected function _getAvailableWidgets()
    {
        $result = [];
        $allWidgets = $this->getWidgetFactory()->getWidgetsArray();
        $accepted = $this->_getAcceptedWidgets();
        foreach ($allWidgets as $widget) {
            if (!in_array($widget['code'], $accepted)) {
                continue;
            }
            $result[$widget['type']] = array(
                'class'       => 'CleverSoft\CleverBuilder\Block\Builder\Widget\Others',
                'title'       => ! empty( $widget['name'] ) ? str_replace("Clever ","",$widget['name']) : __( 'Untitled Widget'),
                'description' => ! empty( $widget['description'] ) ? $widget['description'] : '',
                'installed'   => true,
                'groups'      => array(),
                'type'        => $widget['type'],
                'area'        => 'widget',
                'code'        => $widget['code']
            );
        }
        if (class_exists('CleverSoft\CleverBuilder\Block\Builder\Widget\TextAreaEditor')) {
            $result['CleverSoft\CleverBuilder\Block\Builder\Widget\TextAreaEditor'] = array(
                'class' => 'CleverSoft\CleverBuilder\Block\Builder\Widget\TextAreaEditor',
                'title' => __('Text Editor'),
                'description' => __('A rich text editor'),
                'installed' => true,
                'groups' => '',
                'area'        => 'widget',
                'type'        => 'CleverSoft\CleverBuilder\Block\Builder\Widget\TextAreaEditor',
                'code'        => 'editor-text'
            );
        }

        $jsonElement = $this->_abstractElement->getElementsByJsonFile();
        if (!empty($jsonElement)){
            foreach ($jsonElement as $key=>$jes) {
                $result = array_merge($result, $jes);
            }
        }

        return $result;
    }
    /*
     * return oject of model widget factory
     */
    public function getWidgetFactory() {
        return $this->_widgetFactory->create();
    }

    /**
     * Get a list of URLs of WYSIWYG placeholder images
     *
     * Returns array(<type> => <url>)
     *
     * @return array
     */
    public function getPlaceholderImageUrls()
    {
        $result = [];
        $widgets = $this->getWidgetFactory()->getWidgets();
        /** @var array $widget */
        foreach ($widgets as $widget) {
            if (isset($widget['@'])) {
                if (isset($widget['@']['type'])) {
                    $type = $widget['@']['type'];
                    $result[$type] = $this->getPlaceholderImageUrl($type);
                }
            }
        }
        return $result;
    }
    /**
     * Get image URL of WYSIWYG placeholder image
     *
     * @param string $type
     * @return string
     */
    public function getPlaceholderImageUrl($type)
    {
        $placeholder = false;
        $widget = $this->getWidgetFactory()->getWidgetByClassType($type);
        if (is_array($widget) && isset($widget['placeholder_image'])) {
            $placeholder = (string)$widget['placeholder_image'];
        }

        if ($placeholder) {
            $arrPath = explode('::',$placeholder);

            if(count($arrPath)==2){
                $widgetPath = str_replace('::','/',$placeholder);
                $adminStaticUrl = $this->_urlBuilder->getBaseUrl(['_type' => \Magento\Framework\UrlInterface::URL_TYPE_STATIC]).'adminhtml/Magento/backend/en_US/';

                $filePath = $this->_filesystem->getDirectoryRead(DirectoryList::STATIC_VIEW)->getAbsolutePath('adminhtml/Magento/backend/en_US/').$widgetPath;
                if ($this->_file->isExists($filePath))  {
                    return $adminStaticUrl.$widgetPath;
            }
            }
        }
        return $this->_assetRepo->getUrl('CleverSoft_CleverBuilder::images/widget/placeholder.gif');
    }

    public function getStaticUrl(){
        return $this->_urlBuilder->getBaseUrl(['_type' => \Magento\Framework\UrlInterface::URL_TYPE_STATIC]);
    }

    public function getAssetRespo() {
        return $this->_assetRepo;
    }

    /**
     * Return array of widgets disabled for selection
     *
     * @return string[]
     */
    protected function _getAcceptedWidgets() {
        return explode(",",$this->getStoreConfig('clevervisualpagebuilder/widget/list_widget'));
    }

    /*
     *
     */
    public function getPanelsOption(){
        $user_id = $this->_customerSession->getId();
        $text_widget = 'CleverSoft\CleverBuilder\Block\Builder\Widget\TextAreaEditor';
        $widgets = $this->_getAvailableWidgets();

        $data = array(
            'user'                      => ! empty( $user_id ) ? $user_id : 0,
            'ajaxurl'                   => $this->_urlBuilder->getUrl('cleverbuilder/index/ajax', ['_secure' => true]),
            'doneurl'                   => $this->_urlBuilder->getUrl('*/*/*', ['_current' => true, '_use_rewrite' => true]),
            'widgets'                   => $widgets ,//get all widgets , modules from magento, just put a default value array()
            'text_widget'               => $text_widget,//after
            'widget_dialog_tabs'        => $this->add_widgets_dialog_tabs(array(
                0 => array(
                    'title'  => __( 'All Widgets'),
                    'filter' => array(
                        'installed' => true,
                        'groups'    => '',
                    )
                )
            )) ,
            'row_layouts'               => array(),
            'directory_enabled'         => 1,
            'copy_content'              => true,
            'cache'                     => array(),

            // Settings for the contextual menu
            'contextual'                => array(
                // Developers can change which widgets are displayed by default using this filter
                'default_widgets' => array(
                    'CleverSoft\CleverBuilder\Block\Builder\Widget\TextAreaEditor'
                ) )
        ,

            // General localization messages
            'loc'                       => array(
                'missing_widget'       => array(
                    'title'       => __( 'Missing Widget'),
                    'description' => __( "Page Builder doesn't know about this widget."),
                ),
                'time'                 => array(
                    // TRANSLATORS: Number of seconds since
                    'seconds' => __( '%d seconds'),
                    // TRANSLATORS: Number of minutes since
                    'minutes' => __( '%d minutes'),
                    // TRANSLATORS: Number of hours since
                    'hours'   => __( '%d hours'),

                    // TRANSLATORS: A single second since
                    'second'  => __( '%d second'),
                    // TRANSLATORS: A single minute since
                    'minute'  => __( '%d minute'),
                    // TRANSLATORS: A single hour since
                    'hour'    => __( '%d hour'),

                    // TRANSLATORS: Time ago - eg. "1 minute before".
                    'ago'     => __( '%s before'),
                    'now'     => __( 'Now'),
                ),
                'history'              => array(
                    // History messages
                    'current'           => __( 'Current'),
                    'revert'            => __( 'Original'),
                    'restore'           => __( 'Version restored'),
                    'back_to_editor'    => __( 'Converted to editor'),

                    // Widgets
                    // TRANSLATORS: Message displayed in the history when a widget is deleted
                    'widget_deleted'    => __( 'Widget deleted'),
                    // TRANSLATORS: Message displayed in the history when a widget is added
                    'widget_added'      => __( 'Widget added'),
                    // TRANSLATORS: Message displayed in the history when a widget is edited
                    'widget_edited'     => __( 'Widget edited'),
                    // TRANSLATORS: Message displayed in the history when a widget is duplicated
                    'widget_duplicated' => __( 'Widget duplicated'),
                    // TRANSLATORS: Message displayed in the history when a widget position is changed
                    'widget_moved'      => __( 'Widget moved'),

                    // Rows
                    // TRANSLATORS: Message displayed in the history when a row is deleted
                    'row_deleted'       => __( 'Row deleted'),
                    // TRANSLATORS: Message displayed in the history when a row is added
                    'row_added'         => __( 'Row added'),
                    // TRANSLATORS: Message displayed in the history when a row is edited
                    'row_edited'        => __( 'Row edited'),
                    // TRANSLATORS: Message displayed in the history when a row position is changed
                    'row_moved'         => __( 'Row moved'),
                    // TRANSLATORS: Message displayed in the history when a row is duplicated
                    'row_duplicated'    => __( 'Row duplicated'),
                    // TRANSLATORS: Message displayed in the history when a row is pasted
                    'row_pasted'        => __( 'Row pasted'),

                    // Cells
                    'cell_resized'      => __( 'Cell resized'),

                    // Prebuilt
                    'prebuilt_loaded'   => __( 'Prebuilt layout loaded'),
                ),

                // general localization
                'prebuilt_loading'     => __( 'Loading prebuilt layout'),
                'confirm_use_builder'  => __( "Would you like to copy this editor's existing content to Page Builder?"),
                'confirm_stop_builder' => __( "Would you like to clear your Page Builder content and revert to using the standard visual editor?"),
                // TRANSLATORS: This is the title for a widget called "Layout Builder"
                'layout_widget'        => __( 'Layout Builder Widget'),
                // TRANSLATORS: A standard confirmation message
                'dropdown_confirm'     => __( 'Are you sure?'),
                // TRANSLATORS: When a layout file is ready to be inserted. %s is the filename.
                'ready_to_insert'      => __( '%s is ready to insert.'),

                // Everything for the contextual menu
                'contextual'           => array(
                    'add_widget_below' => __( 'Add Widget Below'),
                    'add_widget_cell'  => __( 'Add Widget to Cell'),
                    'search_widgets'   => __( 'Search Widgets'),

                    'add_row' => __( 'Add Row'),
                    'column'  => __( 'Column'),

                    'cell_actions'        => __( 'Cell Actions'),
                    'cell_paste_widget'   => __( 'Paste Widget'),

                    'widget_actions'   => __( 'Widget Actions'),
                    'widget_edit'      => __( 'Edit Widget'),
                    'widget_duplicate' => __( 'Duplicate Widget'),
                    'widget_delete'    => __( 'Delete Widget'),
                    'widget_copy'      => __( 'Copy Widget'),
                    'widget_paste'     => __( 'Paste Widget Below'),

                    'row_actions'   => __( 'Row Actions'),
                    'row_edit'      => __( 'Edit Row'),
                    'row_duplicate' => __( 'Duplicate Row'),
                    'row_delete'    => __( 'Delete Row'),
                    'row_copy'      => __( 'Copy Row'),
                    'row_paste'     => __( 'Paste Row'),
                ),
                'draft'                => __( 'Draft'),
                'untitled'             => __( 'Untitled'),
                'row' => array(
                    'add' => __( 'New Row'),
                    'edit' => __( 'Row'),
                ),
                'welcomeMessage' => array(
                    'addingDisabled' => __( 'Hmmm... Adding layout elements is not enabled. Please check if Page Builder has been configured to allow adding elements.'),
                    'oneEnabled' => __( 'Add a {{%= items[0] %}} to get started.'),
                    'twoEnabled' => __( 'Add a {{%= items[0] %}} or {{%= items[1] %}} to get started.'),
                    'threeEnabled' => __( 'Add a {{%= items[0] %}}, {{%= items[1] %}} or {{%= items[2] %}} to get started.'),
                    'addWidgetButton' => "<a href='#' class='cs-tool-button cs-widget-add'>" . __( 'Widget') . "</a>",
                    'addRowButton' => "<a href='#' class='cs-tool-button cs-row-add'>" . __( 'Row') . "</a>",
                    'addPrebuiltButton' => "<a href='#' class='cs-tool-button cs-prebuilt-add'>" . __( 'Prebuilt Layout') . "</a>",
                    'docsMessage' => sprintf(
                        __( 'Read our %s if you need help.'),
                        "<a href='http://doc.zootemplate.com/cleveraddon-for-elementor/' target='_blank' rel='noopener noreferrer'>" . __( 'documentation') . "</a>"
                    ),
                ),
            ),
            'plupload'                  => array(
                'max_file_size'       => $this->max_upload_size() . 'b',
                'url'                 => "#",
                'flash_swf_url'       => "#",
                'silverlight_xap_url' => "#",
                'filter_title'        => __( 'Page Builder layouts'),
                'error_message'       => __( 'Error uploading or importing file.'),
            ),
            'wpColorPickerOptions'      => array(),
            'prebuiltDefaultScreenshot' => $this->_assetRepo->getUrl( 'CleverSoft_CleverBuilder::images/prebuilt-default.png' ),
            'loadOnAttach'              => false,
            'cleversoftWidgetRegex'     => str_replace( '*+', '*', $this->shortcodesRegexp( array( 'cleversoft_widget' ) ) ),
            'childWidgetFilter'         => array(
                'row'   => array('clever-innerrow', 'editor-text', 'clever-banner', 'clever-category', 'clever-products', 'clever-products-list',  'clever-textbox', 'clever-button', 'clever-images','clever-testimonial', 'clever-video', 'clever-divider', 'clever-slider', 'clever-tabs', 'clever-iconbox', 'clever-imagebox', 'clever-title', 'clever-countdown'),
                'innerrow'   => array('editor-text', 'clever-banner', 'clever-category', 'clever-products', 'clever-products-list',  'clever-textbox', 'clever-button', 'clever-images','clever-testimonial', 'clever-video', 'clever-divider', 'clever-slider', 'clever-tabs', 'clever-iconbox', 'clever-imagebox', 'clever-title', 'clever-countdown'),
                'tab'   => array('editor-text', 'clever-banner', 'clever-category', 'clever-products', 'clever-products-list',  'clever-textbox', 'clever-button', 'clever-images','clever-testimonial', 'clever-video', 'clever-divider', 'clever-slider', 'clever-innerrow', 'clever-title', 'clever-countdown'),
                'banner' => array('clever-textbox', 'clever-images'),
                'slider' => array('clever-banner', 'clever-images'),
                'textbox' => array('editor-text', 'clever-iconbox', 'clever-imagebox', 'clever-button', 'clever-images','clever-testimonial', 'clever-video', 'clever-divider','clever-title', 'clever-countdown')
            ),
            'childWidgetFilterExClude'         => array(
                'tab' => array('clever-tabs')
            )
        );

        return $this->cleanPanelsOptionData( $data );

    }

    protected function max_upload_size() {
        $u_bytes = $this->convert_hr_to_bytes( ini_get( 'upload_max_filesize' ) );
        $p_bytes = $this->convert_hr_to_bytes( ini_get( 'post_max_size' ) );

        /**
         * Filters the maximum upload size allowed in php.ini.
         *
         * @since 2.5.0
         *
         * @param int $size    Max upload size limit in bytes.
         * @param int $u_bytes Maximum upload filesize in bytes.
         * @param int $p_bytes Maximum size of POST data in bytes.
         */
        return  min( $u_bytes, $p_bytes );
    }

    protected function convert_hr_to_bytes( $value ) {
        $value = strtolower( trim( $value ) );
        $bytes = (int) $value;

        if ( false !== strpos( $value, 'g' ) ) {
            $bytes *= GB_IN_BYTES;
        } elseif ( false !== strpos( $value, 'm' ) ) {
            $bytes *= MB_IN_BYTES;
        } elseif ( false !== strpos( $value, 'k' ) ) {
            $bytes *= KB_IN_BYTES;
        }

        // Deal with large (float) values which run into the maximum integer size.
        return min( $bytes, PHP_INT_MAX );
    }

    protected function cleanPanelsOptionData(array $data){
        foreach ( (array) $data as $key => $value ) {
            if ( !is_scalar($value) )
                continue;

            $l10n[$key] = html_entity_decode( (string) $value, ENT_QUOTES, 'UTF-8');
        }

        return $this->cb_json_encode( $data );
    }

    protected function cb_json_encode( $data, $options = 0, $depth = 512 ) {
        /*
         * json_encode() has had extra params added over the years.
         * $options was added in 5.3, and $depth in 5.5.
         * We need to make sure we call it with the correct arguments.
         */
        if ( version_compare( PHP_VERSION, '5.5', '>=' ) ) {
            $args = array( $data, $options, $depth );
        } elseif ( version_compare( PHP_VERSION, '5.3', '>=' ) ) {
            $args = array( $data, $options );
        } else {
            $args = array( $data );
        }

        // Prepare the data for JSON serialization.
        $args[0] = $this->json_prepare_data( $data );

        $json = @call_user_func_array( 'json_encode', $args );

        // If json_encode() was successful, no need to do more sanity checking.
        // ... unless we're in an old version of PHP, and json_encode() returned
        // a string containing 'null'. Then we need to do more sanity checking.
        if ( false !== $json && ( version_compare( PHP_VERSION, '5.5', '>=' ) || false === strpos( $json, 'null' ) ) )  {
            return $json;
        }

        try {
            $args[0] = $this->json_sanity_check( $data, $depth );
        } catch ( \Exception $e ) {
            return false;
        }

        return call_user_func_array( 'json_encode', $args );
    }

    protected function json_prepare_data( $data ) {
        if ( ! defined( 'JSON_SERIALIZE_COMPATIBLE' ) || JSON_SERIALIZE_COMPATIBLE === false ) {
            return $data;
        }

        switch ( gettype( $data ) ) {
            case 'boolean':
            case 'integer':
            case 'double':
            case 'string':
            case 'NULL':
                // These values can be passed through.
                return $data;

            case 'array':
                // Arrays must be mapped in case they also return objects.
                return array_map( 'json_prepare_data', $data );

            case 'object':
                // If this is an incomplete object (__PHP_Incomplete_Class), bail.
                if ( ! is_object( $data ) ) {
                    return null;
                }

                if ( $data instanceof \JsonSerializable ) {
                    $data = $data->jsonSerialize();
                } else {
                    $data = get_object_vars( $data );
                }

                // Now, pass the array (or whatever was returned from jsonSerialize through).
                return $this->json_prepare_data( $data );

            default:
                return null;
        }
    }

    protected function json_sanity_check( $data, $depth ) {
        if ( $depth < 0 ) {
            throw new \Exception( 'Reached depth limit' );
        }

        if ( is_array( $data ) ) {
            $output = array();
            foreach ( $data as $id => $el ) {
                // Don't forget to sanitize the ID!
                if ( is_string( $id ) ) {
                    $clean_id = $this->json_convert_string( $id );
                } else {
                    $clean_id = $id;
                }

                // Check the element type, so that we're only recursing if we really have to.
                if ( is_array( $el ) || is_object( $el ) ) {
                    $output[ $clean_id ] = $this->json_sanity_check( $el, $depth - 1 );
                } elseif ( is_string( $el ) ) {
                    $output[ $clean_id ] = $this->json_convert_string( $el );
                } else {
                    $output[ $clean_id ] = $el;
                }
            }
        } elseif ( is_object( $data ) ) {
            $output = new \stdClass;
            foreach ( $data as $id => $el ) {
                if ( is_string( $id ) ) {
                    $clean_id = $this->json_convert_string( $id );
                } else {
                    $clean_id = $id;
                }

                if ( is_array( $el ) || is_object( $el ) ) {
                    $output->$clean_id = $this->json_sanity_check( $el, $depth - 1 );
                } elseif ( is_string( $el ) ) {
                    $output->$clean_id = $this->json_convert_string( $el );
                } else {
                    $output->$clean_id = $el;
                }
            }
        } elseif ( is_string( $data ) ) {
            return $this->json_convert_string( $data );
        } else {
            return $data;
        }

        return $output;
    }

    protected function json_convert_string( $string ) {
        static $use_mb = null;
        if ( is_null( $use_mb ) ) {
            $use_mb = function_exists( 'mb_convert_encoding' );
        }

        if ( $use_mb ) {
            $encoding = mb_detect_encoding( $string, mb_detect_order(), true );
            if ( $encoding ) {
                return mb_convert_encoding( $string, 'UTF-8', $encoding );
            } else {
                return mb_convert_encoding( $string, 'UTF-8', 'UTF-8' );
            }
        } else {
            return $string;
        }
    }

    public function checked( $checked, $current = true, $echo = true ) {
        return $this->__checked_selected_helper( $checked, $current, $echo, 'checked' );
    }

    protected function __checked_selected_helper( $helper, $current, $echo, $type ) {
        if ( (string) $helper === (string) $current )
            $result = " $type='$type'";
        else
            $result = '';

        if ( $echo )
            echo $result;

        return $result;
    }

    public function selected( $selected, $current = true, $echo = true ) {
        return $this->__checked_selected_helper( $selected, $current, $echo, 'selected' );
    }

    public function esc_textarea( $text ) {
        return htmlspecialchars( $text, ENT_QUOTES);
    }

    public function parse_args( $args, $defaults = '' ) {
        if ( is_object( $args ) )
            $r = get_object_vars( $args );
        elseif ( is_array( $args ) )
            $r =& $args;
        else
            $this->parse_str( $args, $r );

        if ( is_array( $defaults ) )
            return array_merge( $defaults, $r );
        return $r;
    }

    protected function parse_str( $string, &$array ) {
        parse_str( $string, $array );
        if ( get_magic_quotes_gpc() )
            $array = $this->stripslashes_deep( $array );
    }

    /**
     * Sanitizes an HTML classname to ensure it only contains valid characters.
     *
     * Strips the string down to A-Z,a-z,0-9,_,-. If this results in an empty
     * string then it will return the alternative value supplied.
     *
     * @todo Expand to support the full range of CDATA that a class attribute can contain.
     *
     * @since 2.8.0
     *
     * @param string $class    The classname to be sanitized
     * @param string $fallback Optional. The value to return if the sanitization ends up as an empty string.
     *  Defaults to an empty string.
     * @return string The sanitized value
     */
    public function sanitize_html_class( $class, $fallback = '' ) {
        //Strip out any % encoded octets
        $sanitized = preg_replace( '|%[a-fA-F0-9][a-fA-F0-9]|', '', $class );

        //Limit to A-Z,a-z,0-9,_,-
        $sanitized = preg_replace( '/[^A-Za-z0-9_-]/', '', $sanitized );

        if ( '' == $sanitized && $fallback ) {
            return $this->sanitize_html_class( $fallback );
        }
        return $sanitized;
    }
    /*
     * return array of panel settings
     */
    public function getPanelSettings(){
        return $this->_panelSettings;
    }

    /**
     * @param $attributes
     * @param $widget
     *
     * @return mixed
     */
    public function widget_attributes( $attributes, $widget ){
        if( ! empty( $widget['label'] ) ) {
            $attributes[ 'data-label' ] = $widget['label'];
        }
        return $attributes;
    }

    public function widget_style($widget_id) {
        return $this->_coreRegistry->registry($widget_id);
    }

    /**
     * Remove slashes from a string or array of strings.
     *
     * This should be used to remove slashes from data passed to core API that
     * expects data to be unslashed.
     *
     * @since 3.6.0
     *
     * @param string|array $value String or array of strings to unslash.
     * @return string|array Unslashed $value
     */
    public function wp_unslash( $value ) {
        return $this->stripslashes_deep( $value );
    }

    /**
     * Navigates through an array, object, or scalar, and removes slashes from the values.
     *
     * @since 2.0.0
     *
     * @param mixed $value The value to be stripped.
     * @return mixed Stripped value.
     */
    public function stripslashes_deep( $value ) {
        return $this->map_deep( $value, array($this,'stripslashes_from_strings_only') );
    }

    /**
     * Callback function for `stripslashes_deep()` which strips slashes from strings.
     *
     * @since 4.4.0
     *
     * @param mixed $value The array or string to be stripped.
     * @return mixed $value The stripped value.
     */
    protected function stripslashes_from_strings_only( $value ) {
        return is_string( $value ) ? stripslashes( $value ) : $value;
    }

    /**
     * Maps a function to all non-iterable elements of an array or an object.
     *
     * This is similar to `array_walk_recursive()` but acts upon objects too.
     *
     * @since 4.4.0
     *
     * @param mixed    $value    The array, object, or scalar.
     * @param callable $callback The function to map onto $value.
     * @return mixed The value with the callback applied to all non-arrays and non-objects inside it.
     */
    protected function map_deep( $value, $callback ) {
        if ( is_array( $value ) ) {
            foreach ( $value as $index => $item ) {
                $value[ $index ] = $this->map_deep( $item, $callback );
            }
        } elseif ( is_object( $value ) ) {
            $object_vars = get_object_vars( $value );
            foreach ( $object_vars as $property_name => $property_value ) {
                $value->$property_name = $this->map_deep( $property_value, $callback );
            }
        } else {
            $value = call_user_func( $callback, $value );
        }

        return $value;
    }

    /**
     * Process raw widgets that have come from the Page Builder front end.
     *
     * @param array $widgets An array of widgets from panels_data.
     * @param array $old_widgets
     * @param bool $escape_classes Should the class names be escaped.
     * @param bool $force
     *
     * @return array
     */
    public function process_raw_widgets( $widgets, $old_widgets = array(), $escape_classes = false, $force = false ) {
        if ( empty( $widgets ) || ! is_array( $widgets ) ) {
            return array();
        }

        $old_widgets_by_id = array();
        if( ! empty( $old_widgets ) ) {
            foreach( $old_widgets as $widget ) {
                if( ! empty( $widget[ 'panels_info' ][ 'widget_id' ] ) ) {
                    $old_widgets_by_id[ $widget[ 'panels_info' ][ 'widget_id' ] ] = $widget;
                    unset( $old_widgets_by_id[ $widget[ 'panels_info' ][ 'widget_id' ] ][ 'panels_info' ] );
                }
            }
        }

        foreach( $widgets as $i => & $widget ) {
            if ( ! is_array( $widget ) ) {
                continue;
            }

            if ( is_array( $widget ) ) {
                $info = (array) ( is_array( $widget['panels_info'] ) ? $widget['panels_info'] : $widget['info'] );
            } else {
                $info = array();
            }
            unset( $widget['info'] );

            $info[ 'class' ] = $this->fix_namespace_escaping($info[ 'class' ]);

            if( $escape_classes ) {
                // Escaping for namespaced widgets
                $info[ 'class' ] = preg_replace( '/\\\\+/', '\\\\\\\\', $info['class'] );
            }
            $widget['panels_info'] = $info;
        }

        return $widgets;
    }
    /**
     * Fix class names that have been incorrectly escaped
     *
     * @param $class
     *
     * @return mixed
     */
    public function fix_namespace_escaping( $class ){
        return preg_replace( '/\\\\+/', '\\', $class );
    }
    /*
     * check if rtl is enable and actived
     * return boolean
     */
    public function isRtl() {
        return false;
    }

    public function resizeImage($image, $width = null, $height = null) {
        $imageName = basename($image);
        $imageResized = $this->_filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)->getAbsolutePath('resized/'.$width.'/').$imageName;
        if (!file_exists($imageResized)) { // Only resize image if not already exists.
            //create image factory...
            $imageResize = $this->_imageFactory->create();         
            $imageResize->open($image);
            $imageResize->constrainOnly(TRUE);         
            $imageResize->keepTransparency(TRUE);         
            $imageResize->keepFrame(FALSE);         
            $imageResize->keepAspectRatio(TRUE);         
            $imageResize->resize($width,$height);  
            //destination folder                
            $destination = $imageResized ;    
            //save image      
            $imageResize->save($destination);         
        } 
        $resizedURL = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA).'resized/'.$width.'/'.$imageName;

        return $resizedURL;
    }

    public function getStoreId() {
        return $this->_storeManager->getStore()->getId();
    }
}

