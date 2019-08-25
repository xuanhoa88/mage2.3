<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * CatalogRule data helper
 */
namespace CleverSoft\CleverShopByBrand\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $_objectManager;
    protected $_scopeConfig;
    protected $_urlBuilder;
    protected $_imageHelper;
    protected $_brandFactory;
    protected $_storeManager;
    protected $_attributeCode;
    
    protected $_brandProducts = [];
    protected $_brandProductCount = [];
    
	public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \CleverSoft\CleverShopByBrand\Helper\Image $imageHelper,
        \CleverSoft\CleverShopByBrand\Model\BrandFactory $brandFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->_scopeConfig = $context->getScopeConfig();
        $this->_urlBuilder = $context->getUrlBuilder();
        $this->_imageHelper = $imageHelper;
        $this->_brandFactory = $brandFactory;
        $this->_storeManager = $storeManager;
    }
    
    public function getUrl($path, $params = [])
    {
        return $this->_urlBuilder->getUrl($path, $params);
    }
    
    public function getBrandImage($brand, $type = 'logo', $options)
    {
        $brandThumb = $brand->getData($type);
        if ($type == 'brand_thumbnail' || !$brandThumb) {
            $brandThumb = 'cleversoft/brand/placeholder_thumbnail.jpg';
        }
        if(!isset($options['width'])) {
            $options['width'] = null;
        }
        if(!isset($options['height'])) {
            $options['height'] = null;
        }
        return $this->_imageHelper->init($brandThumb)
            ->resize($options['width'], $options['height'])->__toString();
	}
    
    public function getBrandPageUrl($brandModel)
    {
		if ($brandModel->getData('url_key')) {
            return $this->getUrl('brand', ['_nosid' => true]) . $brandModel->getData('url_key');
        } else {
            return $this->getUrl('brand', ['_nosid' => true]) . urlencode(str_replace(' ','-',strtolower(trim($brandModel->getData('brand_label')))));
        }
	}

    public function getConfig($optionString, $scopeCode = null)
    {
        return $this->scopeConfig->getValue($optionString, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $scopeCode);
    }

    public function getAssignAttributeCode() {
        return $this->getConfig('cleversoft_shopbybrand/all_brand_page/attribute_code');
    }
    public function getBrand ($brandId) {
        return $this->_objectManager->create('CleverSoft\CleverShopByBrand\Model\Brand')->load($brandId);
    }
}
