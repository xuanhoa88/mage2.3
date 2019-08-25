<?php
/**
 * Copyright © 2017 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace CleverSoft\CleverShopByBrand\Model\Config\Source;

class AttributeCode implements \Magento\Framework\Option\ArrayInterface
{
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resourceConnection
    )
    {
        $this->_resourceConnection = $resourceConnection;
    }

	public function toOptionArray()
    {
        $select = $this->_resourceConnection->getConnection()->select();
		$mainTable = $this->_resourceConnection->getTableName('catalog_eav_attribute');
		$eavAttributeTable = $this->_resourceConnection->getTableName('eav_attribute');
		$select->from(['e' => $mainTable])
			->joinLeft( [ 'eav' => $eavAttributeTable ], 'e.attribute_id = eav.attribute_id', ['attribute_code', 'frontend_label'])
			->where('eav.frontend_input = "select" 
                    AND ((eav.source_model is NULL) OR (eav.source_model = "Magento\\\\Eav\\\\Model\\\\Entity\\\\Attribute\\\\Source\\\\Table"))'
             );

		$attributes = $this->_resourceConnection->getConnection()->fetchAll($select);
        
		$list = array();
		foreach ($attributes as $attribute) {
			array_push($list,['value' => $attribute['attribute_code'], 'label' => $attribute['frontend_label']]);
		}
		return $list;
    }	
	
}
