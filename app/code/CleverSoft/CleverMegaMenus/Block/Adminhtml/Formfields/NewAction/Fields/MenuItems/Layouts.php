<?php
/**
 * @category    CleverSoft
 * @package     CleverMegaMenus
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author        ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverMegaMenus\Block\Adminhtml\Formfields\NewAction\Fields\MenuItems;

use \Magento\Framework\App\ObjectManager;
use Zend\Form\Annotation\Instance;

class Layouts extends \Magento\Backend\Block\Template
{
    protected $_assetRepo;
    protected $_menuTypes = ['page','link','text','category'];
    protected $pageCollection;
    protected $_coreRegistry;
    protected $_collection;
    protected $_icons;
    protected $_clevericons;
    protected $_layouts = [];
    protected $filterProvider;
    protected $htmlHelper;
    protected $_columnLayouts = [];

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \CleverSoft\CleverMegaMenus\Model\ResourceModel\Megamenu\Collection $collection,
        \Magento\Framework\Registry $registry,
        \CleverSoft\CleverMegaMenus\Helper\HtmlHelper $htmlHelper,
        \Magento\Cms\Model\Template\FilterProvider $filterProvider,
        \CleverSoft\CleverMegaMenus\Helper\Adminhtml\IconHtml $iconHtml,
        \CleverSoft\CleverMegaMenus\Helper\Adminhtml\CleverIconHtml $cleverIconHtml,
        \Magento\Cms\Model\ResourceModel\Page\Collection $pageCollection,
        array $data = [])
    {
        $this->_assetRepo = $context->getAssetRepository();
        $this->_collection = $collection;
        $this->_coreRegistry = $registry;
        $this->_icons = $iconHtml;
        $this->filterProvider = $filterProvider;
        $this->htmlHelper = $htmlHelper;
        $this->_clevericons = $cleverIconHtml;
        $this->pageCollection = $pageCollection;
        parent::__construct($context, $data);
    }
    /*
     * get the leftContent
     */
    public function getLeftContentByType($type){
        $content = new \stdClass();
        switch ($type) {
            case 'link':
            case 'page':
            case 'category':
                return $this->getContent($content,$type,true,0);
                break;
            case 'text' :
                return $this->getContentTypeText($content,0);
                break;

        }
    }
    /*
     * get Icon html
     */
    public function getIconHtml(){
        return $this->_icons->iconTemplateHelper();
    }
    /*
     * get clever Icon html
     */
    public function getCleverIconHtml(){
        return $this->_clevericons->iconTemplateHelper();
    }

    /*
     * $item @stdclass
     */
    public function getContentByType($item) {
        if(empty($item->content)) $item->content = new \stdClass();
        else {
            if(isset($item->content->layout)) {
                if (($item->content->layout !== '1' && in_array(trim($item->content->layout),$this->htmlHelper->getOldLayouts())) || ($item->content->layout === '1' && !is_array($item->content->content) && in_array(trim($item->content->layout),$this->htmlHelper->getOldLayouts()))) {
                    $item->content->layout = array_search(trim($item->content->layout),$this->htmlHelper->getOldLayouts());
                }
            }
        }
        switch ($item->item_type) {
            case 'link':
            case 'page':
            case 'category':
                return $this->getContent($item->content,$item->item_type,false,$item->depth);
                break;
            case 'text' :
                return $this->getContentTypeText($item->content,$item->depth);
                break;

        }
    }

    public function getContent($content,$type,$left = false,$depth) {
        return $this->getGeneralHtmlPart($content,$type,true,$left,$depth);
    }

    public function getContentTypeText($content,$depth) {
        $html = $this->getGeneralHtmlPart($content,'text',false,false,$depth);
        $html .= $this->htmlHelper->getHeadingHtmlPart(__('GRID DROPDOWN'));
        $html .= $this->getLayoutColumnHtmlPart($this->_layouts,$content);
        $html .= $this->getTextAreaHtmlPart($content);
        $html .= $this->getLabelHtmlPart(__('Ex') . ' : <a class="full-view-img" data-href="' . $this->htmlHelper->getCleverImagePath('menu/background.jpg') . '" onclick="cleverButtonElement.fullImage(this)" href="javascript:void(0)"><img src="' . $this->htmlHelper->getCleverImagePath('menu/background_small.jpg') . '" /></a> <a class="full-view-link" onclick="cleverButtonElement.fullImage(this)" data-href="' . $this->htmlHelper->getCleverImagePath('menu/background.jpg') . '" href="javascript:void(0)">' . __('Click to view example') . '</a>','<span style="margin-top: 12.5px;display: inline-block;">' . __('Dropdown Background Image') . '</span>');
        $html .= $this->getImageHtmlPart('background',__('Get image from library or enter other URL'),__('Image Library'),(isset($content->background) ? $content->background : ''),'image',false,'');
        $html .= $this->getSelectHtmlPart('bg_position', __('Position'), ['left_top'=>__('Left - Top'), 'left_bottom' => __('Left - Bottom'), 'right_top' => __('Right - Top'), 'right_bottom' => __('Right - Bottom')], (isset($content->bg_position) ? $content->bg_position : 'left_top'));
        $html .= $this->getTextHtmlPart('bg_position_x',__('X (px)'), (isset($content->bg_position_x) ? $content->bg_position_x : ''));
        $html .= $this->getTextHtmlPart('bg_position_y',__('Y (px)'), (isset($content->bg_position_y) ? $content->bg_position_y : ''));
        return $html;
    }

    public function getImageHtmlPart($name,$placeholder,$label,$val,$type,$recommend = false,$style){
        if($recommend)
            $recommend = '<p class="content-note">'.__($recommend).'</p>';
        else $recommend = '';
        $unique = uniqid('clever-image-');
        $element = 'megamenu-image';
        return '
        <div class="menu-item-field row type__image field__'.$name.'" '.$style.'>
            <div class="col-xs-4 label"><span></span></div>
            <div class="col-xs-8">
                <input type="text" data-type="'.$type.'" data-name="'.$name.'"
                                     class="menu-field image-field field-'.$name.'" value="'.htmlspecialchars($val).'" id="'.$unique.'"
                                     onchange="cleverButtonElement.changePreviewIcon(this,this.value,1)"
                                     placeholder="'.$placeholder.'">
                <button class="content-btn content-col-image"  onclick="MediabrowserUtility.openDialog(\''.$this->getUrl('cms/wysiwyg_images/index').'element_name/'.$element.'/target_element_id/'.$unique.'\',\'\',\'\',\'\',{closed:\''.__('Closed').'\'})">
                    '. $label.'
                </button>
                <div class="preview-image-outer">
                    <div class="preview-image-inner">
                        <a class="preview-image" onclick="cleverButtonElement.fullImage(this)" data-href="'.$this->htmlHelper->getCleverImagePath('menu/placeholder.jpg').'" href="javascript:void(0)"><img
                            src="'.$this->htmlHelper->getCleverImagePath('menu/placeholder.jpg').'">
                        </a>
                        <button class="content-btn content-col-image-delete" onclick="cleverButtonElement.removePreviewImage(this)"><i class="fa fa-remove"></i></button>
                    </div>
                </div>
                <p class="content-note">'.__('Allow file types: jpg, jpeg, png, gif').'</p>
                '.$recommend.'
            </div>
        </div>
        ';
    }

    /*
     * $content @array
     */
    public function getIconFontHtmlPart($content,$left = false)
    {
        $_iconStyle = $left ? '' : (($content->icon_type == 0) ? '' : 'style="display:none"');
        $_cleverIconStyle = $left ? 'style="display:none"' : (($content->icon_type == 2) ? '' : 'style="display:none"');
        $_imageStyle = $left ? 'style="display:none"' : (($content->icon_type == 1) ? '' : 'style="display:none"');
        $event = [
            'action'=>'cleverButtonElement.eventChangeIconChooser(this);',
            'event'=> 'onchange'
        ]
        ;
        $indexType = [0,1,2];
        $html =
            $this->getSelectHtmlPart('icon_type',__('Icon Font'),[$indexType[0]=>__('Fontawesome icons'),$indexType[2]=>__('Cleverfont icons'),$indexType[1]=>__('Select from library')],(isset($content->icon_type) ? $content->icon_type : 0),$event).
            $this->htmlHelper->getIconHtmlPart('icon_font',__('Icon Font Library'),(isset($content->icon_font) ? $content->icon_font : ""),'icon','clever-script-icons-html','fa fa-',$_iconStyle,$indexType[0]).
            $this->getImageHtmlPart('icon_img', __('Get image from library or enter other URL'),__('Image Icons Library'),(isset($content->icon_img) ? $content->icon_img : ''),'image',__('Recommended size: at least 32px × 32px'),$_imageStyle).
            $this->htmlHelper->getIconHtmlPart('clever_icon_font', __('Clever Icon Font Library'),(isset($content->clever_icon_font) ? $content->clever_icon_font : ""),'clever_icon','clever-script-clever_icons-html','cs-font clever-icon-',$_cleverIconStyle,$indexType[2])
        ;
        $html .= '<script>window.iconSwitchChooserParam = '.json_encode(['icon_font','icon_img','clever_icon_font']).', window.prefixClass = "field__"</script>';
        return $html;
    }

    public function getPreviewHeaderIcon($content) {
        if(!isset($content->icon_type)) return '';
        switch ($content->icon_type) {
            case 0:
                return empty($content->icon_font) ? '' : '<i class="fa fa-'.$content->icon_font.'" ></i>';
                break;
            case 2:
                return empty($content->clever_icon_font) ? '' : '<i class="cs-font clever-icon-'.$content->clever_icon_font.'"></i>';
                break;
            case 1:
                //icon_img
                return empty($content->icon_img) ? '' :  '<a class="preview-image" onclick="cleverButtonElement.fullImage(this)" href="javascript:void(0)" data-href="'.$this->filterProvider->getPageFilter()->filter($content->icon_img).'"><img src="'.$this->filterProvider->getPageFilter()->filter($content->icon_img).'" /></a>';
                break;
            default:
                return '';
                break;
        }
    }

    /*
     * get general field
     */
    public function getGeneralHtmlPart($content,$type, $iconFonts = true,$left = false,$depth) {
        $html = '';
        if($type == 'page' and $left) {
            $html .= $this->getPagesMenu();
            $html .= $this->getGeneralHtml($type,$content,$depth);
        } else {
            $html .= $this->getGeneralHtml($type,$content,$depth);
        }
        if($iconFonts) $html .= $this->getIconFontHtmlPart($content,$left);
        return $html;
    }

    public function getContentTmplDepthZero(){
        $generalContent = $this->htmlHelper->createGeneralFieldContent();
        $html = '';
        foreach ($generalContent as $gC) {
            if (!isset($gC['depth'])) continue;
            switch ($gC['type']) {
                case 'text':
                    $html .= $this->getTextHtmlPart($gC['name'],$gC['label'],'',false,$gC['depth']);
                    break;
                case 'checkbox':
                    $html .= $this->getCheckBoxHtmlPart('',$gC['name'],$gC['label'],false,false,$gC['depth']);
                    break;
                case 'select':
                    $html .= $this->getSelectHtmlPart($gC['name'],$gC['label'],$gC['options'],'',false,false,$gC['depth']);
                    break;
                case 'label':
                    $html .= $this->getLabelHtmlPart($gC['value'],$gC['label'],false,$gC['depth']);
                    break;
            }
        }
        return $html;
    }

    public function getGeneralHtml($type,$content,$depth) {
        $html = '';
        $generalContent = $this->htmlHelper->createGeneralFieldContent();
        foreach ($generalContent as $gC) {
            $_depth = isset($gC['depth']) ? $gC['depth'] : false;
            if((isset($gC['for'])&& in_array($type, $gC['for']) ) || !isset($gC['for'])) {
                switch ($gC['type']) {
                    case 'text':
                        $html .= $this->getTextHtmlPart($gC['name'],$gC['label'],isset($content->{$gC['name']}) ? $content->{$gC['name']} : '',$depth,$_depth,(isset($gC['event']) ? $gC['event'] : false));
                        break;
                    case 'checkbox':
                        $html .= $this->getCheckBoxHtmlPart(isset($content->{$gC['name']}) ? $content->{$gC['name']} : '',$gC['name'],$gC['label'],false,$depth,$_depth);
                        break;
                    case 'select':
                        $html .= $this->getSelectHtmlPart($gC['name'],$gC['label'],$gC['options'],isset($content->{$gC['name']}) ? $content->{$gC['name']} : '',false,$depth,$_depth);
                        break;
                    case 'label':
                        $html .= $this->getLabelHtmlPart($gC['value'],$gC['label'],$depth,$_depth);
                        break;
                    case 'category':
                        $html .= $this->getCategoryHtmlPart($gC['name'],$gC['label'],isset($content->{$gC['name']}) ? $content->{$gC['name']} : '');
                        break;
                }
            }
        }
        return $html;
    }

    public function getCategoryHtmlPart($name, $label, $value) {
        $unique = uniqid('category_');
        $select = __('Select');
        return '
            <div class="menu-item-field row type__category field__'.$name.'">
                <div class="col-xs-4 label"><span>'.$label.'</span></div>
                <div class="col-xs-8"><input class="menu-field field-'.$name.'" data-type="category" data-name="'.$name.'"
                                             id="'.$unique.'" type="text" value="'.$value.'">
                    <button class="content-btn content-col-'.$name.'" onclick="'.$unique.'.choose()">'.$select.'</button>
                </div>
            </div>
        ';
    }

    /*
     *$name,$label,$value @string
     */
    public function getTextHtmlPart($name,$label,$value,$depth = false,$_depth = false,$event = false){
        if($_depth) {
            if ($depth != ($_depth-1)) return '';
            $class = 'hide-on-trigger-parent';
        } else {
            $class = '';
        }
        $addHtml = '';
        if($event) {
            $addHtml = $event['event'].'="'.$event['action'].'"';
        }

        return '
        <div class="menu-item-field row type__text field__'.$name.' '.$class.'">
            <div class="col-xs-4 label"><span>'.$label.'</span></div>
            <div class="col-xs-8"><input data-type="text" data-name="'.$name.'" type="text" class="menu-field field-'.$name.'" value="'.htmlspecialchars($value).'" '.$addHtml.'> </div>
        </div>
        ';
    }

    public function getSelectHtmlPart($name,$label,$options,$selected,$event=false,$depth = false,$_depth = false) {
        if($_depth) {
            if ($depth != ($_depth-1)) return '';
            $class = 'hide-on-trigger-parent';
        } else {
            $class = '';
        }
        $html = '';
        $addHtml = '';
        if($event) {
            $addHtml = $event['event'].'="'.$event['action'].'"';
        }
        foreach($options as $key=>$opt) {
            $html .='<option value="'.$key.'" '.($key == $selected ? 'selected' : '').'>'.$opt.'</option>';
        }
        return '
            <div class="menu-item-field row type__select field__'.$name.' '.$class.'">
                <div class="col-xs-4 label"><span>'.$label.'</span></div>
                <div class="col-xs-8">
                    <select data-type="select" data-name="'.$name.'" class="menu-field field-'.$name.'" '.$addHtml.'>
                        '.$html.'
                    </select>
                </div>
            </div>
        ';
    }

    public function getCheckBoxHtmlPart($val,$name,$label,$left = false,$depth = false,$_depth = false,$removeAfterAddJs = false){
        if($_depth) {
            if ($depth != ($_depth-1)) return '';
        }
        if($left) {
            $html = '
                <div class="col-xs-4"> <input data-type="checkbox" data-name="'.$name.'" type="checkbox" class="menu-field field-'.$name.'" value="'.$val.'"></div>
            ';
        } else {
            $html = '
                <div class="col-xs-1">
                    <input data-type="checkbox" data-name="'.$name.'" type="checkbox" class="menu-field field-'.$name.'" '.((int)$val ? 'checked' : '').' value="'.(int)$val.'"></div>
                <div class="col-xs-7"></div>
            ';

        }
        $actJs = '';
        if($removeAfterAddJs) {
            $actJs = 'data-removeaddjs="1"';
        }

        return '
            <div class="menu-item-field row type__checkbox field__'.$name.' '.($left ? '' : 'hide-on-trigger-parent').'" '.$actJs.'>
                <div class="col-xs-'.($left ? '8' : '4').' label"><span>'.$label.'</span></div>
                '.$html.'
            </div>

        ';
    }

    public function getLabelHtmlPart($val,$label,$depth = false,$_depth = false){
        if($_depth) {
            if ($depth != ($_depth-1)) return '';
        }
        return '
            <div class="menu-item-field row type__label field__label">
                <div class="col-xs-4 label"><span>'.$label.'</span></div>
                <div class="col-xs-8">
                    <div class="label-html">'.$val.'</div>
                </div>
            </div>
        ';
    }

    /*
    * create fields types
    */


    public function createConfigFieldType() {
        $previewIcon = [
            'page'=>'<i class="fa fa-file-text-o"></i>',
            'link'=>'<i class="fa fa-link"></i>',
            'text'=>'<i class="fa fa-file-code-o"></i>',
            'category'=>'<i class="fa fa-th-list"></i>',
        ];
        $fieldsLabel = [
            'page'=> __('Pages'),
            'link'=> __('Custom Link'),
            'text'=> __('Grid Dropdown'),
            'category'=> __('Categories List'),
        ];
        return ['previewIcon'=>$previewIcon, 'fieldsLabel' => $fieldsLabel];
    }

    public function getLayoutColumnHtmlPart($layouts,$content,$type = 'layout',$name = 'layout'){
        $html = '';
        foreach($layouts as $key=>$lt) {
            $html .= '
                <a href="javascript:void(0)" class="layout-row layout-'.$key.'" onclick="cleverButtonElement.changeSampleColumnsTemplate(this,['.implode(',',$lt).'],'.$key.')">             <span class="layout-col layout-col-'.$key.'"></span>            </a>
            ';
        }
        return '
            <div class="menu-item-field row type__layout field__'.$name.'">
                <div class="content-layout-wrap">
                    <input type="hidden" data-name="'.$type.'" data-type="'.$name.'" class="menu-field field-'.$name.'" value="'.(isset($content->layout)?$content->layout:'0').'">
                    <span class="preview-layout layout-'.(isset($content->layout)?$content->layout:'0').'" onclick="cleverButtonElement.toggleSampleColumns(this)">
                        <span class="layout-col layout-col-'.(isset($content->layout)?$content->layout:'0').'"></span>
                    </span>

                    <div class="content-layout-chooser" style="display: none;">
                        '.$html.'
                    </div>
                </div>
            </div>
        ';
    }


    public function getTextAreaHtmlPart($content,$name='content',$type='editor'){
        if(!isset($content->layout)) {
            $content->layout = 0;
            $content->content = [''];
        }
        $html = '';
        $menuColTemplate = 'clever-col-layout-sample';
        foreach($this->_layouts[$content->layout] as $key=>$val){
            $unique = uniqid('editor_');
            $html .= '
                <div class="content-col active" style="width:'.($this->htmlHelper->getPercentWidth($val)).'%">
                        <div class="content-col-inner">
                            <div class="content-heading">
                                <div class="content-actions">
                                    <button class="content-btn content-col-wysiwyg df-btn" onclick="cleverButtonElement.modalOpenWysiwyg(this)" title="'. __('Wysiwyg Editor') .'"><i class="fa fa-pencil"></i></button>
                                    <button class="content-btn content-col-widget df-btn" onclick="widgetTools.openDialog(\''.$this->getUrl('admin/widget') .'widget_target_id/'.$unique.'/\')" title="'.__('Widget') .'" >'.__('Widget') .'</button>
                                    <button class="content-btn content-col-template df-btn" onclick="Icons.openIconChooser(\''.$unique.'\',\''.$menuColTemplate.'\',\''.__('Choose template...') .'\',false)" title="'. __('Column Template') .'"><i class="fa fa-clipboard"></i></button>
                                </div>
                            </div>
                            <div class="content-body">
                                <textarea placeholder="'.__('&lt;p&gt;Insert HTML structure&lt;/p&gt;').'" id="'.$unique.'" data-type="'.$type.'" data-name="'.$name.'" class="menu-field field-'.$name.'">'.(is_array($content->content) ? $content->content[$key] : $content->content).'</textarea>
                            </div>
                        </div>
                </div>
            ';
        }
        return '
            <div class="menu-item-field row type__editor field__'.$name.'">
                <div class="content-row">
                    '.$html.'
                </div>
            </div>
        ';
    }

    public function getLayoutGrid() {
        return $this->_layouts;
    }

    public function _construct()
    {
        parent::_construct();
        $this->_columnLayouts = $this->htmlHelper->columnSampleLayouts();
        $this->_layouts = $this->htmlHelper->getLayouts();
    }

    public function getMenuTypes() {
        return $this->_menuTypes;
    }

    public function getMediaPath(){
        return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
    }

    public function getAllMenu(){
        return $this->_collection;
    }

    public function getCurrentMenu(){
        return $this->_coreRegistry->registry('megamenu');
    }

    public function getPagesMenu() {
        $pages = $this->pageCollection->toOptionIdArray();
        $html = '';
        foreach ($pages as $page) {
            $html .= $this->getCheckBoxHtmlPart($page['value'],'page_selections',__($page['label']),true,false,false,true);
        }
        return $html;
    }

    public function getColumnLayouts() {
        return $this->_columnLayouts;

    }
}

?>