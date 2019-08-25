<?php
/**
 * Copyright © 2017 CleverSoft, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace CleverSoft\CleverShopByBrand\Block\Brands;

class FeaturedBrands extends \CleverSoft\CleverShopByBrand\Block\Brands
{
    public function getFeaturedBrands() {
        $collection = $this->getBrands()->addFieldToFilter('is_featured',1);
        return $collection;
    }
}