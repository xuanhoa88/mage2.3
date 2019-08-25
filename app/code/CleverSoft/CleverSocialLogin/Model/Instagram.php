<?php
/**
 * @category    CleverSoft
 * @package     CleverSocialLogin
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverSocialLogin\Model;

class Instagram extends Account
{
    protected $_type = 'instagram';

    // Instagram Api end-points
    const URL_ACCOUNT_DATA = "https://api.instagram.com/v1/";
    protected $_url = "https://api.instagram.com/oauth/authorize/";
    const URL_REQUEST_TOKEN = "https://api.instagram.com/oauth/access_token";
    protected $sign_token_name = "access_token";
    protected $access_token;
    protected $_buttonLinkParams = [
        'response_type' => 'code',
        'scope' => 'basic',
    ];

    protected $_fields = [
        'user_id' => 'id',
        'firstname' => 'name',
        'lastname' => 'name2',
        'email' => null,
        'dob' => null,
        'gender' => null,
        'photo' => 'profile_picture',
    ];

    protected $_popupSize = [650, 650];

    public function _construct()
    {
        parent::_construct();

        $this->_buttonLinkParams = array_merge(
            [
                'client_id' => $this->_applicationId,
                'redirect_uri' => $this->_redirectUri,
                'response_type' => $this->_responseType
            ], $this->_buttonLinkParams
        );
    }

    public function loadUserData($response)
    {
        if (empty($response)) {
            return false;
        }

        $data = [];

        $params = [
            'client_id' => $this->_applicationId,
            'client_secret' => $this->_secret,
            'grant_type' => 'authorization_code',
            'redirect_uri' => $this->_redirectUri,
            'code' => $response
        ];

        $result = null;
        if ($response = $this->request(self::URL_REQUEST_TOKEN, $params, 'POST')) {
            $response = json_decode($response, true);
            $this->access_token = $response['access_token'];
        }
        $this->_setLog($result);

        $data = [];
        $data = $this->api("users/self/");
        $this->_setLog($data['data'], true);

        if(!$this->_userData = $this->_prepareData($data['data'])) {
            return false;
        }

        $this->_setLog($this->_userData, true);

        return true;
    }

    public function api($url, $method = "GET", $parameters = array(), $decode_json = true)
    {
        if (strrpos($url, 'http://') !== 0 && strrpos($url, 'https://') !== 0) {
            $url = self::URL_ACCOUNT_DATA . $url;
        }

        $parameters[$this->sign_token_name] = $this->access_token;
        $response = null;

        switch ($method) {
            case 'GET'  :
                $response = $this->request($url, $parameters, "GET");
                break;
            case 'POST' :
                $response = $this->request($url, $parameters, "POST");
                break;
            case 'DELETE' :
                $response = $this->request($url, $parameters, "DELETE");
                break;
            case 'PATCH'  :
                $response = $this->request($url, $parameters, "PATCH");
                break;
        }

        if ($response && $decode_json) {
            return $this->response = json_decode($response, true);
        }

        return $this->response = $response;
    }

    protected function _prepareData($data)
    {
        if(empty($data['id'])) {
            return false;
        }

        if(!empty($data['full_name'])) {
            $nameParts = explode(' ', $data['full_name'], 2);
            $data['name'] = $nameParts[0];
            $data['name2'] = !empty($nameParts[1])? $nameParts[1] : '';
        }

        return parent::_prepareData($data);
    }

}