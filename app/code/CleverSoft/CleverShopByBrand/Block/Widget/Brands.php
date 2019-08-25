<?php
/**
 * @category    CleverSoft
 * @package     CleverShopByBrand
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */
 
namespace CleverSoft\CleverShopByBrand\Block\Widget;

class Brands extends \CleverSoft\CleverShopByBrand\Block\Brands\FeaturedBrands implements \Magento\Widget\Block\BlockInterface
{
    protected $_template = "widget/brands.phtml";

    public function getOptionBrands() {
        $brands = $this->getFeaturedBrands();
        $options = array();
        if (count($brands)) {
            foreach ($brands as $brand) {
                $options[] = array(
                    'label' => $brand->getBrandLabel(),
                    'image' => $this->getLogo($brand,['height' => 85]),
                    'linkto' => $this->getBrandPageUrl($brand)
                );
            }
        }
        return $options;
    }
}
