<?php
/**
 * @category    CleverSoft
 * @package     CleverTheme
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverTheme\Block;



class AuthorizationLink extends \Magento\Customer\Block\Account\AuthorizationLink
{
	public function getLabel()
    {
        return $this->isLoggedIn() ? __('Log Out') : __('Login');
    }
}
