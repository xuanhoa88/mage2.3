<?php
/**
 * @category    CleverSoft
 * @package     CleverLayeredNavigation
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverLayeredNavigation\Helper;


use Magento\Catalog\Model\Layer;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Helper\AbstractHelper;
use CleverSoft\CleverLayeredNavigation;

class Data extends AbstractHelper
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    public function __construct(
        Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->storeManager = $storeManager;
    }

    public function isAjaxEnabled()
    {
        return $this->scopeConfig->isSetFlag('clevershopby/general/ajax_enabled', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function isApplyEnabled()
    {
        return $this->scopeConfig->isSetFlag('clevershopby/general/apply_enabled', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function isHorizontal()
    {
        return $this->scopeConfig->isSetFlag('clevershopby/general/horizontal_enabled', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getAjaxScrollType()
    {
        return $this->scopeConfig->getValue('cleversofttheme/infinite_scroll/ic_type', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getAjaxType() {
        return $this->scopeConfig->getValue('clevershopby/general/ajax_enabled', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getTooltipUrl()
    {
        $baseUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        $tooltipImage = $this->scopeConfig->getValue('clevershopby/tooltips/image', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if(empty($tooltipImage)) {
            return '';
        }
        return $baseUrl . $tooltipImage;
    }
}
