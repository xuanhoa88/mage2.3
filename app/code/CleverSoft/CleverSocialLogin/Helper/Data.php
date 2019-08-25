<?php
/**
 * @category    CleverSoft
 * @package     CleverSocialLogin
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author      ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverSocialLogin\Helper;

class Data extends Main
{
    const REFERER_QUERY_PARAM_NAME = 'pslogin_referer';
    const REFERER_STORE_PARAM_NAME = 'pslogin_referer_store';
    const SHOW_POPUP_PARAM_NAME = 'pslogin_show_popup';
    const API_CALL_PARAM_NAME = 'pslogin_api_call';
    const FAKE_EMAIL_PREFIX = 'temp-email-ps';
    const TIME_TO_EDIT = 300;
    const DEBUG_MODE = false;

    protected $_configSectionId = 'sociallogin';
    protected $_buttons = null;
    protected $_buttonsPrepared = null;

    public function moduleEnabled()
    {
        return (bool)$this->getConfig($this->_configSectionId.'/general/enable');
    }

    public function validateIgnore()
    {
        return (bool)$this->getConfig($this->_configSectionId .'/general/validate_ignore');
    }

    public function forLoginEnabled()
    {
        return (bool)$this->getConfig($this->_configSectionId .'/general/enable_for_login');
    }

    public function forRegisterEnabled()
    {
        return (bool)$this->getConfig($this->_configSectionId .'/general/enable_for_register');
    }

    public function photoEnabled()
    {
        return $this->moduleEnabled() && $this->getConfig($this->_configSectionId .'/general/enable_photo');
    }

    public function popupEnabled()
    {
        return $this->moduleEnabled() && $this->getConfig($this->_configSectionId .'/general/show_when_click');
    }

    public function modulePositionEnabled($position)
    {
        $enabled = true;

        $this->moduleEnabled() or $enabled = false;

        switch($position) {
            case 'login':
                $this->forLoginEnabled() or $enabled = false;
                break;

            case 'register':
                $this->forRegisterEnabled() or $enabled = false;
                break;
        }

        return $enabled;
    }

    public function hasButtons()
    {
        if(!$this->moduleEnabled()) {
            return false;
        }

        if($this->_objectManager->get('Magento\Customer\Model\Session')->isLoggedIn()) {
            return false;
        }

        return (bool)$this->getButtons();
    }

    public function positionButtonsLogin()
    {
        return $this->getConfig($this->_configSectionId .'/general/position_login');
    }

    public function positionButtonsRegister()
    {
        return $this->getConfig($this->_configSectionId .'/general/position_register');
    }

    public function getPhotoPath($checkIsEnabled = true)
    {
        if($checkIsEnabled && !$this->photoEnabled()) {
            return false;
        }
        $session = $this->_objectManager->get('Magento\Customer\Model\Session');
        if(!$session->isLoggedIn()) {
            return false;
        }
        if(!$customerId = $session->getCustomerId()) {
            return false;
        }
        $directoryRead = $this->_objectManager->get('Magento\Framework\Filesystem')->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
        $path = 'pslogin'. DIRECTORY_SEPARATOR .'photo'. DIRECTORY_SEPARATOR . $customerId .'.'. \CleverSoft\CleverSocialLogin\Model\Account::PHOTO_FILE_EXT;
        $pathUrl = $this->_objectManager->get('Magento\Store\Model\Store')->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) .'pslogin/photo/' . $customerId .'.'. \CleverSoft\CleverSocialLogin\Model\Account::PHOTO_FILE_EXT;

        if(!$directoryRead->isExist($path)) {
            return false;
        }

        return $pathUrl;
    }

    public function isGlobalScope()
    {
        return $this->_objectManager->get('Magento\Customer\Model\Customer')->getSharingConfig()->isGlobalScope();
    }

    public function getRedirect()
    {
        return [
            'login' => $this->getConfig($this->_configSectionId .'/general/redirect_for_login'),
            'login_link' => $this->getConfig($this->_configSectionId .'/general/redirect_for_login_link'),
            'register' => $this->getConfig($this->_configSectionId .'/general/redirect_for_register'),
            'register_link' => $this->getConfig($this->_configSectionId .'/general/redirect_for_register_link'),
        ];
    }

    public function getCallbackURL($provider, $byRequest = false)
    {
        $request = $this->_getRequest();
        $websiteCode = $request->getParam('website');

        $storeManager = $this->_objectManager->get('Magento\Store\Model\StoreManager');

        $defaultStoreId = $storeManager
            ->getWebsite( $byRequest? $websiteCode : null )
            ->getDefaultGroup()
            ->getDefaultStoreId();

        if(!$defaultStoreId) {
            $websites = $storeManager->getWebsites(true);
            foreach($websites as $website) {
                $defaultStoreId = $website
                    ->getDefaultGroup()
                    ->getDefaultStoreId();

                if ($defaultStoreId) {
                    break;
                }
            }
        }

        if(!$defaultStoreId) {
            $defaultStoreId = 1;
        }

        $url = $storeManager->getStore($defaultStoreId)->getUrl('pslogin/account/login', ['type' => $provider, 'key' => null, '_nosid' => true]);

        $url = str_replace(
            $this->_objectManager->get('Magento\Backend\Helper\Data')->getAreaFrontName() . '/',
            '',
            $url
        );

        if(false !== ($length = stripos($url, '?'))) {
            $url = substr($url, 0, $length);
        }

        if($byRequest) {
            /*if($this->getConfig('web/url/use_store')) {
                // $url = str_replace('admin/', '', $url);
            }*/
            if($this->getConfig('web/seo/use_rewrites')) {
                $url = str_replace('index.php/', '', $url);
            }
        }

        return $url;
    }

    public function getTypes($onlyEnabled = true)
    {
        $groups = $this->getConfig($this->_configSectionId);
        unset(
            $groups['general'],
            $groups['share']
        );

        $types = [];
        foreach ($groups as $name => $fields) {
            if($onlyEnabled && empty($fields['enable'])) {
                continue;
            }
            $types[] = $name;
        }

        return $types;
    }

    public function getButtons()
    {
        if (null === $this->_buttons) {
            $types = $this->getTypes(false);

            $this->_buttons = [];
            foreach ($types as $type) {
                $type = $this->_objectManager->get('CleverSoft\CleverSocialLogin\Model\\'. ucfirst($type));

                if($type->enabled()) {
                    $button = $type->getButton();
                    $this->_buttons[ $button['type'] ] = $button;
                }
            }
        }
        return $this->_buttons;
    }

    public function getPreparedButtons($part = null)
    {
        if(null === $this->_buttonsPrepared) {
            $this->_buttonsPrepared = [
                'visible' => [],
                'hidden' => []
            ];
            $buttons = $this->getButtons();

            $storeName = $this->_getRequest()->getParam('store');
            $sortableString = $this->getConfig($this->_configSectionId .'/general/sortable', $storeName);
            $sortable = null;
            parse_str($sortableString, $sortable);

            if(is_array($sortable)) {
                foreach ($sortable as $partName => $partButtons) {
                    foreach ($partButtons as $button) {
                        if(isset($buttons[$button])) {
                            $this->_buttonsPrepared[$partName][] = $buttons[$button];
                            unset($buttons[$button]);
                        }
                    }
                }

                // If has not sortabled enabled buttons.
                if(!empty($buttons)) {
                    if(empty($this->_buttonsPrepared['visible'])) {
                        $this->_buttonsPrepared['visible'] = [];
                    }
                    $this->_buttonsPrepared['visible'] = array_merge($this->_buttonsPrepared['visible'], $buttons);
                }

                // If visible list is empty.
                if(empty($this->_buttonsPrepared['visible'])) {
                    $this->_buttonsPrepared['visible'] = $this->_buttonsPrepared['hidden'];
                    $this->_buttonsPrepared['hidden'] = [];
                }

                // Set visible.
                foreach($this->_buttonsPrepared['visible'] as &$btn) {
                    $btn['visible'] = true;
                }
            }
        }

        return isset($this->_buttonsPrepared[$part]) ?
                $this->_buttonsPrepared[$part] :
                array_merge($this->_buttonsPrepared['visible'], $this->_buttonsPrepared['hidden']);
    }

    public function refererLink($value = false)
    {
        // Customer session.
        $session = $this->_objectManager->get('Magento\Customer\Model\Session');
        $prevValueByCustomer = $session->getData(self::REFERER_QUERY_PARAM_NAME);

        if($value) {
            $session->setData(self::REFERER_QUERY_PARAM_NAME, $value);
        }elseif($value === null) {
            $session->unsetData(self::REFERER_QUERY_PARAM_NAME);
        }

        return $prevValueByCustomer;
    }

    public function getCookieRefererLink()
    {
        // $referer = $this->_objectManager->get('Magento\Framework\Stdlib\Cookie\PhpCookieManager')
        return $cookieReferer = $this->_objectManager->get('Magento\Framework\Stdlib\CookieManagerInterface')
            ->getCookie(self::REFERER_QUERY_PARAM_NAME);
    }

    public function refererStore($value = false)
    {
        // Customer session.
        $session = $this->_objectManager->get('Magento\Customer\Model\Session');
        $prevValueByCustomer = $session->getData(self::REFERER_STORE_PARAM_NAME);

        if($value) {
            $session->setData(self::REFERER_STORE_PARAM_NAME, $value);
        }elseif($value === null) {
            $session->unsetData(self::REFERER_STORE_PARAM_NAME);
        }

        return $prevValueByCustomer;
    }

    public function getRefererLinkSkipModules()
    {
        return ['customer/account', /*'checkout',*/ 'pslogin/account'];
    }

    public function showPopup()
    {
        $cookieManager = $this->_objectManager->get('Magento\Framework\Stdlib\CookieManagerInterface');
        $publicCookieMetadata = $this->_objectManager->create(
            'Magento\Framework\Stdlib\Cookie\PublicCookieMetadata',
            ['metadata' => []]
        );
        $publicCookieMetadata
            ->setDuration(600)
            ->setPath('/');

        $cookieManager->setPublicCookie(self::SHOW_POPUP_PARAM_NAME, 1, $publicCookieMetadata);
    }

    public function apiCall($params = null)
    {
        $session = $this->_objectManager->get('Magento\Customer\Model\Session');
        $show = $session->getData(self::API_CALL_PARAM_NAME);

        if($params) {
            $session->setData(self::API_CALL_PARAM_NAME, $params);
        }else{
            $session->unsetData(self::API_CALL_PARAM_NAME);
        }

        return $show;
    }

    public function getRedirectUrl($after = 'login')
    {
        $redirectUrl = null;
        $redirect = $this->getRedirect();
        switch($redirect[$after]) {

            case '__referer__':
                $redirectUrl = $this->_objectManager->get('\Magento\Framework\App\Response\RedirectInterface')->getRefererUrl();
                if($redirectUrl == $this->_objectManager->get('Magento\Store\Model\Store')->getBaseUrl()) $redirectUrl = $this->_objectManager->get('Magento\Store\Model\Store')->getBaseUrl().' ';
                break;

            case '__custom__':
                $redirectUrl = $redirect["{$after}_link"];
                if (!$this->isUrlInternal($redirectUrl)) {
                    $redirectUrl = $this->_objectManager->get('Magento\Store\Model\Store')->getBaseUrl() . $redirectUrl;
                }
                break;

            case '__dashboard__':
                $redirectUrl = $this->_objectManager->get('Magento\Customer\Model\Url')->getDashboardUrl();
                break;

            default:
                if(is_numeric($redirect[$after])) {
                    $redirectUrl = $this->_objectManager->get('Magento\Cms\Helper\Page')->getPageUrl($redirect[$after]);
                }
        }

        if (!$redirectUrl) {
            $redirectUrl = $this->_objectManager->get('Magento\Customer\Model\Url')->getDashboardUrl();
        }

        return $redirectUrl;
    }

    public function isUrlInternal($url)
    {
        return (stripos($url, 'http') === 0);
    }

    public function isFakeMail($email = null)
    {
        if(null === $email) {
            $session = $this->_objectManager->get('Magento\Customer\Model\Session');
            if($session->isLoggedIn()) {
                $email = $session->getCustomer()->getEmail();
            }
        }
        return (bool)(strpos($email, self::FAKE_EMAIL_PREFIX) === 0);
    }

    public function getDebugMode()
    {
        return self::DEBUG_MODE;
    }
}