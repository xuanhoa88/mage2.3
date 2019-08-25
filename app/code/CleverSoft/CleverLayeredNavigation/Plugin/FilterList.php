<?php
/**
 * @category    CleverSoft
 * @package     CleverLayeredNavigation
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author      ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverLayeredNavigation\Plugin;


class FilterList extends \Magento\Catalog\Model\Layer\FilterList
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var array
     */
    protected $filters;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * FilterList constructor.
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ){
        $this->objectManager = $objectManager;
        $this->request = $request;
        $this->scopeConfig = $scopeConfig;

    }

    /**
     * @param \Magento\Catalog\Model\Layer\FilterList $subject
     * @param \Closure                                $closure
     * @param \Magento\Catalog\Model\Layer            $layer
     *
     * @return array
     */
    public function aroundGetFilters(\Magento\Catalog\Model\Layer\FilterList $subject, \Closure $closure, \Magento\Catalog\Model\Layer $layer)
    {
        $listFilters = $closure($layer);
        $listAdditionalFilters = $this->getAdditionalFilters($layer);
        return $this->insertAdditionalFilters($listFilters, $listAdditionalFilters);
    }

    /**
     * @param \Magento\Catalog\Model\Layer $layer
     *
     * @return array
     */
    protected function getAdditionalFilters(\Magento\Catalog\Model\Layer $layer)
    {
        if(is_null($this->filters)) {
            $this->filters = [];
            if ($this->request->getRouteName() != 'brand') {
                $isStockEnabled = $this->scopeConfig->isSetFlag('clevershopby/stock_filter/enabled', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                if($isStockEnabled && $this->isEnabledShowOutOfStock()) {
                    $this->filters[] = $this->objectManager->create('CleverSoft\CleverLayeredNavigation\Model\Layer\Filter\Stock', ['layer'=>$layer]);
                }
                $isRatingEnabled = $this->scopeConfig->isSetFlag('clevershopby/rating_filter/enabled', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                if($isRatingEnabled) {
                    $this->filters[] = $this->objectManager->create('CleverSoft\CleverLayeredNavigation\Model\Layer\Filter\Rating', ['layer'=>$layer]);
                }

                $isNewEnabled = $this->scopeConfig->isSetFlag('clevershopby/new_filter/enabled', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                if($isNewEnabled) {
                    $this->filters[] = $this->objectManager->create('CleverSoft\CleverLayeredNavigation\Model\Layer\Filter\NewProduct', ['layer' => $layer]);
                }

                $isOnsaleEnabled = $this->scopeConfig->isSetFlag('clevershopby/on_sale_filter/enabled', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                if($isOnsaleEnabled) {
                    $this->filters[] = $this->objectManager->create('CleverSoft\CleverLayeredNavigation\Model\Layer\Filter\OnSale', ['layer' => $layer]);
                }
            }
        }

        return $this->filters;
    }

    protected function insertAdditionalFilters($listStandartFilters, $listAdditionalFilters)
    {
        if(count($listAdditionalFilters) == 0) {
            return $listStandartFilters;
        }
        $listNewFilters = [];
        foreach($listStandartFilters as $filter) {
            if(!$filter->hasAttributeModel()) {
                $listNewFilters[] = $filter;
                continue;
            }
            $position = $filter->getAttributeModel()->getPosition();
            foreach($listAdditionalFilters as $key=>$additionalFilter) {
                $additionalFilterPosition = $additionalFilter->getPosition();
                if($additionalFilterPosition <= $position) {
                    $listNewFilters[] = $additionalFilter;
                    unset($listAdditionalFilters[$key]);
                }
            }
            $listNewFilters[] = $filter;
        }
        $listNewFilters = array_merge($listNewFilters, $listAdditionalFilters);
        return $listNewFilters;
    }

    protected function isEnabledShowOutOfStock()
    {
        return $this->scopeConfig->isSetFlag(
            'cataloginventory/options/show_out_of_stock',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
}
