<?php
/**
 * @category    CleverSoft
 * @package     CleverMegaMenus
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverMegaMenus\Block\Widget\Html;

use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\Data\Tree\Node;

/**
 * Html page top menu block Magento\Theme\Block\Html\Topmenu
 */
class SubCatsHtml extends Template implements IdentityInterface
{
    /**
     * Cache identities
     *
     * @var array
     */
    protected $identities = [];

    /**
     * Top menu data tree
     *
     * @var \Magento\Framework\Data\Tree\Node
     */
    protected $_menu;

    /**
     * Core registry
     *
     * @var Registry
     */
    protected $registry;
    /**
     * Catalog category
     *
     * @var \Magento\Catalog\Helper\Category
     */
    protected $catalogCategory;
    protected $_categoryFlat;
    protected $_nodeFactory;
    protected $_treeFactory;

    /**
     * @var \Magento\Catalog\Model\Indexer\Category\Flat\State
     */
    protected $categoryFlatState;

    /**
     * @var MenuCategoryData
     */
    protected $menuCategoryData;
    /**
     * @param Template\Context $context
     * @param NodeFactory $nodeFactory
     * @param TreeFactory $treeFactory
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        \Magento\Framework\Data\Tree\NodeFactory $nodeFactory,
        \Magento\Framework\Data\TreeFactory $treeFactory,
        \CleverSoft\CleverMegaMenus\Helper\Category $catalogCategory,
        \Magento\Catalog\Model\Indexer\Category\Flat\State $categoryFlatState,
        \CleverSoft\CleverMegaMenus\Model\ResourceModel\Category\Flat $categoryFlat,
        \Magento\Catalog\Observer\MenuCategoryData $menuCategoryData,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->catalogCategory = $catalogCategory;
        $this->categoryFlatState = $categoryFlatState;
        $this->_nodeFactory = $nodeFactory;
        $this->_treeFactory = $treeFactory;
        $this->menuCategoryData = $menuCategoryData;
        $this->_categoryFlat = $categoryFlat;
        $this->_menu = $this->_nodeFactory->create(
            [
                'data' => [],
                'idField' => 'root',
                'tree' => $this->_treeFactory->create()
            ]
        );
    }
    /**
     * Get top menu html
     *
     * @param string $outermostClass
     * @param string $childrenWrapClass
     * @param int $limit
     * @return string
     */
    public function getHtml($outermostClass = '', $childrenWrapClass = '', $limit = 0)
    {
        $parent_id = $this->getData('parent_id')?$this->getData('parent_id'):$this->_storeManager->getStore()->getRootCategoryId();
        $this->_categoryFlat->resetLoaded();
        if($this->_menu->getParentId()) {
            $this->_menu = $this->_nodeFactory->create(
                [
                    'data' => [],
                    'idField' => 'root',
                    'tree' => $this->_treeFactory->create()
                ]
            );
        }
        $this->_menu->setParentId($parent_id);
        $this->_menu->setOutermostClass($outermostClass);
        $this->_menu->setChildrenWrapClass($childrenWrapClass);
        if($this->getData('level')){
            $this->_menu->setLevel($this->getData('level'));
        }
        $this->categoriesToMenuTree($this->catalogCategory->getCatsByParentId($parent_id), $this->_menu);
        $html = $this->_getHtml($this->_menu, $childrenWrapClass, $limit);
        $transportObject = new \Magento\Framework\DataObject(['html' => $html]);
        $html = $transportObject->getHtml();
        return $html;
    }
    /**
     * Get all sub categories then add to menu tree
     * @param array $c
     * @param tree $pmenu
     */
    protected function categoriesToMenuTree($categores, $parent)
    {
        foreach ($categores as $cat) {
            if (!$cat->getIsActive()) {
                continue;
            }
            $this_node = $this->_nodeFactory->create([
                'data' => $this->menuCategoryData->getMenuCategoryData($cat),
                'idField' => 'id',
                'tree' => $parent->getTree(),
                'parent'=>$parent
            ]);
            $parent->addChild($this_node);

            // that returns all children
            if ($this->categoryFlatState->isFlatEnabled() && $cat->getUseFlatResource()) {
                $this_sub = (array)$cat->getChildrenNodes();
            } else {
                $this_sub = $cat->getChildren();
            }

            $this->categoriesToMenuTree($this_sub, $this_node);
        }
    }

    public function getChildrenNodes($category){
        $this->_categoryFlat->resetLoaded();
        $number = $this->catalogCategory->getCatsByParentId($category);
        return count($number);
    }

    /**
     * Count All Subnavigation Items
     *
     * @param \Magento\Backend\Model\Menu $items
     * @return int
     */
    protected function _countItems($items)
    {
        $total = $items->count();
        foreach ($items as $item) {
            /** @var $item \Magento\Backend\Model\Menu\Item */
            if ($item->hasChildren()) {
                $total += $this->_countItems($item->getChildren());
            }
        }
        return $total;
    }

    /**
     * Building Array with Column Brake Stops
     *
     * @param \Magento\Backend\Model\Menu $items
     * @param int $limit
     * @return array|void
     *
     * @ Add Depth Level limit, and better logic for columns
     */
    protected function _columnBrake($items, $limit)
    {
        $total = $this->_countItems($items);
        if ($total <= $limit) {
            return;
        }

        $result[] = ['total' => $total, 'max' => (int)ceil($total / ceil($total / $limit))];

        $count = 0;
        $firstCol = true;

        foreach ($items as $item) {
            $place = $this->_countItems($item->getChildren()) + 1;
            $count += $place;

            if ($place >= $limit) {
                $colbrake = !$firstCol;
                $count = 0;
            } elseif ($count >= $limit) {
                $colbrake = !$firstCol;
                $count = $place;
            } else {
                $colbrake = false;
            }

            $result[] = ['place' => $place, 'colbrake' => $colbrake];

            $firstCol = false;
        }

        return $result;
    }

    /**
     * Add sub menu HTML code for current menu item
     *
     * @param \Magento\Framework\Data\Tree\Node $child
     * @param string $childLevel
     * @param string $childrenWrapClass
     * @param int $limit
     * @return string HTML code
     */
    protected function _addSubMenu($child, $childLevel, $childrenWrapClass, $limit)
    {
        $html = '';
        if (!$child->hasChildren()) {
            return $html;
        }

        $colStops = null;
        if ($childLevel == 0 && $limit) {
            $colStops = $this->_columnBrake($child->getChildren(), $limit);
        }

        $html .= '<ul class="level' . $childLevel . ' clever-mega-menu-sub">';
        $html .= $this->_getHtml($child, $childrenWrapClass, $limit, $colStops);
        $html .= '</ul>';

        return $html;
    }

    /**
     * Recursively generates top menu html from data that is specified in $menuTree
     *
     * @param \Magento\Framework\Data\Tree\Node $menuTree
     * @param string $childrenWrapClass
     * @param int $limit
     * @param array $colBrakes
     * @return string
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _getHtml(
        \Magento\Framework\Data\Tree\Node $menuTree,
        $childrenWrapClass,
        $limit,
        $colBrakes = []
    ) {
        $html = '';

        $itemCount = $this->getData('item_count');

        $children = $menuTree->getChildren();
        $parentLevel = $menuTree->getLevel();
        $childLevel = ($parentLevel === null) ? 1 : $parentLevel + 1;

        $counter = 1;
        $itemPosition = 1;
        $childrenCount = $children->count();

        $parentPositionClass = $menuTree->getPositionClass();
        $itemPositionClassPrefix = $parentPositionClass ? $parentPositionClass . '-' : 'nav-';

        foreach ($children as $child) {
            $child->setLevel($childLevel);
            $child->setIsFirst($counter == 1);
            $child->setIsLast($counter == $childrenCount);
            $child->setPositionClass($itemPositionClassPrefix . $counter);

            $outermostClassCode = '';
            $outermostClass = $menuTree->getOutermostClass();

            if ($childLevel == 0 && $outermostClass) {
                $outermostClassCode = ' class="' . $outermostClass . '" ';
                $child->setClass($outermostClass);
            }

            if (count($colBrakes) && $colBrakes[$counter]['colbrake']) {
                $html .= '</ul></li><li class="column"><ul>';
            }

            $html .= '<li ' . $this->_getRenderedMenuItemAttributes($child) . '>';
            $html .= '<a class="menu-link" href="' . $child->getUrl() . '" ' . $outermostClassCode . '><span>' . $this->escapeHtml(
                    $child->getName()
                ) . '</span></a>' . $this->_addSubMenu(
                    $child,
                    $childLevel,
                    $childrenWrapClass,
                    $limit
                ) . '</li>';
            $itemPosition++;
            if($itemCount){
                if($itemCount == $counter){
                    break;
                }
            }
            $counter++;
        }

        if (count($colBrakes) && $limit) {
            $html = '<li class="column"><ul>' . $html . '</ul></li>';
        }

        return $html;
    }

    /**
     * Generates string with all attributes that should be present in menu item element
     *
     * @param \Magento\Framework\Data\Tree\Node $item
     * @return string
     */
    protected function _getRenderedMenuItemAttributes(\Magento\Framework\Data\Tree\Node $item)
    {
        $html = '';
        $attributes = $this->_getMenuItemAttributes($item);
        foreach ($attributes as $attributeName => $attributeValue) {
            $html .= ' ' . $attributeName . '="' . str_replace('"', '\"', $attributeValue) . '"';
        }
        return $html;
    }

    /**
     * Returns array of menu item's attributes
     *
     * @param \Magento\Framework\Data\Tree\Node $item
     * @return array
     */
    protected function _getMenuItemAttributes(\Magento\Framework\Data\Tree\Node $item)
    {
        $menuItemClasses = $this->_getMenuItemClasses($item);
        return ['class' => implode(' ', $menuItemClasses)];
    }

    /**
     * Returns array of menu item's classes
     *
     * @param \Magento\Framework\Data\Tree\Node $item
     * @return array
     */
    protected function _getMenuItemClasses(\Magento\Framework\Data\Tree\Node $item)
    {
        $classes = [];

        $classes[] = 'level' . $item->getLevel();
        $classes[] = $item->getPositionClass();

        $classes[] = 'item';
        if ($item->getIsFirst()) {
            $classes[] = 'first';
        }

        if ($item->getIsActive()) {
            $classes[] = 'active';
        } elseif ($item->getHasActive()) {
            $classes[] = 'has-active';
        }

        if ($item->getIsLast()) {
            $classes[] = 'last';
        }

        if ($item->getClass()) {
            $classes[] = $item->getClass();
        }

        if ($item->hasChildren()) {
            $classes[] = 'parent';
        }

        return $classes;
    }

    /**
     * Add identity
     *
     * @param array $identity
     * @return void
     */
    public function addIdentity($identity) {
        if (!in_array($identity, $this->identities)) {
            $this->identities[] = $identity;
        }
    }
    /**
     * Get identities
     *
     * @return array
     */
    public function getIdentities()
    {
        return $this->identities;
    }
    /**
     * Get cache key informative items
     *
     * @return array
     */
    public function getCacheKeyInfo()
    {
        $keyInfo = parent::getCacheKeyInfo();
        $keyInfo[] = $this->getUrl('*/*/*', ['_current' => true, '_query' => '']);
        return $keyInfo;
    }

    /**
     * Get tags array for saving cache
     *
     * @return array
     */
    protected function getCacheTags()
    {
        return array_merge(parent::getCacheTags(), $this->getIdentities());
    }

    /**
     * Get menu object.
     *
     * @return Node
     */
    public function getMenu()
    {
        return $this->_menu;
    }
}
