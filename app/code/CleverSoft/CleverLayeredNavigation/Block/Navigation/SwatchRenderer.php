<?php
/**
 * @category    CleverSoft
 * @package     CleverLayeredNavigation
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */
 
namespace CleverSoft\CleverLayeredNavigation\Block\Navigation;

use Magento\Catalog\Model\ResourceModel\Layer\Filter\AttributeFactory;
use Magento\Eav\Model\Entity\Attribute;
use CleverSoft\CleverLayeredNavigation\Model\Source\DisplayMode;
use CleverSoft\CleverLayeredNavigation\Api\Data\FilterSettingInterface;
use CleverSoft\CleverLayeredNavigation\Helper\FilterSetting;
use CleverSoft\CleverLayeredNavigation\Helper\Data as LayeredNavigationHelper;
use CleverSoft\CleverLayeredNavigation\Helper\UrlBuilder;
use CleverSoft\CleverLayeredNavigation\Model\Layer\Filter\Item;
use Magento\Eav\Model\Entity\Attribute\Option;
use Magento\Catalog\Model\Layer\Filter\Item as FilterItem;

class SwatchRenderer extends \Magento\Swatches\Block\LayeredNavigation\RenderLayered
{
    protected $urlBuilderHelper;
    protected $filterSetting;
    /** @var  FilterSetting */
    protected $settingHelper;
    /**
     * @var LayeredNavigationHelper
     */
    protected $helper;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context, Attribute $eavAttribute,
        AttributeFactory $layerAttribute,
        FilterSetting $settingHelper,
        LayeredNavigationHelper $helper,
        \Magento\Swatches\Helper\Data $swatchHelper,
        \Magento\Swatches\Helper\Media $mediaHelper,
        \CleverSoft\CleverLayeredNavigation\Helper\UrlBuilder $urlBuilderHelper,
        array $data = []
    ) {
        parent::__construct(
            $context, $eavAttribute, $layerAttribute, $swatchHelper,
            $mediaHelper, $data
        );
        $this->helper = $helper;
        $this->settingHelper = $settingHelper;
        $this->urlBuilderHelper = $urlBuilderHelper;
    }

    /**
     * @param string $attributeCode
     * @param int $optionId
     * @return string
     */
    public function buildUrl($attributeCode, $optionId)
    {
        return $this->urlBuilderHelper->buildUrl($this->filter, $optionId);
    }

    public function setSwatchFilter(\Magento\Catalog\Model\Layer\Filter\AbstractFilter $filter)
    {
        $this->filter = $filter;
        $setting = $this->settingHelper->getSettingByLayerFilter($this->filter);
        $this->getTemplateByFilterSetting($setting);//setTemplate('widget/brands.phtml');
        $this->eavAttribute = $filter->getAttributeModel();
        return $this;
    }

    /**
     * @param FilterItem $filterItem
     * @param Option $swatchOption
     * @return array
     */
    protected function getOptionViewData(FilterItem $filterItem, Option $swatchOption)
    {
        $customStyle = '';
        $linkToOption = $this->buildUrl($this->eavAttribute->getAttributeCode(), $filterItem->getValue());
        if ($this->isOptionDisabled($filterItem)) {
            $customStyle = 'disabled';
            $linkToOption = 'javascript:void();';
        }

        return [
            'label' => $swatchOption->getLabel(),
            'link' => $linkToOption,
            'count' => $filterItem->getCount(),
            'custom_style' => $customStyle,
            'value' => $swatchOption->getValue()
        ];
    }

    public function getSwatchData()
    {
        if (false === $this->eavAttribute instanceof Attribute) {
            throw new \RuntimeException('Magento_Swatches: RenderLayered: Attribute has not been set.');
        }

        $attributeOptions = [];
        foreach ($this->eavAttribute->getOptions() as $option) {
            if ($currentOption = $this->getFilterOption($this->filter->getItems(), $option)) {
                $attributeOptions[$option->getValue()] = $currentOption;
            } elseif ($this->isShowEmptyResults()) {
                $attributeOptions[$option->getValue()] = $this->getUnusedOption($option);
            }
        }

        $attributeOptionIds = array_keys($attributeOptions);
        $swatches = $this->swatchHelper->getSwatchesByOptionsId($attributeOptionIds);

        $setting = $this->settingHelper->getSettingByLayerFilter($this->filter);
      //  $this->assign('filterSetting', $setting);
     //   $this->assign('tooltipUrl', $this->helper->getTooltipUrl());

        $data = [
            'attribute_id' => $this->eavAttribute->getId(),
            'attribute_code' => $this->eavAttribute->getAttributeCode(),
            'attribute_label' => $this->eavAttribute->getStoreLabel(),
            'options' => $attributeOptions,
            'swatches' => $swatches,
            'filterSetting' => $setting,
            'filterItem' => $this->filter->getItems(),
            'tooltipUrl' => $this->helper->getTooltipUrl(),
        ];

        return $data;
    }

    protected function getTemplateByFilterSetting(FilterSettingInterface $filterSetting)
    {
        switch($filterSetting->getDisplayMode()) {
            case DisplayMode::MODE_DROPDOWN:
                $this->setTemplate('layer/filter/swatchrender/dropdown.phtml');
                break;
            case DisplayMode::MODE_VISUAL_LABEL:
                $this->setTemplate('layer/filter/swatchrender/imgandlabel.phtml');
                break;
            case DisplayMode::MODE_VISUAL:
                $this->setTemplate('layer/filter/swatchrender/image.phtml');
                break;
            default:
                $this->setTemplate('layer/filter/swatchrender/label.phtml');
                break;
        }
    }

    public function escapeId($data)
    {
        return str_replace(",", "_", $data);
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
}
