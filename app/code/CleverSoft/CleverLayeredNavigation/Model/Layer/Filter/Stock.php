<?php
/**
 * @category    CleverSoft
 * @package     CleverLayeredNavigation
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverLayeredNavigation\Model\Layer\Filter;

use Magento\Catalog\Model\Layer\Filter\AbstractFilter;
use Magento\Catalog\Model\Layer\Filter\DataProvider\Category as CategoryDataProvider;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Catalog\Model\ResourceModel\Collection\AbstractCollection;
/**
 * Layer category filter
 */
class Stock extends AbstractFilter
{
    /**
     * @var \Magento\Framework\Escaper
     */
    private $escaper;

    /**
     * @var CategoryDataProvider
     */
    private $dataProvider;
    protected $attributeValue;
    protected $_localeDate;

    /**
     * Core store config
     *
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;
    protected $flagJoin;
    protected $in_or_outstock;
    /**
     * @param \Magento\Catalog\Model\Layer\Filter\ItemFactory $filterItemFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\Layer $layer
     * @param \Magento\Catalog\Model\Layer\Filter\Item\DataBuilder $itemDataBuilder
     * @param \Magento\Catalog\Model\CategoryFactory $categoryFactory
     * @param \Magento\Framework\Escaper $escaper
     * @param CategoryManagerFactory $categoryManager
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Model\Layer\Filter\ItemFactory $filterItemFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Layer $layer,
        \Magento\Catalog\Model\Layer\Filter\Item\DataBuilder $itemDataBuilder,
        \Magento\Framework\Escaper $escaper,
        \Magento\Catalog\Model\Layer\Filter\DataProvider\CategoryFactory $categoryDataProviderFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        ScopeConfigInterface $scopeConfig,
        array $data = []
    ) {
        parent::__construct(
            $filterItemFactory,
            $storeManager,
            $layer,
            $itemDataBuilder,
            $data
        );
        $this->escaper = $escaper;
        $this->_requestVar = 'stock';
        $this->_localeDate = $localeDate;
        $this->scopeConfig = $scopeConfig;
        $this->dataProvider = $categoryDataProviderFactory->create(['layer' => $this->getLayer()]);
    }

    /**
     * Apply category filter to product collection
     *
     * @param   \Magento\Framework\App\RequestInterface $request
     * @return  $this
     */
    public function apply(\Magento\Framework\App\RequestInterface $request)
    {
        $stockParam = (int)$request->getParam($this->getRequestVar());
        if (empty($stockParam)) {
            return $this;
        }

        $collection = $this->getLayer()->getProductCollection();
        $manageStock = $this->scopeConfig->getValue(
            \Magento\CatalogInventory\Model\Configuration::XML_PATH_MANAGE_STOCK,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        $this->in_or_outstock = $stockParam;
        //In-stock Product
        if ($stockParam == 1) {
            $cond = [
                '{{table}}.use_config_manage_stock = 0 AND {{table}}.manage_stock=1 AND {{table}}.is_in_stock=1',
                '{{table}}.use_config_manage_stock = 0 AND {{table}}.manage_stock=0'
            ];

            if ($manageStock) {
                $cond[] = '{{table}}.use_config_manage_stock = 1 AND {{table}}.is_in_stock=1';
            } else {
                $cond[] = '{{table}}.use_config_manage_stock = 1';
            }
            $collection->joinField(
                'inventory_in_stock',
                'cataloginventory_stock_item',
                'is_in_stock',
                'product_id=entity_id',
                '(' . join(') OR (', $cond) . ')'
            );
            $this->getLayer()->getState()->addFilter($this->_createItem('In Stock', $stockParam));

        }elseif ($stockParam == 2) {
            //Out-stock Product
            $cond = [
                '{{table}}.use_config_manage_stock = 0 AND {{table}}.manage_stock=1 AND {{table}}.is_in_stock=0',
                '{{table}}.use_config_manage_stock = 0 AND {{table}}.manage_stock=0'
            ];

            if ($manageStock) {
                $cond[] = '{{table}}.use_config_manage_stock = 1 AND {{table}}.is_in_stock=0';
            } else {
                $cond[] = '{{table}}.use_config_manage_stock = 1';
            }
            $collection->joinField(
                'inventory_in_stock',
                'cataloginventory_stock_item',
                'is_in_stock',
                'product_id=entity_id',
                '(' . join(') OR (', $cond) . ')'
            );
            $this->getLayer()->getState()->addFilter($this->_createItem('Out Stock', $stockParam));
        }
        return $this;
    }

    /**
     * Get filter value for reset current filter state
     *
     * @return mixed|null
     */
    public function getResetValue()
    {
        return $this->dataProvider->getResetValue();
    }

    /**
     * Get filter name
     *
     * @return \Magento\Framework\Phrase
     */
    public function getName()
    {
        $label = $this->scopeConfig->getValue('clevershopby/stock_filter/label', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        return $label;
    }

    public function getPosition()
    {
        $position = (int) $this->scopeConfig->getValue('clevershopby/stock_filter/position', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        return $position;
    }

    /**
     * Get request variable name which is used for apply filter
     *
     * @return string
     */
    public function getRequestVar()
    {
        return $this->_requestVar;
    }

    /**
     * Get data array for building category filter items
     *
     * @return array
     */

    protected function _getItemsData()
    {
        $inStockProductCollection = clone $this->getLayer()->getProductCollection();
        $statement = $inStockProductCollection->getConnection()->query($inStockProductCollection->getSelect()->limit(10000))->fetchAll();
        $total = count($statement);

        //In-stock Product
        $manageStock = $this->scopeConfig->getValue(
            \Magento\CatalogInventory\Model\Configuration::XML_PATH_MANAGE_STOCK,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $cond = [
            '{{table}}.use_config_manage_stock = 0 AND {{table}}.manage_stock=1 AND {{table}}.is_in_stock=1',
            '{{table}}.use_config_manage_stock = 0 AND {{table}}.manage_stock=0'
        ];

        $isStockFilter = 0;
        if ($manageStock) {
            $cond[] = '{{table}}.use_config_manage_stock = 1 AND {{table}}.is_in_stock=1';
        } else {
            $cond[] = '{{table}}.use_config_manage_stock = 1';
        }
        if(!strpos($inStockProductCollection->getSelect()->__toString(), 'is_in_stock') > 0 ) {
            $inStockProductCollection->joinField(
                'inventory_in_stock',
                'cataloginventory_stock_item',
                'is_in_stock',
                'product_id=entity_id',
                '(' . join(') OR (', $cond) . ')'
            );
        }else{
            $isStockFilter = 1;
        }
        $inStockStatement = $inStockProductCollection->getConnection()->query($inStockProductCollection->getSelect()->limit(10000))->fetchAll();
        $in_stock = 0;
        $out_stock = 0;

        //User don't using stock filter
        if($isStockFilter == 0){
            $in_stock = count($inStockStatement);
            $out_stock = (int)$total - (int)$in_stock;
        }//User using stock filter is instock
        else if($this->in_or_outstock == 1){
            $in_stock = count($inStockStatement);
        }//User using stock filter is outstock
        else{
            $out_stock = count($inStockStatement);
        }


        //Build Item
        if(!empty($in_stock)){
            $this->itemDataBuilder->addItemData(
                $this->escaper->escapeHtml('In Stock'),
                '1',
                $in_stock
            );
        }

        if(!empty($out_stock)){
            $this->itemDataBuilder->addItemData(
                $this->escaper->escapeHtml('Out Stock'),
                '2',
                $out_stock
            );
        }

        return $this->itemDataBuilder->build();
    }
}
