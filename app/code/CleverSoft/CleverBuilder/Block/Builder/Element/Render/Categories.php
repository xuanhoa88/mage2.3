<?php
/**
 * @category    CleverSoft
 * @package     CleverBuilder
 * @copyright   Copyright © 2018 CleverSoft., JSC. All Rights Reserved.
 * @author      ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverBuilder\Block\Builder\Element\Render;

use Magento\Catalog\Model\Product\Attribute\Repository;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\Element\Template;

class Categories extends Template implements \Magento\Widget\Block\BlockInterface
{
    protected $_productAttributeRepository;
    protected $_objectManager;

    public function __construct(
        Template\Context $context,
        ObjectManagerInterface $objectManagerInterface,
        \Magento\Catalog\Model\Indexer\Category\Flat\State $categoryFlatState,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        array $data = []
    ) {
        $this->_objectManager = $objectManagerInterface;
        $this->storeManager = $storeManager;
        $this->categoryFlatConfig = $categoryFlatState;
        parent::__construct($context,$data);
    }

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('element/categories.phtml');
        $this->_productAttributeRepository =  $this->_objectManager->create('Magento\Catalog\Model\Product\Attribute\Repository');
    }

    public function getInforCategories($rootCategoryIds = array(), $categoryIds = array()){
        if (!count($categoryIds)) {
            $categoryIds = explode(',' , $this->getData('category_ids'));
            $rootCategoryIds = $categoryIds;
        }
        $options = array();
        foreach($categoryIds as $key=>$value){
            $categoryId = (int)$value;
            $catObj = $this->_objectManager->create('Magento\Catalog\Model\Category')->load($categoryId);
            if (in_array($catObj->getParentId(), $categoryIds)) {
                continue;
            }
            $options[$categoryId] = array(
                'numberofproduct' => $catObj->getProductCount() ?  $catObj->getProductCount() : 0,
                'image' => $catObj->getImageUrl() ?  $catObj->getImageUrl() : '',
                'namecategory' => $catObj->getName() ?  $catObj->getName() : '',
                'urlcategory' => $catObj->getUrl() ?  $catObj->getUrl() : ''
            );
            $subCatIds = array_intersect($this->getSubcategories($catObj),$rootCategoryIds);
            if (count($subCatIds)) {
                $options[$categoryId]['child'] = $this->getInforCategories($categoryIds,$subCatIds);
            }
        }

        return $options;
    }

    public function getSubcategories($category)
    {
        if ($this->categoryFlatConfig->isFlatEnabled() && $category->getUseFlatResource())
            return (array)$category->getChildrenNodes();

        return $category->getChildren();
    }

    public function renderCategory($categories = array()) {
        if (!count($categories)) {
            $categories = $this->getInforCategories();
        }
        $lazyload = $this->getData('lazyload');
        $showDescription = $this->getData('show_description');
        $showQty = $this->getData('show_qty');
        $min_height_img_lazyload = $this->getData('height_image') ?  $this->getData('height_image') : 200;
        $image_url = $this->getViewFileUrl('CleverSoft_CleverBuilder::images/transparent.gif');
        $html = '';
        foreach ($categories as $category) {   
            $html.= '<li class="clever-category-item">';
            $html.= '<a class="menu-link" href="'.$category['urlcategory'].'" title="'.$category['namecategory'].'">';

            $html.= $category['namecategory'];

            $html.= '</a>';
            
            if (isset($category['child'])) {
                $html.= '<ul class="clever-category-sub">';
                $html.= $this->renderCategory($category['child']);
                $html.= '</ul>'; 
            }
            $html.= '</li>'; 
        }

        return $html;
    }
}
