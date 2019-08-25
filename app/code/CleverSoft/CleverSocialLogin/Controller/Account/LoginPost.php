<?php
/**
 * @category    CleverSoft
 * @package     CleverSocialLogin
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverSocialLogin\Controller\Account;

class LoginPost extends \Magento\Framework\App\Action\Action
{

    public function execute()
    {
        if ($redirectUrl = $this->getRequest()->getParam(\Magento\Customer\Model\Url::REFERER_QUERY_PARAM_NAME)) {
            $redirectUrl = base64_decode($redirectUrl);
            $this->getResponse()->setRedirect($redirectUrl);
        } else {
            $this->_redirect('/');
        }
    }

}