<?php
/**
 * @category    CleverSoft
 * @package     CleverLayeredNavigation
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverLayeredNavigation\Helper;

use CleverSoft\CleverLayeredNavigation\Api\Data\OptionSettingInterface;
use CleverSoft\CleverLayeredNavigation\Helper\OptionSetting;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Layer;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\View\Page\Config;
use Magento\Store\Model\StoreManager;


class Content extends AbstractHelper
{
    /** @var  Layer */
    protected $layer;

    /** @var  OptionSetting */
    protected $optionHelper;

    /** @var  StoreManager */
    protected $storeManager;

    public function __construct(Context $context, Layer\Resolver $layerResolver, OptionSetting $optionHelper, StoreManager $storeManager)
    {
        parent::__construct($context);
        $this->layer = $layerResolver->get();
        $this->optionHelper = $optionHelper;
        $this->storeManager = $storeManager;
    }

    public function setCategoryData(Category $category)
    {
        $brand = $this->getCurrentBranding();
        if ($brand) {
            $this->populateCategoryWithBrand($category, $brand);
        }
    }

    /**
     * @return null|OptionSettingInterface
     */
    public function getCurrentBranding()
    {
        $attribute_code = $this->scopeConfig->getValue('clevershopby/brand_filter/attribute_code');
        if ($attribute_code == '') {
            return null;
        }

        $value = $this->_getRequest()->getParam($attribute_code);
        if (!isset($value)) {
            return null;
        }

        $isRootCategory = $this->layer->getCurrentCategory()->getId() == $this->layer->getCurrentStore()->getRootCategoryId();
        if (!$isRootCategory) {
            return null;
        }

        $setting = $this->optionHelper->getSettingByValue($value, 'attr_' . $attribute_code, $this->storeManager->getStore()->getId());

        return $setting;
    }

    protected function populateCategoryWithBrand(Category $category, OptionSettingInterface $brand)
    {
        $category->setName($brand->getTitle());
        $category->setData('description', $brand->getDescription());
        $category->setData('landing_page', $brand->getTopCmsBlockId());
        if ($brand->getTopCmsBlockId()) {
            $category->setData('clevershopby_force_mixed_mode', 1);
        }
        $category->setData('clevershopby_image_url', $brand->getImageUrl());

        $category->setData('meta_title', $brand->getMetaTitle());
        $category->setData('meta_description', $brand->getMetaDescription());
        $category->setData('meta_keywords', $brand->getMetaKeywords());

    }
}
