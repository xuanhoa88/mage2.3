<?php
/**
 * @category    CleverSoft
 * @package     CleverLayeredNavigation
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverLayeredNavigation\Model\Search;

use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory;
use CleverSoft\CleverLayeredNavigation\Helper\FilterSetting;
use Magento\Framework\Search\Request\FilterInterface;
use Magento\Framework\Search\Request\QueryInterface;

class RequestGenerator extends \Magento\CatalogSearch\Model\Search\RequestGenerator
{
    const FAKE_SUFFIX = '_clevershopby_filter_';
    /**
     * @var FilterSetting
     */
    protected $settingHelper;
    /**
     * @param CollectionFactory $productAttributeCollectionFactory
     */
    public function __construct(
        CollectionFactory $productAttributeCollectionFactory,
        FilterSetting $settingHelper
    ) {
        $this->settingHelper = $settingHelper;
        parent::__construct($productAttributeCollectionFactory);
    }


    public function generate()
    {
        $requests = [];
        $requests['catalog_view_container'] =
            $this->generateFakeRequest(\Magento\Catalog\Api\Data\EavAttributeInterface::IS_FILTERABLE, 'catalog_view_container');
        $requests['quick_search_container'] =
            $this->generateFakeRequest(\Magento\Catalog\Api\Data\EavAttributeInterface::IS_FILTERABLE_IN_SEARCH, 'quick_search_container');
        return $requests;
    }

    protected function generateFakeRequest($attributeType, $container)
    {
        $request = [];
        foreach ($this->getSearchableAttributes() as $attribute) {
            $filterSetting = $this->settingHelper->getSettingByAttribute($attribute);
            if($attribute->getData($attributeType) && $filterSetting->isUseAndLogic()) {
                foreach ($attribute->getOptions() as $key => $option) {
                    if($key == 0) {
                        continue;
                    }
                    $attributeCode = $attribute->getAttributeCode() . self::FAKE_SUFFIX . $key;
                    $queryName = $attributeCode . '_query';

                    $request['queries'][$container]['queryReference'][] = [
                        'clause' => 'should',
                        'ref' => $queryName,
                    ];
                    $filterName = $attributeCode . self::FILTER_SUFFIX;
                    $request['queries'][$queryName] = [
                        'name' => $queryName,
                        'type' => QueryInterface::TYPE_FILTER,
                        'filterReference' => [['ref' => $filterName]],
                    ];

                    $request['filters'][$filterName] = [
                        'type' => FilterInterface::TYPE_TERM,
                        'name' => $filterName,
                        'field' => $attributeCode,
                        'value' => '$' . $attributeCode . '$',
                    ];
                }
            }
        }

        return $request;
    }




}
