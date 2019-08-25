<?php
/**
 * @category    CleverSoft
 * @package     CleverLayeredNavigation
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */
 
namespace CleverSoft\CleverLayeredNavigation\Block\Navigation;

use CleverSoft\CleverLayeredNavigation\Api\Data\FilterSettingInterface;
use CleverSoft\CleverLayeredNavigation\Helper\FilterSetting;
use CleverSoft\CleverLayeredNavigation\Helper\Data as LayeredNavigationHelper;
use CleverSoft\CleverLayeredNavigation\Helper\UrlBuilder;
use CleverSoft\CleverLayeredNavigation\Model\Layer\Filter\Item;
use CleverSoft\CleverLayeredNavigation\Model\Source\DisplayMode;
use Magento\Catalog\Model\Layer\Filter\FilterInterface;

class FilterRenderer extends \Magento\LayeredNavigation\Block\Navigation\FilterRenderer
{
    /** @var  FilterSetting */
    protected $settingHelper;

    /** @var  UrlBuilder */
    protected $urlBuilder;

    /** @var  FilterInterface */
    protected $filter;

    /**
     * @var LayeredNavigationHelper
     */
    protected $helper;

    protected $html = '';

    protected $olle = 2;

    protected $_drop = '-';

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        FilterSetting $settingHelper,
        UrlBuilder $urlBuilder,
        LayeredNavigationHelper $helper,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->settingHelper = $settingHelper;
        $this->urlBuilder = $urlBuilder;
        $this->helper = $helper;
        $this->_objectManager = $objectManager;
    }

    public function render(FilterInterface $filter)
    {
        $this->filter = $filter;
        $setting = $this->settingHelper->getSettingByLayerFilter($filter);
        $template = $this->getTemplateByFilterSetting($setting);
        $this->setTemplate($template);
        $this->assign('filterSetting', $setting);
        $this->assign('tooltipUrl', $this->helper->getTooltipUrl());

        return parent::render($filter);
    }

    protected function getTemplateByFilterSetting(FilterSettingInterface $filterSetting)
    {
        switch($filterSetting->getDisplayMode()) {
            case DisplayMode::MODE_SLIDER:
                $template = "layer/filter/slider.phtml";
                break;
            case DisplayMode::MODE_DROPDOWN:
                $template = "layer/filter/dropdown.phtml";
                break;
            case DisplayMode::MODE_FROMTOONLY:
                $template = "layer/filter/fromtoonly.phtml";
                break;
            default:
                $template = "layer/filter/default.phtml";
                break;
        }
        return $template;
    }

    public function checkedFilter($filterItem)
    {
        $data = $this->getRequest()->getParam($filterItem->getFilter()->getRequestVar());
        if (!empty($data)) {
            $ids = explode(',', $data);
            if (in_array($filterItem->getValue(), $ids)) {
                return 1;
            }
        }
        return 0;
    }
    public function checkedFilterFromTo()
    {
        $data = $this->getRequest()->getParam('price');
        if (!empty($data)) {
            return $data;
        }
        return 0;
    }

    public function getClearUrl()
    {
        if (!array_key_exists('filterItems', $this->_viewVars) || !is_array($this->_viewVars['filterItems'])) {
            return '';
        }
        $items = $this->_viewVars['filterItems'];

        foreach ($items as $item) {
            /** @var Item $item */

            if ($this->checkedFilter($item)) {
                return $item->getRemoveUrl();
            }
        }

        return '';
    }

    public function getSliderUrlTemplate()
    {
        return $this->urlBuilder->buildUrl($this->filter, 'clevershopby_slider_from-clevershopby_slider_to');
    }

    public function getCurrencySymbol()
    {
        return $this->_storeManager->getStore()->getCurrentCurrency()->getCurrencySymbol();
    }

    public function escapeId($data)
    {
        return str_replace(",", "_", $data);
    }

    public function resetHtml(){
        $this->html = '';
    }

    public function getChildCategoriesTemplate($filter,$filterSetting,$drop){
        if($drop) {
            foreach ($filter as $filterItem){
                $disabled = ($filterItem->getCount() == 0) ? ' disabled' : "";
                $selected = ($this->checkedFilter($filterItem)) ? ' selected' : "";
                $url = $this->escapeUrl($filterItem->getUrl());
                $label = $filterItem->getLabel();
                if ($filterSetting->isShowProductQuantities()) {
                    $label .= ' <span class="count">(' . $filterItem->getCount() . ')</span>';
                }
                $this->html .= '<option value="'.$url .'" '.$disabled . $selected .'>'.$this->_drop.' '.$label .'</option>';
                if($filterItem->getChild()) {
                    $this->_drop .= ' -';
                    $this->getChildCategoriesTemplate($filterItem->getChild(),$filterSetting,true);
                }
            }
        } else {
            $this->html .= '<ol class="items items-children level'.$this->olle.'">';
            foreach ($filter as $filterItem){
                if ($filterItem->getCount() > 0){
                    $this->html .= '<li class="item" data-label="'.$this->escapeHtml($filterItem->getLabel()).'">';
                    $checked =  $this->checkedFilter($filterItem) ? ' checked' : '';
                    $this->html .= '<a class="cleversoft_shopby_filter_item_'.$this->escapeId($filterItem->getValue()).'" href="'.$this->escapeUrl($filterItem->getUrl()) .'">';
                    if ($filterSetting->isMultiselect()){
                        $this->html .= '<input type="checkbox" '.$checked.'/>';
                        $this->html .= '<span class="checkbox-material"><span class="check"></span></span>';
                    }
                    $this->html .= $filterItem->getLabel();
                    if ($filterSetting->isShowProductQuantities()){
                        $this->html .= '<span class="count pull-right">'.$filterItem->getCount();
                        $this->html .= '</span>';
                    }
                    $this->html .= '</a>';
                    if($filterItem->getChild()) {
                        $this->olle = $this->olle + 1;
                        $this->getChildCategoriesTemplate($filterItem->getChild(),$filterSetting,false);
                    }
                    $this->html .= '<script type="text/x-magento-init">
                        {
                            ".cleversoft_shopby_filter_item_'.$this->escapeId($filterItem->getValue()).'": {
                                "cleverLayeredNavigationFilterItemDefault": {}
                            }
                        }
                        </script>';
                    $this->html .= '</li>';
                }
            }
            $this->html .= '</ol>';
        }
        return $this->html;
    }
}
