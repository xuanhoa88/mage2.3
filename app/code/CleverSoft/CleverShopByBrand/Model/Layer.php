<?php
/**
 * @category    CleverSoft
 * @package     CleverShopByBrand
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverShopByBrand\Model;

class Layer extends \Magento\Catalog\Model\Layer
{
	public function getProductCollection()
	{
		$assignAttributeCode = \Magento\Framework\App\ObjectManager::getInstance()
            ->get('Magento\Framework\App\Config\ScopeConfigInterface')
            ->getValue('cleversoft_shopbybrand/all_brand_page/attribute_code');
            
	    $collection = parent::getProductCollection();
	    $collection->addAttributeToFilter($assignAttributeCode, ['eq' => $this->registry->registry('current_brand')->getId()]);
		return $collection;
	}
}
