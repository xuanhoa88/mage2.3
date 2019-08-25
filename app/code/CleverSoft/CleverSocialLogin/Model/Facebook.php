<?php
/**
 * @category    CleverSoft
 * @package     CleverSocialLogin
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverSocialLogin\Model;

class Facebook extends Account
{
	protected $_type = 'facebook';
	
    protected $_url = 'https://www.facebook.com/dialog/oauth';

	protected $_fields = [
					'user_id' => 'id',
		            'firstname' => 'first_name',
		            'lastname' => 'last_name',
		            'email' => 'email',
		            'dob' => 'birthday',
                    'gender' => 'gender',
                    'photo' => 'picture',
				];

	protected $_buttonLinkParams = [
                    'display' => 'popup',
				];

    protected $_popupSize = [650, 650];

	public function _construct()
    {      
        parent::_construct();
        
        $this->_buttonLinkParams = array_merge($this->_buttonLinkParams, [
            'client_id'     => $this->_applicationId,
            'redirect_uri'  => $this->_redirectUri,
            'response_type' => $this->_responseType
        ]);
    }

    public function loadUserData($response)
    {
    	if(empty($response)) {
    		return false;
    	}

        $data = [];

        $params = [
            'client_id' => $this->_applicationId,
            'client_secret' => $this->_secret,
            'code' => $response,
            'redirect_uri' => $this->_redirectUri
        ];
    
        $token = null;
        if($response = $this->_call('https://graph.facebook.com/oauth/access_token', $params)) {
            $token = @json_decode($response, true);
            if (!$token) {
                parse_str($response, $token);
            }
        }
        $this->_setLog($response, true);
        $this->_setLog($token, true);
    
        if (isset($token['access_token'])) {
            $params = [
                'access_token'  => $token['access_token'],
                'fields'        => implode(',', $this->_fields)
            ];
    
            if($response = $this->_call('https://graph.facebook.com/me', $params)) {
                $data = json_decode($response, true);
            }
            
            if(!empty($data['id'])) {
                $data['picture'] = 'https://graph.facebook.com/'. $data['id'] .'/picture?return_ssl_resources=true';
            }
            
            $this->_setLog($data, true);
        }
 
        if(!$this->_userData = $this->_prepareData($data)) {
        	return false;
        }

        $this->_setLog($this->_userData, true);

        return true;
    }

    protected function _prepareData($data)
    {
    	if(empty($data['id'])) {
    		return false;
    	}

        return parent::_prepareData($data);
    }

}