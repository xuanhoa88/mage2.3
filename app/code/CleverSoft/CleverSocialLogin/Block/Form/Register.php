<?php
/**
 * @category    CleverSoft
 * @package     CleverSocialLogin
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverSocialLogin\Block\Form;

use Magento\Customer\Model\AccountManagement;

class Register extends \Magento\Customer\Block\Form\Register
{
    protected function _prepareLayout()
    {
        $_objectManager = \Magento\Framework\App\ObjectManager::getInstance(); //instance of\Magento\Framework\App\ObjectManager
        $storeManager = $_objectManager->get('Magento\Store\Model\StoreManagerInterface');
        $currentStore = $storeManager->getStore();
        $curentUrl = $currentStore->getCurrentUrl(false);
        $isRegisterPage = strpos($curentUrl, 'customer/account/create');
        if ($isRegisterPage !== false) {
            $this->pageConfig->getTitle()->set(__('Create New Customer Account'));
        }

        return $this;
    }
}
