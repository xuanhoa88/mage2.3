<?php
/**
 * @category    CleverSoft
 * @package     CleverSocialLogin
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverSocialLogin\Model;

class Linkedin extends Account
{
    protected $_type = 'linkedin';

    protected $_url = 'https://www.linkedin.com/oauth/v2/authorization';

    const URL_REQUEST_TOKEN = 'https://www.linkedin.com/oauth/v2/accessToken';
    const URL_ACCOUNT_DATA = 'https://api.linkedin.com/v1/people/~:';
    protected $sign_token_name = "oauth2_access_token";
    protected $access_token;

    protected $_fields = [
        'user_id' => 'id',
        'firstname' => 'firstName',
        'lastname' => 'lastName',
        'email' => 'emailAddress',
        'dob' => null,
        'gender' => null,
        'photo' => 'myimage',
    ];

    protected $_popupSize = [650, 650];

    public function _construct()
    {
        parent::_construct();

        $this->_buttonLinkParams = [
            'response_type' => $this->_responseType,
            'client_id' => $this->_applicationId,
            'redirect_uri' => $this->_redirectUri,
            'state' => uniqid("cleversoft_"),
            'scope' => 'r_basicprofile+r_emailaddress',
        ];
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
        if ($response = $this->_call(self::URL_REQUEST_TOKEN, $params, 'POST')) {
            $response = json_decode($response, true);
            $this->access_token = $response['access_token'];
        }
        $this->_setLog($result);

        $data = [];
        $data = $this->api();
        $this->_setLog($data, true);

        if (!$this->_userData = $this->_prepareData($data)) {
            return false;
        }

        $this->_setLog($this->_userData, true);

        return true;
    }

    public function api($method = "GET", $parameters = array(), $decode_json = true)
    {
        $url = self::URL_ACCOUNT_DATA;
        $url .= '(id,firstName,lastName,pictureUrls::(original),headline,publicProfileUrl,location,industry,positions,email-address)';

        $parameters[$this->sign_token_name] = $this->access_token;
        $parameters['format'] = 'json';
        $Userdata = $this->request($url, $parameters, "GET");

        if ($Userdata && $decode_json) {
            return $this->response = json_decode($Userdata, true);
        }

        return false;
    }

    protected function _prepareData($data)
    {
        if (empty($data['id'])) {
            return false;
        }

        if (!empty($data['pictureUrls'])) {
            if(!empty($data['pictureUrls']['values'])) $data['myimage'] = $data['pictureUrls']['values'][0];
        }

        return parent::_prepareData($data);
    }
}