<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * CatalogRule data helper
 */
namespace CleverSoft\CleverPinMarker\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $_objectManager;
    protected $_scopeConfig;
    protected $_urlBuilder;
    protected $_imageHelper;
    protected $_pinmarkerFactory;
    protected $_storeManager;
    protected $_attributeCode;
    
    protected $_pinmarkerProducts = [];
    protected $_pinmarkerProductCount = [];
    
	public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Backend\Model\UrlInterface $backendUrl,
        \CleverSoft\CleverPinMarker\Helper\Image $imageHelper,
        \CleverSoft\CleverPinMarker\Model\PinMarkerFactory $pinmarkerFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->_scopeConfig = $context->getScopeConfig();
        $this->_backendUrl = $backendUrl;
        $this->_urlBuilder = $context->getUrlBuilder();
        $this->_imageHelper = $imageHelper;
        $this->_pinmarkerFactory = $pinmarkerFactory;
        $this->_storeManager = $storeManager;
    }
    
    public function getUrl($path, $params = [])
    {
        return $this->_urlBuilder->getUrl($path, $params);
    }

    public function getMediaUrl()
    {
        return $this->_storeManager->getStore()->getBaseUrl(
            \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
        );
    }

    /**
     * get Slider Banner Url
     * @return string
     */
    public function getCollectionPinUrl()
    {
        return $this->_backendUrl->getUrl('*/*/pins', ['_current' => true]);
    }

    public function getConfig($optionString, $scopeCode = null)
    {
        return $this->scopeConfig->getValue($optionString, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $scopeCode);
    }
}
