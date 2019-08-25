<?php
/**
 * @category    CleverSoft
 * @package     CleverSocialLogin
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverSocialLogin\Controller\Account;

class Login extends \CleverSoft\CleverSocialLogin\Controller\AbstractAccount
{

    public function execute()
    {
        $session = $this->_getSession();
        $type = $this->getRequest()->getParam('type');

        // API.
        $callTarget = false;
        if($call = $this->_getHelper()->apiCall()) {
            if(isset($call['type']) && $call['type'] == $type && !empty($call['action'])) {
                $_target = explode('.', $call['action'], 3);
                if(count($_target) === 3) {
                    $callTarget = $_target;
                }else{
                    $this->_windowClose();
                    return;
                }
            }
        }

        if ($session->isLoggedIn() && !$callTarget) {
            return $this->_windowClose();
            // $this->_redirect('.');
        }

        $className = 'CleverSoft\CleverSocialLogin\Model\\'. ucfirst($type);
        if(!$type || !class_exists($className)) {
            return $this->_windowClose();
            // $this->_redirect('customer/account/login');
        }

        $model = $this->_objectManager->get($className);
        /*if(!$this->_getHelper()->moduleEnabled() || !$model->enabled()) {
            return $this->_windowClose();
            // $this->_redirect('customer/account/login');
        }*/

        $responseTypes = $model->getResponseType();
        if(is_array($responseTypes)) {
            $response = [];
            foreach ($responseTypes as $responseType) {
                $response[$responseType] = $this->getRequest()->getParam($responseType);
            }
        }else{
            $response = $this->getRequest()->getParam($responseTypes);
        }
        $model->_setLog($this->getRequest()->getParams());

        if(!$model->loadUserData($response)) {
            return $this->_windowClose();
            // $this->_redirect('customer/account/login');
        }

        // Switch store.
        if($storeId = $this->_getHelper()->refererStore()) {
            $this->_objectManager->get('Magento\Store\Model\StoreManager')->setCurrentStore($storeId);
        }

        // API.
        if($callTarget) {
            list($module, $controller, $action) = $callTarget;
            $this->_forward($action, $controller, $module, ['pslogin' => $model->getUserData()]);
            return;
        }

        if($customerId = $model->getCustomerIdByUserId()) {
            # Do auth.
            $redirectUrl = $this->_getHelper()->getRedirectUrl();
        }elseif($customerId = $model->getCustomerIdByEmail()) {
            # Customer with received email was placed in db.
            // Remember customer.
            $model->setCustomerIdByUserId($customerId);
            // System message.
            $url = $this->_getUrl('customer/account/forgotpassword');
            // $url = $this->_objectManager->get('Magento\Customer\Model\Url')->getForgotPasswordUrl();
            $message = __('Customer with email (%1) already exists in the database. If you are sure that it is your email address, please <a href="%2">click here</a> to retrieve your password and access your account.', $model->getUserData('email'), $url);
            $this->messageManager->addNotice($message);

            $redirectUrl = $this->_getHelper()->getRedirectUrl();
        }else{
            # Registration customer.
            if($customerId = $model->registrationCustomer()) {
                # Success.
                // Display system messages (before setCustomerIdByUserId(), because reset messages).
                $this->messageManager->addSuccess(__('Customer registration successful.'));

                if($errors = $model->getErrors()) {
                    foreach ($errors as $error) {
                        $this->messageManager->addNotice($error);
                    }
                }

                // Send Welcome email to new resign user
                $this->sendNewResignWelcomEmail($model);

                // Dispatch event.
                $this->_dispatchRegisterSuccess($model->getCustomer());

                // Remember customer.
                $model->setCustomerIdByUserId($customerId);

                $redirectUrl = $this->_getHelper()->getRedirectUrl('register');
            }else{
                # Error.
                $session->setCustomerFormData($model->getUserData());
                $redirectUrl = $this->_getUrl('customer/account/create', ['_secure' => true]);
                // $url = $this->_objectManager->get('Magento\Customer\Model\Url')->getRegisterUrl();

                if($errors = $model->getErrors()) {
                    foreach ($errors as $error) {
                        $this->messageManager->addError($error);
                    }
                }

                // Remember current provider data.
                $session->setData('pslogin', [
                    'provider'  => $model->getProvider(),
                    'user_id'   => $model->getUserData('user_id'),
                    'photo'     => $model->getUserData('photo'),
                    'timeout'   => time() + \CleverSoft\CleverSocialLogin\Helper\Data::TIME_TO_EDIT,
                ]);
            }
        }

        if($customerId) {
            // Load photo.
            if($this->_getHelper()->photoEnabled()) {
                $model->setCustomerPhoto($customerId);
            }

            // Loged in.
            if ($session->loginById($customerId)) {
                $session->regenerateId();
            }

            // Unset referer link.
            $this->_getHelper()->refererLink(null);
        }

        if ($this->getRequest()->isXmlHttpRequest()) {
            $this->getResponse()->clearHeaders()->setHeader('Content-type', 'application/json', true);
            $this->getResponse()->setBody(json_encode([
                'redirectUrl' => $redirectUrl
            ]));
        } else {
            $jsAction = '
                var pslDocument = window.opener ? window.opener.document : document;
                pslDocument.getElementById("pslogin-login-referer").value = "'.htmlspecialchars(base64_encode($redirectUrl)).'";
                pslDocument.getElementById("pslogin-login-submit").click();
            ';

            $body = $this->_jsWrap('if(window.opener && window.opener.location &&  !window.opener.closed) { window.close(); }; '.$jsAction.';');
            $this->getResponse()->setBody($body);
        }
    }

    private function sendNewResignWelcomEmail($user){

        try {
            $user_email = $user->getUserData('email');
            $user_name = $user->getUserData('firstname') . " " . $user->getUserData('lastname');

            $store_config = $this->_getStoreConfig();
            $store_owner_email = $store_config->getValue( 'trans_email/ident_general/email', \Magento\Store\Model\ScopeInterface::SCOPE_STORE );
            $store_owner_name = $store_config->getValue( 'trans_email/ident_general/name', \Magento\Store\Model\ScopeInterface::SCOPE_STORE );

            $templateOptions = array('area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $this->_getStore()->getId());
            $templateVars = array(
                'store' => $this->_getStore(),
                'customer_name' => $user_name,
                'message'   => 'Welcome!'
            );
            $from = array('email' => $store_owner_email, 'name' => $store_owner_name);
            $to = array($user_email);
            $transportBuilder = $this->_getTransportBuilder();
            $transport = $transportBuilder->setTemplateIdentifier('customer_create_account_email_template')
                ->setTemplateOptions($templateOptions)
                ->setTemplateVars($templateVars)
                ->setFrom($from)
                ->addTo($to)
                ->getTransport();
            $transport->sendMessage();
        } catch (Exception $e) {
            echo 'Send email error: ',  $e->getMessage(), "\n";
        }

    }

}