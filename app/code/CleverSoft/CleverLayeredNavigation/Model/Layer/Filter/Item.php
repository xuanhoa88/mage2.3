<?php
/**
 * @category    CleverSoft
 * @package     CleverLayeredNavigation
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */
 
namespace CleverSoft\CleverLayeredNavigation\Model\Layer\Filter;

use CleverSoft\CleverLayeredNavigation;
use Magento\Catalog\Model\Layer\Filter\FilterInterface;

class Item extends \Magento\Catalog\Model\Layer\Filter\Item
{
    protected $_request;

    /** @var  CleverLayeredNavigation\Helper\FilterSetting */
    protected $filterSettingHelper;

    /** @var  CleverLayeredNavigation\Helper\UrlBuilder */
    protected $urlBuilderHelper;

    protected $objectmanager;

    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\UrlInterface $url,
        \Magento\Theme\Block\Html\Pager $htmlPagerBlock,
        \Magento\Framework\ObjectManagerInterface $objectmanager,
        CleverLayeredNavigation\Helper\FilterSetting $filterSettingHelper,
        CleverLayeredNavigation\Helper\UrlBuilder $urlBuilderHelper,
        array $data = []
    ) {
        $this->_request = $request;
        $this->filterSettingHelper = $filterSettingHelper;
        $this->_objectmanager = $objectmanager;
        $this->urlBuilderHelper = $urlBuilderHelper;
        parent::__construct($url,$htmlPagerBlock,$data);
    }
    /**
     * Get filter item url
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->urlBuilderHelper->buildUrl($this->getFilter(), $this->getValue());
    }

//    public function getChildCategories($id){
//        /** @var \Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection $productCollection */
//        $catmodel = $this->_objectmanager->create('CleverSoft\CleverLayeredNavigation\Model\Layer\Filter\Category');
//        $productCollection = $catmodel->getLayer()->getProductCollection();
//        $optionsFacetedData = $productCollection->getFacetedData('category');
//        $catmodel->dataProvider->setCategoryId($id);
//        $category = $catmodel->dataProvider->getCategory();
//        $categories = $category->getChildrenCategories();
//
//        $collectionSize = $productCollection->getSize();
//
//        if ($category->getIsActive()) {
//            foreach ($categories as $category) {
//                if ($category->getIsActive()
//                    && isset($optionsFacetedData[$category->getId()])
//                    && $catmodel->isOptionReducesResults($optionsFacetedData[$category->getId()]['count'], $collectionSize)
//                ) {
//                    $catmodel->itemDataBuilder->addItemData(
//                        $catmodel->escaper->escapeHtml($category->getName()),
//                        $category->getId(),
//                        $optionsFacetedData[$category->getId()]['count']
//                    );
//                }
//            }
//        }
//        $html = $this->getChildCategoriesHtml($catmodel->itemDataBuilder->build());
//        return 'test';
//    }
//
//    public function getChildCategoriesHtml($filterItem){
//
//    }

    /**
     * Get url for remove item from filter
     *
     * @return string
     */
    public function getRemoveUrl()
    {
        return $this->urlBuilderHelper->buildUrl($this->getFilter(), $this->getValue());
    }

}
