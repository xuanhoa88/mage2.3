<?php
/**
 * @category    CleverSoft
 * @package     CleverLayeredNavigation
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverLayeredNavigation\Model\Customizer\Category;

/**
 * Class Brand
 *
 * @author Artem Brunevski
 */


use CleverSoft\CleverLayeredNavigation\Model\Customizer\Category\CustomizerInterface;
use Magento\Catalog\Model\Category;
use CleverSoft\CleverLayeredNavigation\Helper\Content;

class Brand implements CustomizerInterface
{
    /** @var  Content */
    protected $_contentHelper;

    /**
     * @param Content $contentHelper
     */
    public function __construct(Content $contentHelper)
    {
        $this->_contentHelper = $contentHelper;
    }

    /**
     * @param Category $category
     */
    public function prepareData(Category $category)
    {
        $this->_contentHelper->setCategoryData($category);
    }
}