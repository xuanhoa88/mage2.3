<?php
/**
 * @category    CleverSoft
 * @package     CleverLayeredNavigation
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverLayeredNavigation\Plugin;

use Magento\Catalog\Block\Category\View;
use Magento\Catalog\Model\Category;

class CategoryPlugin
{
    /**
     * @param Category $subject
     * @param string|null $result
     * @return string|null
     */
    public function afterGetImageUrl(Category $subject, $result)
    {
        if ($subject->hasData('clevershopby_image_url')) {
            return $subject->getData('clevershopby_image_url');
        } else {
            return $result;
        }
    }
}
