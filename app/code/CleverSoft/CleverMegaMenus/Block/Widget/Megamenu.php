<?php
/**
 * @category    CleverSoft
 * @package     CleverMegaMenus
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverMegaMenus\Block\Widget;
use Magento\Framework\View\Element\Template;
use CleverSoft\CleverMegaMenus\Model\MegamenuFactory as MegamenuFactory;

class Megamenu extends Template implements \Magento\Widget\Block\BlockInterface
{
    protected $_megamenuFactory;
    protected $_categoriesTree;
    protected $_filterProvider;
    protected $_storeManager;
    protected $_blockFactory;
    protected $_blockFilter;
    protected $_menuObject;
    protected $_catTree;
    protected $_menuContentArray;
    protected $htmlHelper;
    protected $catsTHtml;
    protected $_template = 'menu.phtml';

    public function __construct(
        Template\Context $context,
        MegamenuFactory $megamenuFactory,
        \Magento\Cms\Model\Template\FilterProvider $filterProvider,
        \Magento\Cms\Model\BlockFactory $blockFactory,
        \CleverSoft\CleverMegaMenus\Block\Widget\Html\SubCatsHtml $catsTHtml,
        \CleverSoft\CleverMegaMenus\Helper\HtmlHelper $htmlHelper,
        \CleverSoft\CleverMegaMenus\Block\Widget\Html\SubCatsHtml $catsTree,
        \Magento\Framework\App\Http\Context $httpContext,
        array $data = []
    ){
        parent::__construct($context, $data);
        $this->_megamenuFactory = $megamenuFactory;
        $this->httpContext = $httpContext;
        $this->_catTree = $catsTree;
        $this->catsTHtml = $catsTHtml;
        $this->_filterProvider = $filterProvider;
        $this->htmlHelper = $htmlHelper;
        $this->_storeManager = $context->getStoreManager();
        $this->_blockFactory = $blockFactory;
        $storeId = $this->_storeManager->getStore()->getId();
        $this->_blockFilter = $this->_filterProvider->getBlockFilter()->setStoreId($storeId);
//        $this->addData([
//            'cache_lifetime' => 86400,
//            'cache_tags' => ['MEGAMENU']
//        ]);
    }
    public function getMenuObject(){
        if(!$this->_menuObject){
            $identifier = $this->getMenu();
            $megamenu = $this->_megamenuFactory->create();
            $col = $megamenu->getCollection()
                ->addFieldToFilter('is_active',1)
                ->addFieldToFilter('identifier',$identifier);
            if($col->count() > 0){
                $this->_menuObject = $col->getFirstItem();
            }else{
                $this->_menuObject = $col;
            }
        }
        return $this->_menuObject;
    }
    protected function _toHtml()
    {
        return $this->_blockFilter->filter(parent::_toHtml());
    }
    public function openTag($items,$i){
        $curDepth = $items[$i]->depth;
        $prevDepth = isset($items[$i-1])?$items[$i-1]->depth:$curDepth;
        if($curDepth == $prevDepth){
            $html = '<li onclick="void(0)"';
        }elseif($curDepth > $prevDepth){
            $sub_align = (isset($items[$i-1]->content->sub_align) &&  $items[$i-1]->content->sub_align != '')? $items[$i-1]->content->sub_align : '';
            if (isset($items[$i-1]->content->dropdown_width) &&  $items[$i-1]->content->dropdown_width != '') {
                $split = preg_split('/(?<=[0-9])(?=[a-z]+)/i',trim($items[$i-1]->content->dropdown_width));
                $left = ';left:-'.(intval($split[0])/2).$split[1];
                $style = 'width:'.$items[$i-1]->content->dropdown_width;
            } else {
                $style = '';
                $left = '';
            }
            switch ($sub_align) {
                case "2" :
                    $style = $style.$left;
                    $class = '';
                    break;
                case "3" :
                    $style = '';
                    $class =' container';
                    break;
                default:
                    $class = '';
                    break;
            }
            $class .=' slidedown';
            if(isset($items[$i-1]->content->hide_sub_menu_mobile) && $items[$i-1]->content->hide_sub_menu_mobile == 1 ) $html = '<ul class="clever-mega-menu-sub hidden-xs '.$class.'" style="'.$style.'"><li';
            else $html = '<ul class="clever-mega-menu-sub '.$class.'" style="'.$style.'"><li';
        }else{
            $html = '</li><li onclick="void(0)"';
        }
        return $html;
    }
    public function closeTag($items,$i){
        $curDepth = $items[$i]->depth;
        $nextDepth = isset($items[$i+1])?$items[$i+1]->depth:0;
        if($curDepth == $nextDepth){
            $html = '</li>';
        }elseif($curDepth > $nextDepth){
            $html = str_repeat('</li></ul>',$curDepth-$nextDepth);
        }else{
            $html = '';
        }
        return $html;
    }

    public function getHtmlHelper(){
        return $this->htmlHelper;
    }

    public function getCategoryTreeBlock(){
        return $this->catsTHtml;
    }

    public function getIcon($content){
        if(isset($content->icon_type) && $content->icon_type == 0){
            return ($content->icon_font)?'<i class="menu-icon fa fa-'.$content->icon_font.'"></i>':'';
        } elseif(isset($content->icon_type) && $content->icon_type == 2){
            return ($content->clever_icon_font) ? '<i class="menu-icon cs-font clever-icon-'.$content->clever_icon_font.'"></i>':'';
        } else{
            return (isset($content->icon_img)) ?'<i class="menu-icon img-icon"><img src="'.$content->icon_img.'"></i>':'';
        }
    }

    public function hasChildren($items,$i){
        $curDepth = $items[$i]->depth;
        $nextDepth = isset($items[$i+1])?$items[$i+1]->depth:$curDepth;
        return ($nextDepth > $curDepth);
    }
    public function getBackgroundStyle($content){
        switch ($content->bg_position){
            case 'left_top':
                return "left:{$content->bg_position_x}px; top:{$content->bg_position_y}px"; break;
            case 'left_bottom':
                return "left:{$content->bg_position_x}px; bottom:{$content->bg_position_y}px"; break;
            case 'right_top':
                return "right:{$content->bg_position_x}px; top:{$content->bg_position_y}px"; break;
            case 'right_bottom':
            default:
                return "right:{$content->bg_position_x}px; bottom:{$content->bg_position_y}px"; break;
        }
    }
    public function getMenuContentArray(){
        if(!$this->_menuContentArray){
            $menu = $this->getMenuObject();
            $this->_menuContentArray = json_decode($menu->getMenucontent());
        }
        return $this->_menuContentArray;
    }
    public function getItemCSSClass($items,$i)
    {
        $item = $items[$i];
        $depth = (int)$item->depth;
        $content = $item->content;

        $class[] = "clever-mega-menu-item level{$depth} {$content->class}";
        if($depth == 0){
            if (isset($content->hide_item_mobile) && $content->hide_item_mobile == 1) $class[] = 'hidden-xs';
            if (isset($content->sub_align) && $content->sub_align != '') {
                switch ($content->sub_align) {
                    case '0':
                        $class[]='clever-sub_drop_to_right';
                        break;

                    case '1':
                        $class[]='clever-sub_drop_to_left';
                        break;

                    case '2':
                        $class[]='clever-sub_drop_to_center';
                        break;
                    case '3':
                        $class[]='clever-menu-justify';
                        break;
                }
            }
            $class[] = 'level-top';
        }

        $type = $this->_menuObject->getData('direction');
        switch ($type) {
            case '0' :
                $add_class = ' container';
                break;
            default:
                $add_class = '';
                break;
        }

        switch ($item->item_type){
            case 'page':
                if($this->hasChildren($items,$i)){
                    $class[] = 'parent';
                }
                break;
            case 'category':
                $child = $this->_catTree->getChildrenNodes($item->content->category);
                $_class = $child > 0 ? 'parent' : '';
                $class[] = $_class.' category-menu';
                if($content->display_type == 1){
                    $class[] = 'no-dropdown';
                }
                break;
            case 'link':
                if($this->hasChildren($items,$i)){
                    $class[] = 'parent';
                }
                break;
            case 'text':
                $class[] = $add_class.' text-content'; break;
            default:
        }
        return implode(' ',$class);
    }

//    public function getIdentities()
//    {
//        return [\CleverSoft\CleverMegaMenus\Model\Megamenu::CACHE_TAG . '_' . $this->getMenu()];
//    }
}