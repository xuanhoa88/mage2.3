<?php
/**
 * @category    CleverSoft
 * @package     CleverLayeredNavigation
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverLayeredNavigation\Model\Layer\Filter;

use CleverSoft\CleverLayeredNavigation\Model\Source\DisplayMode;

class Decimal extends \Magento\CatalogSearch\Model\Layer\Filter\Decimal
{
    protected $_fromTo;

    protected $settingHelper;

    protected $currencySymbol;

    /**
     * @var \CleverSoft\CleverLayeredNavigation\Model\Search\Adapter\Mysql\AggregationAdapter
     */
    protected $aggregationAdapter;

    public function __construct(
        \Magento\Catalog\Model\Layer\Filter\ItemFactory $filterItemFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Layer $layer,
        \Magento\Catalog\Model\Layer\Filter\Item\DataBuilder $itemDataBuilder,
        \Magento\Catalog\Model\ResourceModel\Layer\Filter\DecimalFactory $filterDecimalFactory,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \CleverSoft\CleverLayeredNavigation\Helper\FilterSetting $settingHelper,
        \CleverSoft\CleverLayeredNavigation\Model\Search\Adapter\Mysql\AggregationAdapter $aggregationAdapter,
        array $data = []
    ) {
        $this->settingHelper = $settingHelper;
        $this->currencySymbol = $priceCurrency->getCurrencySymbol();
        $this->aggregationAdapter = $aggregationAdapter;
        parent::__construct($filterItemFactory, $storeManager, $layer, $itemDataBuilder, $filterDecimalFactory, $priceCurrency, $data);
    }


    public function apply(\Magento\Framework\App\RequestInterface $request)
    {
        $apply = parent::apply($request);
        $filter = $request->getParam($this->getRequestVar());
        if(!empty($filter) && !is_array($filter)) {
            list($from, $to) = explode('-', current(explode(',', $filter)));
            $this->_fromTo['from'] = $from;
            $this->_fromTo['to'] = $to;
        }

        return $apply;
    }


    public function getCurrentFrom()
    {
        return $this->_fromTo['from'];
    }

    public function getCurrentTo()
    {
        return $this->_fromTo['to'];
    }

    public function setFromTo($from = null, $to = null)
    {
        $this->_fromTo['from'] = $from;
        $this->_fromTo['to'] = $to;
    }


    protected function _initItems()
    {
        $filterSetting = $this->settingHelper->getSettingByLayerFilter($this);
        if($filterSetting->getDisplayMode() != DisplayMode::MODE_SLIDER) {
            return parent::_initItems();
        }

        $productCollectionOrigin = $this->getLayer()->getProductCollection();
        $attribute = $this->getAttributeModel();

        if(!is_null($this->_fromTo)){
            $requestBuilder = clone $productCollectionOrigin->_memRequestBuilder;
            $requestBuilder->removePlaceholder($attribute->getAttributeCode());
            $queryRequest = $requestBuilder->create();
            $facets = $this->aggregationAdapter->getBucketByRequest($queryRequest, $attribute->getAttributeCode());
        } else {
            $facets = $productCollectionOrigin->getFacetedData($attribute->getAttributeCode());
        }

        $min = $facets['data']['min'];
        $max = $facets['data']['max'];
        if(!$min) {
            return [];
        }

        $this->_items = [
            [
                'from'          => !empty($this->getCurrentFrom()) ? $this->getCurrentFrom() : $min,
                'to'          => !empty($this->getCurrentTo()) ? $this->getCurrentTo() : $max,
                'min'           => $min,
                'max'           => $max,
                'requestVar'    => $this->getRequestVar(),
                'step'          => round($filterSetting->getSliderStep(), 4),
                'template'      => !$filterSetting->getUnitsLabelUseCurrencySymbol() ? '{amount} '.$filterSetting->getUnitsLabel() : $this->currencySymbol . '{amount}'
            ]
        ];
        return $this;
    }

    protected function renderRangeLabel($fromPrice, $toPrice)
    {
        $filterSetting = $this->settingHelper->getSettingByLayerFilter($this);
        if($filterSetting->getUnitsLabelUseCurrencySymbol()) {
            return parent::renderRangeLabel($fromPrice, $toPrice);
        }
        $formattedFromPrice = round($fromPrice, 4).' '.$filterSetting->getUnitsLabel();
        if ($toPrice === '') {
            return __('%1 and above', $formattedFromPrice);
        } else {
            if ($fromPrice != $toPrice) {
                $toPrice -= .01;
            }
            return __('%1 - %2', $formattedFromPrice, round($toPrice, 4).' '.$filterSetting->getUnitsLabel());
        }
    }
}
