<?php
/**
 * @category    CleverSoft
 * @package     CleverSocialLogin
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverSocialLogin\Controller\Account;

class DoUse extends \CleverSoft\CleverSocialLogin\Controller\AbstractAccount
{
    public function execute()
    {
        $session = $this->_getSession();
        if ($session->isLoggedIn() && !$this->getRequest()->getParam('call')) {
            return $this->_windowClose();
        }

        $type = $this->getRequest()->getParam('type');
        $className = 'CleverSoft\CleverSocialLogin\Model\\'. ucfirst($type);
        if(!$type || !class_exists($className)) {
            return $this->_windowClose();
        }

        $model = $this->_objectManager->get($className);
        if(!$this->_getHelper()->moduleEnabled() || !$model->enabled()) {
            return $this->_windowClose();
        }

        if($call = $this->getRequest()->getParam('call')) {
            $this->_getHelper()->apiCall([
                'type'      => $type,
                'action'    => $call,
            ]);
        }else{
            $this->_getHelper()->apiCall(null);
        }

        // Set current store.
        $currentStoreId = $this->_objectManager->get('Magento\Store\Model\StoreManager')->getStore()->getId();
        if ($currentStoreId) {
            $this->_getHelper()->refererStore($currentStoreId);
        }

        // Set redirect url.
        if ($referer = $this->_getHelper()->getCookieRefererLink()) {
            $this->_getHelper()->refererLink($referer);
        }

        switch($model->getProtocol()) {

            case 'OAuth':
                if($link = $model->getProviderLink()) {
                    return $this->_redirect($link);
                }else{
                    $this->getResponse()->setBody(__('This Login Application was not configured correctly. Please contact our customer support.'));
                }
                break;

            case 'OpenID':
            case 'BrowserID':
            default:
                return $this->_windowClose();
        }
    }
}