<?php
/**
 * @category    CleverSoft
 * @package     CleverLayeredNavigation
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */
/**
 * Item Data Builder
 */
namespace CleverSoft\CleverLayeredNavigation\Model\Layer\Filter\Item;

class DataBuilder extends \Magento\Catalog\Model\Layer\Filter\Item\DataBuilder {
    public function addItemData($label, $value, $count,$child = null){
        if(is_null($child)) {
            $this->_itemsData[] = [
                'label' => $label,
                'value' => $value,
                'count' => $count
            ];
        } else {
            $this->_itemsData[] = [
                'label' => $label,
                'value' => $value,
                'count' => $count,
                'child' => $child,
            ];
        }

    }
}
