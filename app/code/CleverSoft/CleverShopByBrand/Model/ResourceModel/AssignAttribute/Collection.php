<?php
/**
 * @category    CleverSoft
 * @package     CleverShopByBrand
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */
namespace CleverSoft\CleverShopByBrand\Model\ResourceModel\AssignAttribute;

class Collection extends \Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult
{
	protected function _construct()
    {
		parent::_construct();
    }

    protected function _beforeLoad()
    {
		$assignAttributeCode = \Magento\Framework\App\ObjectManager::getInstance()
            ->get('Magento\Framework\App\Config\ScopeConfigInterface')
            ->getValue('cleversoft_shopbybrand/all_brand_page/attribute_code');

        $storeId = \Magento\Store\Model\Store::DEFAULT_STORE_ID;
        
		$this->getSelect()
			->joinLeft(array('cea' => $this->getTable('catalog_eav_attribute') ),'main_table.attribute_id = cea.attribute_id','is_visible')
			->joinLeft(array('ea' => $this->getTable('eav_attribute') ),'cea.attribute_id = ea.attribute_id','attribute_code')
			->where("ea.attribute_code = '{$assignAttributeCode}'")
			->group("main_table.option_id");
    }
}
