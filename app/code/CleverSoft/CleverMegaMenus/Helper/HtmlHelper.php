<?php
/**
 * @category    CleverSoft
 * @package     CleverMegaMenus
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverMegaMenus\Helper;

class HtmlHelper extends \Magento\Framework\App\Helper\AbstractHelper {

    protected $_assetRepo;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Backend\Block\Template\Context $contextTemplate

    ) {
        $this->_assetRepo = $contextTemplate->getAssetRepository();
        parent::__construct($context);
    }

    /*
     *
     */
    public function getPercentWidth($layout) {
        $layout = explode('/',$layout);
        return count($layout) == 2 ? ((int)$layout[0]/(int)$layout[1])*100 : (int)$layout * 100;
    }

    /*
     *
     */
    public function columnSampleLayouts(){
        return [
            'sample01' => $this->getCleverImagePath('layout_extension/itp.jpg'),
            'sample03' => $this->getCleverImagePath('layout_extension/tp.jpg'),
            'sample05' => $this->getCleverImagePath('layout_extension/tpi.jpg'),
            'sample02' => $this->getCleverImagePath('layout_extension/itl.jpg'),
            'sample04' => $this->getCleverImagePath('layout_extension/tl.jpg'),
            'sample06' => $this->getCleverImagePath('layout_extension/tli.jpg')
        ];
    }
    /*
     *
     */
    public function getLayouts(){
        return [['1'], ['1/2', '1/2'], ['1/3', '1/3', '1/3'], ['1/4', '1/4', '1/4', '1/4'], ['1/6', '1/6', '1/6', '1/6', '1/6', '1/6'], ['1/3', '2/3'], ['2/3', '1/3'], ['1/4', '1/4', '1/2'], ['1/4', '1/2', '1/4'], ['1/2', '1/4', '1/4'], ['1/6', '1/6', '1/6', '1/6', '1/3'], ['1/3', '1/6', '1/6', '1/6', '1/6']];
    }
    /*
     *
     */
    public function getOldLayouts(){
        return ['1','1,1','1,1,1','1,1,1,1','1,1,1,1,1,1','1,2','2,1','1,1,2','2,1,1','1,2,1','1,1,1,1,2','2,1,1,1,1'];
    }
    /*
     *
     */
    public function getNewLayout($content) {
        $layout = $content->layout;
        $global = $this->getLayouts();
        if($layout == null) throw new \Exception(__('Your content does not have any layout'));
        if (($layout !== '1' && in_array(trim($layout),$this->getOldLayouts())) || ($layout === '1' && !is_array($content->content) && in_array(trim($layout),$this->getOldLayouts()))) {
            $index = array_search(trim($layout),$this->getOldLayouts());
            return $global[$index];
        } else {
            return $global[(int)$layout];
        }
    }
    /*
     *
     */
    public function getCleverImagePath($path){
        return $this->_assetRepo->getUrl('CleverSoft_CleverMegaMenus/images/' . $path);
    }

    public function getHeadingHtmlPart($heading){
        return '
        <div class="menu-item-field row type__heading ">
            <div class="heading"><span>'.$heading.'</span></div>
        </div>
        ';
    }

    public function getIconHtmlPart($name,$label,$val,$type,$tmpl,$class,$style,$options){
        $unique = uniqid($type);
        return '
        <div class="menu-item-field row type__icon field__'.$name.'" '.$style.'>
            <div class="col-xs-4 label"><span></span></div>
            <div class="col-xs-8">
                <input type="hidden" data-type="'.$type.'" data-name="'.$name.'" class="menu-field field-'.$name.'" value="'.$val.'" id="'.$unique.'" onchange="cleverButtonElement.changePreviewIcon(this,this.value,'.$options.')">
                <button class="content-btn content-col-icon" onclick="Icons.openIconChooser(\''.$unique.'\',\''.$tmpl.'\',\''.__('Insert Icons').'\', true)"> '.$label.'</button>
                <div class="preview-icon-outer">
                    <div class="preview-icon-inner">
                        <button class="content-btn content-col-icon-delete" onclick="cleverButtonElement.removePreviewIcon(this,\''.$type.'\')"><i class="fa fa-remove"></i></button>
                        <span class="icon preview-icon" id="preview-'.$unique.'"><i '.(!empty($val) ? 'class="'.$class.$val.'"' : '').'></i></span>
                    </div>
                </div>
            </div>
        </div>
        ';
    }

    public function createGeneralFieldContent(){
        return [
            ['label' => __('Item Name'), 'name' => 'label', 'type' => 'text','for'=>['page','link','category'],'event'=>['action'=>'cleverButtonElement.changeLabelOnKeyUp(this);','event'=>'onkeyup']],
            ['label' => __('Item Link'), 'name' => 'url', 'type' => 'text','for'=>['page','link','category']],
            ['label' => __('Parent Cat ID'), 'name' => 'category', 'type' => 'category','for'=>['category']],
            ['label' => __('Custom Class'), 'name' => 'class', 'type' => 'text','for'=>['text','link','category','page']],
            ['label' => __('Custom CSS Inline Style'), 'name' => 'style', 'type' => 'text','placeholder'=>'padding:20px; margin:30px;','for'=>['text']],
            ['label' => __('Display Type'), 'name' => 'display_type', 'type' => 'select',
                'options' => [__('Show categories list as a drop down menu of item title'),__('Show categories list just below item title')],
                'for'=>['category']
            ],
            ['label' => __('Dropdown Width'),'depth'=>1 , 'name' => 'dropdown_width', 'type' => 'text','placeholder'=>'600px','for'=>['text','link','category','page']],

            ['label' => __('Hide Text of This Item'),'depth'=>1 , 'name' => 'hide_text', 'type' => 'checkbox'],
            ['label' => __('Hide Item on Mobile') ,'depth'=>1 , 'name' => 'hide_item_mobile', 'type' => 'checkbox'],
            ['label' => __('Hide Sub Menu on Mobile') ,'depth'=>1 , 'name' => 'hide_sub_menu_mobile', 'type' => 'checkbox'],
            ['label' => __('Disable Link of This Item'),'depth'=>1,  'name' => 'disable_link_this_item', 'type' => 'checkbox'],


            ['label' => __('Sub Menu Align'),'depth'=>1, 'name' => 'sub_align', 'type' => 'select',
                'options' => [__('Drop To Right Side'),__('Drop To Left Side'),__('Drop To Center'),__('Full')]
            ],

            ['label' => __('Item Icon'), 'type' => 'label', 'value' => __('Ex') . ' : <i class="fa fa-diamond"></i>' . __('Diamond'),'for'=>['category','link','page']]
        ];
    }

}