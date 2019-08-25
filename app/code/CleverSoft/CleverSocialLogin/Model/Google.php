<?php
/**
 * @category    CleverSoft
 * @package     CleverSocialLogin
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverSocialLogin\Model;

class Google extends Account
{
    protected $_type = 'google';

    protected $_url = "https://accounts.google.com/o/oauth2/auth";
    // Google api end-points
    public $scope = "https://www.googleapis.com/auth/plus.login+https://www.googleapis.com/auth/plus.profile.emails.read+https://www.google.com/m8/feeds/";
    const URL_REQUEST_TOKEN = "https://accounts.google.com/o/oauth2/token";
    const URL_ACCOUNT_DATA = "https://www.googleapis.com/plus/v1/people/me";
    protected $sign_token_name = "access_token";
    protected $access_token;

    protected $_fields = [
        'user_id' => 'id',
        'firstname' => 'name',
        'lastname' => 'name2',
        'email' => 'emails',
        'dob' => null,
        'gender' => 'gender',
        'photo' => 'picture',
    ];

    protected $_popupSize = [650, 650];

    public function _construct()
    {
        parent::_construct();

        $this->_buttonLinkParams = [
            'client_id'     => $this->_applicationId,
            'redirect_uri'  => $this->_redirectUri,
            'response_type' => $this->_responseType,
            'scope'         => $this->scope,
            "access_type" => "offline"
        ];
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
            "grant_type"    => "authorization_code",
            'code' => $response,
            'redirect_uri' => $this->_redirectUri
        ];

        $result = null;
        if ($response = $this->request(self::URL_REQUEST_TOKEN, $params, 'POST')) {
            $response = json_decode($response, true);
            $this->access_token = $response['access_token'];
        }
        $this->_setLog($result);

        $data = [];
        $parameters = [];
        $parameters[$this->sign_token_name] = $this->access_token;
        $data = $this->request(self::URL_ACCOUNT_DATA, $parameters);
        $data = json_decode($data, true);
        $this->_setLog($data, true);

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

        if(!empty($data['displayName'])) {
            $nameParts = explode(' ', $data['displayName'], 2);
            $data['name'] = $nameParts[0];
            $data['name2'] = !empty($nameParts[1])? $nameParts[1] : '';
        }

        if(!empty($data['emails'])) {
            $data['emails'] = $data['emails'][0]['value'];
        }

        if(!empty($data['image'])) {
            $data['picture'] = $data['image']['url'];
        }

        return parent::_prepareData($data);
    }

}