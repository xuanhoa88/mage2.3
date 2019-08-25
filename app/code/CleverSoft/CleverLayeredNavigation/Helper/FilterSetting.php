<?php
/**
 * @category    CleverSoft
 * @package     CleverLayeredNavigation
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverLayeredNavigation\Helper;

use Magento\Catalog\Model\Layer\Filter\FilterInterface;
use Magento\Framework\App\Helper\Context;
use CleverSoft\CleverLayeredNavigation;
use CleverSoft\CleverLayeredNavigation\Model\ResourceModel\FilterSetting\Collection;
use CleverSoft\CleverLayeredNavigation\Model\ResourceModel\FilterSetting\CollectionFactory;
use CleverSoft\CleverLayeredNavigation\Api\Data\FilterSettingInterface;

class FilterSetting extends \Magento\Framework\App\Helper\AbstractHelper
{
    /** @var  Collection */
    protected $collection;

    /** @var  CleverLayeredNavigation\Model\FilterSettingFactory */
    protected $settingFactory;

    public function __construct(Context $context, CollectionFactory $settingCollectionFactory, CleverLayeredNavigation\Model\FilterSettingFactory $settingFactory)
    {
        parent::__construct($context);
        $this->collection = $settingCollectionFactory->create();
        $this->settingFactory = $settingFactory;
    }

    /**
     * @param FilterInterface $layerFilter
     * @return CleverLayeredNavigation\Api\Data\FilterSettingInterface
     */
    public function getSettingByLayerFilter(FilterInterface $layerFilter)
    {
        $filterCode = $this->getFilterCode($layerFilter);
        $setting = null;
        if($layerFilter->getRequestVar() == 'cat'){
            $filterCode = 'attr_category_ids';
        }
        if (isset($filterCode)) {
            $setting = $this->collection->getItemByColumnValue(CleverLayeredNavigation\Model\FilterSetting::FILTER_CODE, $filterCode);
        }
        if (is_null($setting)) {
            $data = [FilterSettingInterface::FILTER_CODE=>$filterCode];
            if($layerFilter instanceof \CleverSoft\CleverLayeredNavigation\Model\Layer\Filter\Stock) {
                $data = $this->getDataByCustomFilter('stock');
            } elseif($layerFilter instanceof \CleverSoft\CleverLayeredNavigation\Model\Layer\Filter\Rating) {
                $data = $this->getDataByCustomFilter('rating');
            } elseif($layerFilter instanceof \CleverSoft\CleverLayeredNavigation\Model\Layer\Filter\NewProduct) {
                $data = $this->getDataByCustomFilter('new');
            } elseif($layerFilter instanceof \CleverSoft\CleverLayeredNavigation\Model\Layer\Filter\OnSale) {
                $data = $this->getDataByCustomFilter('on_sale');
            }
            $setting = $this->settingFactory->create(['data'=>$data]);
        }
        return $setting;
    }

    /**
     * @param $attributeModel
     *
     * @return CleverLayeredNavigation\Model\FilterSetting|\Magento\Framework\DataObject
     */
    public function getSettingByAttribute($attributeModel)
    {
        $filterCode = 'attr_' . $attributeModel->getAttributeCode();
        $setting = $this->collection->getItemByColumnValue(CleverLayeredNavigation\Model\FilterSetting::FILTER_CODE, $filterCode);
        if (is_null($setting)) {
            $setting = $this->settingFactory->create();
        }

        return $setting;
    }

    protected function getFilterCode(FilterInterface $layerFilter)
    {
        try
        {
            // Produces exception when attribute model missing
            $attribute = $layerFilter->getAttributeModel();
            return 'attr_' . $attribute->getAttributeCode();
        } catch (\Exception $exception)
        {
            // Put here cases for special filters like Category, Stock etc.
            ;
        }

        return null;
    }

    protected function getDataByCustomFilter($filterName)
    {
        $data = [];
        $data[FilterSettingInterface::FILTER_SETTING_ID] = $filterName;
        $data[FilterSettingInterface::DISPLAY_MODE] = $this->scopeConfig->getValue('clevershopby/'.$filterName.'_filter/display_mode', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $data[FilterSettingInterface::FILTER_CODE] = $filterName;
        $data[FilterSettingInterface::IS_EXPANDED] = $this->scopeConfig->getValue('clevershopby/'.$filterName.'_filter/is_expanded', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $data[FilterSettingInterface::TOOLTIP] = $this->scopeConfig->getValue('clevershopby/'.$filterName.'_filter/tooltip', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        return $data;
    }
}
