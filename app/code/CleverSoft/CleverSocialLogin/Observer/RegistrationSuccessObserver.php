<?php
/**
 * @category    CleverSoft
 * @package     CleverSocialLogin
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverSocialLogin\Observer;

use CleverSoft\CleverSocialLogin\Helper\Data as HelperData;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\App\RequestInterface;

class RegistrationSuccessObserver implements ObserverInterface
{
    protected $_helper;
    protected $_objectManager;
    protected $_session;
    protected $_request;

    public function __construct(
        HelperData $helper,
        ObjectManagerInterface $objectManager,
        Session $customerSession,
        RequestInterface $httpRequest
    ) {
        $this->_helper = $helper;
        $this->_objectManager = $objectManager;
        $this->_session = $customerSession;
        $this->_request = $httpRequest;
    }

    public function execute(Observer $observer)
    {
        if(!$this->_helper->moduleEnabled()) {
            return;
        }

        $data = $this->_session->getData('pslogin');
        
        if(!empty($data['provider']) && !empty($data['timeout']) && $data['timeout'] > time()) {
            $model = $this->_objectManager->get('CleverSoft\CleverSocialLogin\Model\\'. ucfirst($data['provider']));
            
            $customerId = null;
            if($customer = $observer->getCustomer()) {
                $customerId = $customer->getId();
            }

            if($customerId) {
                $model->setUserData($data);

                // Remember customer.
                $model->setCustomerIdByUserId($customerId);

                // Load photo.
                if($this->_helper->photoEnabled()) {
                    $model->setCustomerPhoto($customerId);
                }
            }

        }

        // Set redirect url.
        $redirectUrl = $this->_helper->getRedirectUrl('register');
        $this->_request->setParam(\Magento\Framework\App\Response\RedirectInterface::PARAM_NAME_SUCCESS_URL, $redirectUrl);
    }
}
