<?php
/**
 * @category    CleverSoft
 * @package     CleverLayeredNavigation
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverLayeredNavigation\Plugin;


class AttributeCollectionPlugin
{
    public function aroundGetItemByColumnValue($subject, \Closure $closure, $column, $value)
    {
        if($column == 'attribute_code' && ($pos = strpos($value, \CleverSoft\CleverLayeredNavigation\Model\Search\RequestGenerator::FAKE_SUFFIX)) !== false) {
            $value = substr($value, 0, $pos);
        }
        return $closure($column, $value);
    }

}
