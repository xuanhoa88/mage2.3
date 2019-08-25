<?php
/**
 * @category    CleverSoft
 * @package     CleverLayeredNavigation
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverLayeredNavigation\Model\Customizer;

use Magento\Framework\ObjectManagerInterface;
use Magento\Catalog\Model\Category as CatalogCategory;

class Category
{
    /** @var array  */
    protected $_customizers;

    /** @var ObjectManagerInterface  */
    protected $_objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param array $customizers
     */
    public function __construct(ObjectManagerInterface $objectManager, $customizers = [])
    {
        $this->_objectManager = $objectManager;
        $this->_customizers = $customizers;
    }

    /**
     * @param $customizer
     * @param CatalogCategory $category
     */
    protected function _modifyData($customizer, CatalogCategory $category)
    {
        if (array_key_exists($customizer, $this->_customizers)){
            $object = $this->_objectManager->get($this->_customizers[$customizer]);
            if ($object instanceof Category\CustomizerInterface){
                /** @var $object  Category\CustomizerInterface */
                $object->prepareData($category);
            }
        }
    }

    /**
     * @param CatalogCategory $category
     */
    public function prepareData(CatalogCategory $category)
    {
        $this->_modifyData('brand', $category);
        $this->_modifyData('page', $category);
    }
}