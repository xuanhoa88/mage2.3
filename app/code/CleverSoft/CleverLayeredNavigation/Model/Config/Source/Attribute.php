<?php
/**
 * @category    CleverSoft
 * @package     CleverLayeredNavigation
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverLayeredNavigation\Model\Config\Source;

/**
 * Class Filter
 *
 * @author Artem Brunevski
 */
use Magento\Framework\Option\ArrayInterface;
use Magento\Eav\Model\Config as EavConfig;

class Attribute implements ArrayInterface
{
    /**
     * @var EavConfig
     */
    protected $_eavConfig;

    /**
     * @var array
     */
    protected $_attributes;

    /**
     * @param EavConfig $eavConfig
     */
    public function __construct(
        EavConfig $eavConfig
    ){
        $this->_eavConfig = $eavConfig;
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $optionArray = [];
        $arr = $this->toArray();
        foreach($arr as $value => $label){
            $optionArray[] = [
                'value' => $value,
                'label' => $label
            ];
        }
        return $optionArray;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        if ($this->_attributes === null){
            $this->_attributes = [];
            /** @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection $collection */
            $collection = $this->_eavConfig->getEntityType(
                \Magento\Catalog\Model\Product::ENTITY
            )->getAttributeCollection();

            $collection->join(
                ['catalog_eav' => $collection->getTable('catalog_eav_attribute')],
                'catalog_eav.attribute_id=main_table.attribute_id',
                []
            )->addFieldToFilter('catalog_eav.is_filterable' , 1);

            /** @var \Magento\Eav\Model\Attribute $item */
            foreach($collection as $item){
                $this->_attributes[$item->getId()] = $item->getFrontendLabel();
            }
        }

        return $this->_attributes;
    }
}