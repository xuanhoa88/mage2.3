<?php
/**
 * @category    CleverSoft
 * @package     CleverSocialProof
 * @copyright   Copyright © 2018 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverSocialProof\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context
    ) {
        parent::__construct($context);
    }

    public function getConfig($optionString, $scopeCode = null)
    {
        return $this->scopeConfig->getValue($optionString, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $scopeCode);
    }
}
