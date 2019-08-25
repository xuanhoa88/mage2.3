<?php
/**
 * @category    CleverSoft
 * @package     Base
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverTheme\Helper;

class FooterData extends \Magento\Framework\App\Helper\AbstractHelper
{

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \CleverSoft\CleverTheme\Model\ResourceModel\FooterBlock\CollectionFactory $collectionFactory
    ) {
        $this->_storeManager = $storeManager;
        $this->_objectManager = $objectManager;
        $this->_collectionFactory = $collectionFactory;
        parent::__construct($context);
    }

    public function getSettings() {
        $storeId = $this->_storeManager->getStore()->getId();
        if ($this->_collectionFactory->create()->addFieldToFilter('store_id',$storeId)->getFirstItem()->getId()) {
            $settings = $this->_collectionFactory->create()->addFieldToFilter('store_id',$storeId)->getFirstItem()->getFooterStyle();
            if(isset(json_decode($settings)->footer)) {
                $settingData =  json_decode($settings)->footer;
            }
            if (isset($settingData->{'onoffswitch-footerbuilder'}) && $settingData->{'onoffswitch-footerbuilder'}) {
                return $this->_collectionFactory->create()->addFieldToFilter('store_id',$storeId)->getFirstItem()->getFooterStyle();
            } else {
                return $this->_collectionFactory->create()->addFieldToFilter('store_id',0)->getFirstItem()->getFooterStyle();
            }
        }
        return $this->_collectionFactory->create()->addFieldToFilter('store_id',0)->getFirstItem()->getFooterStyle();
    }

    public function getSettingData() {
        $settings = $this->getSettings();
        if(isset(json_decode($settings)->footer)) {
            return json_decode($settings)->footer;
        }

        return null;
    }
    
    public function getMediaUrl() {
        return $this->_storeManager->getStore()->getBaseUrl(
            \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
        );
    }

    public function isMobile() {
        return preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER["HTTP_USER_AGENT"]);
    }
    
    public function getFooterMobile() {
        $storeId = $this->_storeManager->getStore()->getId();
        if ($this->_collectionFactory->create()->addFieldToFilter('store_id',$storeId)->getFirstItem()->getId()) {
            return $this->_collectionFactory->create()->addFieldToFilter('store_id',$storeId)->getFirstItem()->getFooterMobileData();
        }
        return $this->_collectionFactory->create()->addFieldToFilter('store_id',0)->getFirstItem()->getFooterMobileData();
    }
}
