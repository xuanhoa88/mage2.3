<?php
/**
 * @category    CleverSoft
 * @package     CleverSocialLogin
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverSocialLogin\Controller;

use Magento\Framework\Controller\ResultFactory;

abstract class AbstractAccount extends \Magento\Framework\App\Action\Action
{

    protected function _windowClose()
    {
        if($this->getRequest()->isXmlHttpRequest()) {
            $this->getResponse()->clearHeaders()->setHeader('Content-type', 'application/json', true);
            $this->getResponse()->setBody(json_encode([
                'windowClose' => true
            ]));
        }else{
            $this->getResponse()->setBody($this->_jsWrap('window.close();'));
        }
    }

    protected function _dispatchRegisterSuccess($customer)
    {
        $this->_eventManager->dispatch(
            'customer_register_success',
            ['account_controller' => $this, 'customer' => $customer]
        );
    }

    protected function _getSession()
    {
        return $this->_objectManager->get('Magento\Customer\Model\Session');
    }

    protected function _getUrl($url, $params = [])
    {
        return $this->_url->getUrl($url, $params);
    }

    protected function _getHelper()
    {
        return $this->_objectManager->get('CleverSoft\CleverSocialLogin\Helper\Data');
    }

    protected function _getStore()
    {
        return $this->_objectManager->get('\Magento\Store\Model\StoreManagerInterface')->getStore();
    }

    protected function _getStoreConfig()
    {
        return $this->_objectManager->get('\Magento\Framework\App\Config\ScopeConfigInterface');
    }

    protected function _getTransportBuilder()
    {
        return $this->_objectManager->get('\Magento\Framework\Mail\Template\TransportBuilder');
    }

    protected function _jsWrap($js)
    {
        return '<html><head></head><body><script type="text/javascript">'.$js.'</script></body></html>';
    }

}