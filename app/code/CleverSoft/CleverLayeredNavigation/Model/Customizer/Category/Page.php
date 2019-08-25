<?php
/**
 * @category    CleverSoft
 * @package     CleverLayeredNavigation
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverLayeredNavigation\Model\Customizer\Category;

/**
 * Class Page
 *
 * @author Artem Brunevski
 */

use CleverSoft\CleverLayeredNavigation\Model\Customizer\Category\CustomizerInterface;
use Magento\Catalog\Model\Category;
use CleverSoft\CleverLayeredNavigation\Model\ResourceModel\Page\CollectionFactory as PageCollectionFactory;
use Magento\Framework\App\Helper\Context;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Config as CatalogConfig;
use CleverSoft\CleverLayeredNavigation\Model\Page as PageEntity;

class Page implements CustomizerInterface
{
    /** @var PageCollectionFactory  */
    protected $_pageCollectionFactory;

    /** @var \Magento\Framework\App\RequestInterface  */
    protected $_request;

    /** @var CatalogConfig  */
    protected $_catalogConfig;

    /**
     * @param Context $context
     * @param PageCollectionFactory $pageCollectionFactory
     * @param CatalogConfig $catalogConfig
     */
    public function __construct(
        Context $context,
        PageCollectionFactory $pageCollectionFactory,
        CatalogConfig $catalogConfig
    ){
        $this->_pageCollectionFactory = $pageCollectionFactory;
        $this->_request = $context->getRequest();
        $this->_catalogConfig = $catalogConfig;
    }

    public function prepareData(Category $category)
    {
        $collection = $this->_pageCollectionFactory->create()
            ->addFieldToFilter('categories', [
                ['finset' => $category->getId()],
                ['eq' => 0],
                ['null' => true]
            ])
            ->addStoreFilter($category->getStoreId());


        foreach ($collection as $page){
            /** @var PageEntity $page */
            if ($this->_matchCurrentFilters($page)){
                $this->_modifyCategory($page, $category);
                break;
            }
        }
    }

    /**
     * Compare page filters with selected filters
     * @param \CleverSoft\CleverLayeredNavigation\Model\Customizer\Category\Page $page
     * @return bool
     */
    protected function _matchCurrentFilters(PageEntity $page)
    {
        $match = true;

        $conditions = $page->getConditionsUnserialized();

        foreach($conditions as $condition){
            $attribute = $this->_catalogConfig->getAttribute(Product::ENTITY, $condition['filter']);
            if ($attribute->getId()){
                $paramValue = $this->_request->getParam($attribute->getAttributeCode());

                //compare with array for multiselect attributes
                if ($attribute->getFrontendInput() === 'multiselect') {
                    $paramValue = explode(',', $paramValue);

                    if (!is_array($condition['value'])){
                        $match = false;
                        break;
                    }

                    if (array_diff($condition['value'], $paramValue)){
                        $match = false;
                        break;
                    }

                } else {
                    if ($paramValue !== $condition['value']){
                        $match = false;
                        break;
                    }
                }
            }
        }
        return $match;
    }

    /**
     * @param PageEntity $page
     * @param $pageValue
     * @param $categoryValue
     * @param null $delimiter
     * @return string
     */
    protected function _getModifiedCategoryData(
        PageEntity $page,
        $pageValue,
        $categoryValue,
        $delimiter = null
    ){
        if ($delimiter !== null && $page->getPosition() !== PageEntity::POSITION_REPLACE){
            //if has a delimiter, place at the start or end
            $categoryValueArr =
                $categoryValue !== null  &&
                $categoryValue !== '' ?
                    explode($delimiter, $categoryValue) :
                    [];

            if ($page->getPosition() === PageEntity::POSITION_AFTER){
                $categoryValueArr[] = $pageValue;
            } else if ($page->getPosition() === PageEntity::POSITION_BEFORE){
                $categoryValueArr = array_merge([$pageValue], $categoryValueArr);
            }
            $categoryValue = implode($delimiter, $categoryValueArr);
        } else {
            $categoryValue = $pageValue;
        }
        return $categoryValue;
    }

    /**
     * @param PageEntity $page
     * @param Category $category
     * @param $pageKey
     * @param $categoryKey
     * @param null $delimiter
     */
    protected function _modifyCategoryData(
        PageEntity $page,
        Category $category,
        $pageKey,
        $categoryKey,
        $delimiter = null
    ){
        $categoryValue = $category->getData($categoryKey);
        $pageValue = $page->getData($pageKey);
        $category->setData($categoryKey, $this->_getModifiedCategoryData($page, $pageValue, $categoryValue, $delimiter));
    }

    /**
     * @param PageEntity $page
     * @param Category $category
     */
    protected function _modifyCategory(PageEntity $page, Category $category)
    {
        $categoryName = $this->_getModifiedCategoryData($page, $page->getTitle(), $category->getName());
        $category->setName($categoryName);

        $this->_modifyCategoryData($page, $category, 'description', 'description');
        $this->_modifyCategoryData($page, $category, 'meta_title', 'meta_title', ' ');
        $this->_modifyCategoryData($page, $category, 'meta_description', 'meta_description', ' ');
        $this->_modifyCategoryData($page, $category, 'meta_keywords', 'meta_keywords', ',');
        $this->_modifyCategoryData($page, $category, 'top_block_id', 'landing_page');
        $this->_modifyCategoryData($page, $category, 'url', 'url');

        if ($page->getData('top_block_id')) {
            $category->setData(PageEntity::CATEGORY_FORCE_MIXED_MODE, 1);
        }

        if ($page->getData('url')) {
            $category->setData(PageEntity::CATEGORY_FORCE_USE_CANONICAL, 1);
        }
    }
}