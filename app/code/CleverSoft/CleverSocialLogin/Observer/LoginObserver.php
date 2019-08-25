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
use Magento\Customer\Model\Session;

class LoginObserver implements ObserverInterface
{
    protected $_helper;
    protected $_session;

    public function __construct(
        HelperData $helper,
        Session $customerSession
    ) {
        $this->_helper = $helper;
        $this->_session = $customerSession;
    }

    public function execute(Observer $observer)
    {
        if(!$this->_helper->moduleEnabled()) {
            return;
        }

        // Set redirect url.
        $redirectUrl = $this->_helper->getRedirectUrl('login');
        $this->_session->setBeforeAuthUrl($redirectUrl);
    }
}
