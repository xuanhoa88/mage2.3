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
use CleverSoft\CleverLayeredNavigation\Helper\FilterSetting;

/**
 * Layer category filter
 */
class Category extends AbstractFilter
{
    /**
     * @var \Magento\Framework\Escaper
     */
    private $escaper;

    /**
     * @var CategoryDataProvider
     */
    private $dataProvider;

    private $settingHelper;

    protected $filterSetting;

    protected $_categoryFactory;


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
        FilterSetting $settingHelper,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Catalog\Model\Layer\Filter\DataProvider\CategoryFactory $categoryDataProviderFactory,
        array $data = []
    ) {
        parent::__construct(
            $filterItemFactory,
            $storeManager,
            $layer,
            $itemDataBuilder,
            $data
        );
        $this->_categoryFactory = $categoryFactory;
        $this->escaper = $escaper;
        $this->_requestVar = 'cat';
        $this->settingHelper = $settingHelper;
        $this->dataProvider = $categoryDataProviderFactory->create(['layer' => $this->getLayer()]);
    }

    public function getCategory($categoryId)
    {
        $category = $this->_categoryFactory->create();
        $category->load($categoryId);
        return $category;
    }

    public function getCategoryProducts($categoryId,$ids)
    {
        $products = $this->getCategory($categoryId)->getProductCollection();
        $products->addFieldToFilter('entity_id', ['in' => $ids]);
        $products->addAttributeToSelect('*');
        return $products;
    }
    /**
     * Apply category filter to product collection
     *
     * @param   \Magento\Framework\App\RequestInterface $request
     * @return  $this
     */
    public function apply(\Magento\Framework\App\RequestInterface $request)
    {
        $categoryId = $request->getParam($this->_requestVar) ?: $request->getParam('id');
        if (empty($categoryId)) {
            return $this;
        }
        $categories = explode(',',$categoryId);
        $catsFilter['eq'] = array();
        foreach ($categories as $catId) {
            if(empty($catId)) continue;
            $catsFilter['eq'][] = $catId;
            $this->dataProvider->setCategoryId($catId);
            $category = $this->dataProvider->getCategory();
            if ($request->getParam('id') != $category->getId() && $this->dataProvider->isValid()) {
                $this->getLayer()->getState()->addFilter($this->_createItem($category->getName(), $catId));
            }
        }
        $this->getLayer()->getProductCollection()->setFlag('category_ids',$categories);
//        $this->getLayer()->getProductCollection()->addCategoriesFilter($catsFilter);

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
     * @return \CleverSoft\CleverLayeredNavigation\Api\Data\FilterSettingInterface
     */
    protected function getFilterSetting()
    {
        if(is_null($this->filterSetting)) {
            $this->filterSetting = $this->settingHelper->getSettingByLayerFilter($this);
        }
        return $this->filterSetting;
    }

    protected function isMultiselectAllowed()
    {
        return $this->getFilterSetting()->isMultiselect();
    }


    /**
     * Get filter name
     *
     * @return \Magento\Framework\Phrase
     */
    public function getName()
    {
        return __('Category');
    }

    /**
     * Get data array for building category filter items
     *
     * @return array
     */

    protected function _getItemsData()
    {
        /** @var \Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection $productCollection */
        $productCollection = $this->getLayer()->getProductCollection();
//        $sql = $productCollection->getSelectSql()->__toString();
        $current = $this->getLayer()->getCurrentCategory()->getId();
        if ($this->isMultiselectAllowed()){
            $this->dataProvider->setCategoryId($current);
        }
        $optionsFacetedData = $productCollection->getFacetedData('category');

        //begin customization
        $ids = array();
        /** @var \Magento\Framework\Search\Document $document */
        foreach ($productCollection->getQueryResponse() as $document) {
            $ids[] = $document->getId();
        }
        $category = $this->dataProvider->getCategory();
        $categories = $category->getChildrenCategories();
        $clone = clone $this->itemDataBuilder;
        if ($category->getIsActive()) {
            foreach ($categories as $category) {
                if ($category->getIsActive() ) {
                    if($this->getCategoryProducts($category->getId(),$ids)->count()==0) continue;
                    $child = $this->_getChildCategories($category->getId(),$ids);
                    if(!$child) {
                        $clone->addItemData(
                            $this->escaper->escapeHtml($category->getName()),
                            $category->getId(),
                            $this->getCategoryProducts($category->getId(),$ids)->count()
                        );
                    } else {
                        $clone->addItemData(
                            $this->escaper->escapeHtml($category->getName()),
                            $category->getId(),
                            $this->getCategoryProducts($category->getId(),$ids)->count(),
                            $child
                        );
                    }
                }
            }
        }
        return $clone->build();
    }

    protected function _initItems()
    {
        $data = $this->_getItemsData();
        $items = [];
        foreach ($data as $itemData) {
            if(isset($itemData['child'])) {
                $items[] = $this->_createItem($itemData['label'], $itemData['value'], $itemData['count'],$this->_initSingleItems($itemData['child']));
            } else {
                $items[] = $this->_createItem($itemData['label'], $itemData['value'], $itemData['count']);
            }
        }
        $this->_items = $items;
        return $this;
    }

    protected function _initSingleItems($child)
    {
        foreach ($child as $itemData) {
            if(isset($itemData['child'])) {
                $items[] = $this->_createItem($itemData['label'], $itemData['value'], $itemData['count'],$this->_initSingleItems($itemData['child']));
            } else {
                $items[] = $this->_createItem($itemData['label'], $itemData['value'], $itemData['count']);
            }
        }
        return $items;
    }

    protected function _createItem($label, $value, $count = 0,$child = null)
    {
        if(is_null($child)) {
            return $this->_filterItemFactory->create()
                ->setFilter($this)
                ->setLabel($label)
                ->setValue($value)
                ->setCount($count);
        }
        return $this->_filterItemFactory->create()
            ->setFilter($this)
            ->setLabel($label)
            ->setValue($value)
            ->setCount($count)
            ->setChild($child);
    }

    protected function _getChildCategories($id,$ids){
//        $productCollectionOrigin = $this->getLayer()->getProductCollection();
        $this->dataProvider->setCategoryId($id);
        $category = $this->dataProvider->getCategory();
//        $productCollection = clone $productCollectionOrigin;
//        $requestBuilder = clone $productCollection->_memRequestBuilder;
//        $requestBuilder->removePlaceholder('category_ids');
//        $productCollection->setRequestData($requestBuilder);
//        $productCollection->clear();
//        $productCollection->addCategoryFilter($category);

        $collectionSize = $this->getCategoryProducts($category->getId(),$ids)->count();
        $categories = $category->getChildrenCategories();
        $_temp = clone $this->itemDataBuilder;
        foreach ($categories as $category) {
            $count = $this->getCategoryProducts($category->getId(),$ids)->count();
            if ($count == 0) continue;
            if ($category->getIsActive()) {
                $child = $this->_getChildCategories($category->getId(),$ids );
                if(!$child) {
                    $_temp->addItemData(
                        $this->escaper->escapeHtml($category->getName()),
                        $category->getId(),
                        $count
                    );
                } else {
                    $_temp->addItemData(
                        $this->escaper->escapeHtml($category->getName()),
                        $category->getId(),
                        $count,
                        $this->_getChildCategories($category->getId(),$ids)
                    );
                }
            }
        }
        if ( count($categories)  > 0 ) return $_temp->build();
        else {return false;}

    }

}
