<?php
/**
 * @category    CleverSoft
 * @package     Base
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverTheme\Helper;

class HeaderData extends \Magento\Framework\App\Helper\AbstractHelper
{

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \CleverSoft\CleverTheme\Model\ResourceModel\Block\CollectionFactory $collectionFactory
    ) {
        $this->_storeManager = $storeManager;
        $this->_objectManager = $objectManager;
        $this->_collectionFactory = $collectionFactory;
        parent::__construct($context);
    }

    public function getSettings() {
        $storeId = $this->_storeManager->getStore()->getId();
        if ($this->_collectionFactory->create()->addFieldToFilter('store_id',$storeId)->getFirstItem()->getId()) {
            $settings = $this->_collectionFactory->create()->addFieldToFilter('store_id',$storeId)->getFirstItem()->getHeaderStyle();
            if(isset(json_decode($settings)->header)) {
                $settingData =  json_decode($settings)->header;
            }
            if (isset($settingData->{'onoffswitch-headerbuilder'}) && $settingData->{'onoffswitch-headerbuilder'}) {
                return $this->_collectionFactory->create()->addFieldToFilter('store_id',$storeId)->getFirstItem()->getHeaderStyle();
            } else {
                return $this->_collectionFactory->create()->addFieldToFilter('store_id',0)->getFirstItem()->getHeaderStyle();
            }
        }
        return $this->_collectionFactory->create()->addFieldToFilter('store_id',0)->getFirstItem()->getHeaderStyle();
    }

    public function getSettingData() {
        $settings = $this->getSettings();
        if(isset(json_decode($settings)->header)) {
            return json_decode($settings)->header;
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
    
    public function getHeaderMobile() {
        $storeId = $this->_storeManager->getStore()->getId();
        if ($this->_collectionFactory->create()->addFieldToFilter('store_id',$storeId)->getFirstItem()->getId()) {
            return $this->_collectionFactory->create()->addFieldToFilter('store_id',$storeId)->getFirstItem()->getHeaderMobileData();
        }
        return $this->_collectionFactory->create()->addFieldToFilter('store_id',0)->getFirstItem()->getHeaderMobileData();
    }
}
