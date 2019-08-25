<?php
/**
 * @category    CleverSoft
 * @package     CleverSocialLogin
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverSocialLogin\Block\Form;

class Login extends \Magento\Customer\Block\Form\Login
{
    protected function _prepareLayout()
    {
        $_objectManager = \Magento\Framework\App\ObjectManager::getInstance(); //instance of\Magento\Framework\App\ObjectManager
        $storeManager = $_objectManager->get('Magento\Store\Model\StoreManagerInterface');
        $currentStore = $storeManager->getStore();
        $curentUrl = $currentStore->getCurrentUrl(false);
        $isLoginPage = strpos($curentUrl, 'customer/account/login');
        if ($isLoginPage !== false) {
            $this->pageConfig->getTitle()->set(__('Customer Login'));
        }
    }
}
