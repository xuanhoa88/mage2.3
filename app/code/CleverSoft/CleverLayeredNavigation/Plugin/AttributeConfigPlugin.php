<?php
/**
 * @category    CleverSoft
 * @package     CleverLayeredNavigation
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverLayeredNavigation\Plugin;


class AttributeConfigPlugin
{
    public function aroundGetAttribute($subject, \Closure $closure, $entityType, $code)
    {
        if(is_string($code) && ($pos = strpos($code, \CleverSoft\CleverLayeredNavigation\Model\Search\RequestGenerator::FAKE_SUFFIX)) !== false) {
            $code = substr($code, 0, $pos);
        }
        return $closure($entityType, $code);
    }

}
