<?php
/**
 * @category    CleverSoft
 * @package     CleverLayeredNavigation
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */
 
namespace CleverSoft\CleverLayeredNavigation\Model\Layer\Filter;

use CleverSoft\CleverLayeredNavigation\Helper\FilterSetting;
use Magento\Catalog\Model\Layer\Filter\AbstractFilter;
use Magento\Framework\Exception\LocalizedException;

/**
 * Layer attribute filter
 */
class Attribute extends AbstractFilter
{
    /**
     * @var \Magento\Framework\Filter\StripTags
     */
    protected $tagFilter;

    /** @var  FilterSetting */
    protected $settingHelper;
    /**
     * @var \CleverSoft\CleverLayeredNavigation\Api\Data\FilterSettingInterface
     */
    protected $filterSetting;

    /**
     * @var \CleverSoft\CleverLayeredNavigation\Model\Search\Adapter\Mysql\AggregationAdapter
     */
    protected $aggregationAdapter;

    protected $attributeValue = [];

    public function __construct(
        \Magento\Catalog\Model\Layer\Filter\ItemFactory $filterItemFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Layer $layer,
        \Magento\Catalog\Model\Layer\Filter\Item\DataBuilder $itemDataBuilder,
        \Magento\Framework\Filter\StripTags $tagFilter,
        \CleverSoft\CleverLayeredNavigation\Model\Search\Adapter\Mysql\AggregationAdapter $aggregationAdapter,
        array $data = [],
        FilterSetting $settingHelper
    ) {
        parent::__construct(
            $filterItemFactory,
            $storeManager,
            $layer,
            $itemDataBuilder,
            $data
        );
        $this->tagFilter = $tagFilter;
        $this->settingHelper = $settingHelper;
        $this->aggregationAdapter = $aggregationAdapter;
    }

    /**
     * Apply attribute option filter to product collection
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */

    public function apply(\Magento\Framework\App\RequestInterface $request)
    {
        $attributeValue = $request->getParam($this->_requestVar);
        if (empty($attributeValue)) {
            return $this;
        }
        $values = explode(',',$attributeValue);
        $this->attributeValue = $values;
        if (!$this->isMultiselectAllowed() && count($values) > 1) {
            throw new LocalizedException(__('Layer Filter applied with multiple parameters, but multiselect restricted for the filter.'));
        }
        $attribute = $this->getAttributeModel();
        /** @var \CleverSoft\CleverLayeredNavigation\Model\ResourceModel\Fulltext\Collection $productCollection */
        $productCollection = $this->getLayer()
            ->getProductCollection();

        if($this->getFilterSetting()->isUseAndLogic()) {
            foreach($values as $key=>$value) {
                $productCollection->addFieldToFilter($this->getFakeAttributeCodeForApply($attribute->getAttributeCode(), $key), $value);
            }
        } else {
            $collectionValue = count($values) > 1 ? $values : $values[0];
            $productCollection->addFieldToFilter($attribute->getAttributeCode(), $collectionValue);
        }



        if ($this->shouldAddState()) {
            $this->addState($values);
        }

        if (!$this->isVisibleWhenSelected()) {
            $this->_items = [];
        }
        return $this;
    }

    protected function isMultiselectAllowed()
    {
        return $this->getFilterSetting()->isMultiselect();
    }

    public function shouldAddState()
    {
        // Could be overwritten in plugins
        return true;
    }

    protected function addState(array $values)
    {
        foreach($values as $value){
            $label = $this->getOptionText($value);
            $this->getLayer()
                ->getState()
                ->addFilter($this->_createItem($label, $value));
        }
    }

    public function isVisibleWhenSelected()
    {
        return $this->isMultiselectAllowed() || $this->isDropdown();
    }

    protected function isDropdown()
    {
        return $this->getFilterSetting()->getDisplayMode() == \CleverSoft\CleverLayeredNavigation\Model\Source\DisplayMode::MODE_DROPDOWN;
    }

    /**
     * Get data array for building attribute filter items
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _getItemsData()
    {
        $attribute = $this->getAttributeModel();
        /** @var \CleverSoft\CleverLayeredNavigation\Model\ResourceModel\Fulltext\Collection $productCollection */
        $productCollectionOrigin = $this->getLayer()
            ->getProductCollection();

        if($this->attributeValue && $this->isMultiselectAllowed() && !$this->getFilterSetting()->isUseAndLogic()){
            $requestBuilder = clone $productCollectionOrigin->_memRequestBuilder;
            $requestBuilder->removePlaceholder($attribute->getAttributeCode());
            $queryRequest = $requestBuilder->create();
            $optionsFacetedData = $this->aggregationAdapter->getBucketByRequest($queryRequest, $attribute->getAttributeCode());
        } else {
            $optionsFacetedData = $productCollectionOrigin->getFacetedData($attribute->getAttributeCode());
        }

        $options = $attribute->getFrontend()
            ->getSelectOptions();
        if($this->getFilterSetting()->getSortOptionsBy() == \CleverSoft\CleverLayeredNavigation\Model\Source\SortOptionsBy::NAME) {
            usort($options, [$this, 'sortOption']);
        }
        foreach ($options as $option) {
            if (empty($option['value'])) {
                continue;
            }
            if(isset($optionsFacetedData[$option['value']])){
                $this->itemDataBuilder->addItemData(
                    $this->tagFilter->filter($option['label']),
                    $option['value'],
                    $optionsFacetedData[$option['value']]['count']
                );
            }
        }

        $itemsData = $this->itemDataBuilder->build();

        $setting = $this->settingHelper->getSettingByLayerFilter($this);
        if ($setting->getHideOneOption()) {
            if (count($itemsData) == 1) {
                $itemsData = [];
            }
        }

        return $itemsData;
    }

    /**
     * @return \CleverSoft\CleverLayeredNavigation\Api\Data\FilterSettingInterface
     */
    protected function getFilterSetting()
    {
        if(is_null($this->filterSetting)) {
            $this->filterSetting = $this->settingHelper->getSettingByLayerFilter($this);
        }
        return $this->filterSetting;
    }

    public function sortOption($a, $b)
    {
        return strcmp($a['label'], $b['label']);
    }

    protected function getFakeAttributeCodeForApply($attributeCode, $key)
    {
        if($key > 0) {
            $attributeCode .= \CleverSoft\CleverLayeredNavigation\Model\Search\RequestGenerator::FAKE_SUFFIX . $key;
        }

        return $attributeCode;
    }

}
