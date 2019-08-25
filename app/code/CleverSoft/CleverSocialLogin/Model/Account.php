<?php
/**
 * @category    CleverSoft
 * @package     CleverSocialLogin
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverSocialLogin\Model;

class Account extends \Magento\Framework\Model\AbstractModel
{
    const PHOTO_FILE_EXT = 'png';

    protected $_type = null;
    protected $_protocol = 'OAuth';
    protected $_websiteId = null;
    protected $_redirectUri = null;
    protected $_userData = [];
    protected $_passwordLength = 6;

    protected $_photoDir = null;
    protected $_photoSize = 40;

    protected $_applicationId = null;
    protected $_secret = null;
    protected $_responseType = 'code';
    protected $_dob = [];
    protected $_gender = ['male', 'female'];
    public $curl_compressed = false;
    public $curl_proxy = null;

    public function _construct()
    {
        $this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->_helper = $this->_objectManager->get('CleverSoft\CleverSocialLogin\Helper\Data');

        $this->_init('CleverSoft\CleverSocialLogin\Model\ResourceModel\Account');
        $this->_websiteId = $this->_objectManager->get('Magento\Store\Model\StoreManager')->getWebsite()->getId();
        $this->_redirectUri = $this->_helper->getCallbackURL($this->_type);
        $this->_photoDir = $this->_objectManager->get('Magento\Framework\Filesystem')->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)->getAbsolutePath('pslogin'. DIRECTORY_SEPARATOR .'photo');
        $this->_applicationId = trim($this->_helper->getConfig($this->_helper->getConfigSectionId() .'/'. $this->_type .'/application_id'));

        $encryptor = $this->_objectManager->get('Magento\Framework\Encryption\Encryptor');
        $this->_secret = trim($encryptor->decrypt($this->_helper->getConfig($this->_helper->getConfigSectionId() .'/'. $this->_type .'/secret')));
    }

    public function enabled()
    {
        return (bool)$this->_helper->getConfig($this->_helper->getConfigSectionId() .'/'. $this->_type .'/enable');
    }

    public function setCustomerIdByUserId($customerId)
    {
        $data = [
            'type' => $this->_type,
            'user_id' => $this->getUserData('user_id'),
            'customer_id' => $customerId
        ];

        $this->addData($data)->save();
        return $this;
    }

    public function getCustomerIdByUserId()
    {
        $customerId = $this->_getCustomerIdByUserId();
        if(!$customerId && $this->_helper->isGlobalScope()) {
            $customerId = $this->_getCustomerIdByUserId(true);
        }

        return $customerId;
    }

    protected function _getCustomerIdByUserId($useGlobalScope = false)
    {
        $customerId = 0;

        if($this->getUserData('user_id')) {
            $collection = $this->getCollection()
                ->join(['ce' => 'customer_entity'], 'ce.entity_id = main_table.customer_id', null)
                ->addFieldToFilter('main_table.type', $this->_type)
                ->addFieldToFilter('main_table.user_id', $this->getUserData('user_id'))
                ->setPageSize(1);

            if($useGlobalScope == false) {
                $collection->addFieldToFilter('ce.website_id', $this->_websiteId);
            }

            $customerId = $collection->getFirstItem()->getData('customer_id');
        }

        return $customerId;
    }

    public function getCustomerIdByEmail()
    {
        $customerId = $this->_getCustomerIdByEmail();
        if(!$customerId && $this->_helper->isGlobalScope()) {
            $customerId = $this->_getCustomerIdByEmail(true);
        }
        return $customerId;
    }

    protected function _getCustomerIdByEmail($useGlobalScope = false)
    {
        $customerId = 0;

        if(is_string($this->getUserData('email'))) {
            $collection = $this->_objectManager->get('Magento\Customer\Model\Customer')->getCollection()
                ->addFieldToFilter('email', $this->getUserData('email'))
                ->setPageSize(1);

            if($useGlobalScope == false) {
                $collection->addFieldToFilter('website_id', $this->_websiteId);
            }

            $customerId = $collection->getFirstItem()->getId();
        }

        return $customerId;
    }

    public function registrationCustomer()
    {
        $customerId = 0;
        $errors = [];
        $customer = $this->_objectManager->get('Magento\Customer\Model\Customer')->setId(null);

        try{
            $customer->setData($this->getUserData())
                ->setConfirmation($this->getUserData('password'))
                ->setPasswordConfirmation($this->getUserData('password'))
                ->setData('is_active', 1)
                ->getGroupId();

            if(!$this->_helper->isFakeMail( $this->getUserData('email') )) {
                $customer->setIsSubscribed(1);
            }

            $errors = $this->_validateErrors($customer);

            // If email is not valid, always error.
            $correctEmail = \Zend_Validate::is($this->getUserData('email'), 'EmailAddress');

            if( (empty($errors) || $this->_helper->validateIgnore()) && $correctEmail) {
                $customerId = $customer->save()->getId();

                // Set email confirmation;
                $customer->setConfirmation(null)->save();
                /*$customer->setConfirmation(null)
                    ->getResource()->saveAttribute($customer, 'confirmation');*/

            }
        } catch (\Exception $e) {
            $errors[] = $e->getMessage();
        }

        $this->setCustomer($customer);
        $this->setErrors($errors);

        return $customerId;
    }

    protected function _validateErrors($customer)
    {
        $errors = [];

        // Date of birth.
        $entityType = $this->_objectManager->get('Magento\Eav\Model\Config')->getEntityType('customer');
        $attribute = $this->_objectManager->get('Magento\Customer\Model\Attribute')->loadByCode($entityType, 'dob');

        if($attribute->getIsRequired() && $this->getUserData('dob') && !\Zend_Validate::is($this->getUserData('dob'), 'Date')) {
            $errors[] = __('The Date of Birth is not correct.');
        }

        if(true !== ($customerErrors = $customer->validate())) {
            $errors = array_merge($customerErrors, $errors);
        }

        return $errors;
    }

    public function getResponseType()
    {
        return $this->_responseType;
    }

    public function setUserData($key, $value = null)
    {
        if(is_array($key)) {
            $this->_userData = array_merge($this->_userData, $key);
        }else{
            $this->_userData[$key] = $value;
        }
        return $this;
    }

    public function getUserData($key = null)
    {
        if($key !== null) {
            return isset($this->_userData[$key]) ? $this->_userData[$key] : null;
        }
        return $this->_userData;
    }

    protected function _prepareData($data)
    {
        $_data = [];
        foreach ($this->_fields as $customerField => $userField) {
            $_data[$customerField] = ($userField && isset($data[$userField])) ? $data[$userField] : null;
        }

        $_data['firstname'] = $_data['firstname']?: '-';
        $_data['lastname'] = $_data['lastname']?: '-';

        // Generate email.
        if(empty($_data['email']) && $this->_helper->validateIgnore()) {
            $_data['email'] = $this->_getRandomEmail();
        }

        // Prepare date of birth.
        if(! empty($_data['dob'])) {
            $_data['dob'] = call_user_func_array([$this, '_prepareDob'], array_merge([$_data['dob']], $this->_dob));
        }else{
            $_data['dob'] = '0000-00-00';
        }

        // Convert gender.
        if (! empty($_data['gender'])) {
            $genderAttribute = $this->_objectManager->get('Magento\Eav\Model\Config')->getAttribute('customer', 'gender');
            if ($genderAttribute && $options = $genderAttribute->getSource()->getAllOptions(false)) {
                switch($_data['gender']) {
                    case $this->_gender[0]: $_data['gender'] = $options[0]['value']; break;
                    case $this->_gender[1]: $_data['gender'] = $options[1]['value']; break;
                    default: $_data['gender'] = 0;
                }
            } else {
                $_data['gender'] = 0;
            }
        } else {
            $_data['gender'] = 0;
        }

        // Tax/Vat number.
        $_data['taxvat'] = '0';

        // Set password.
        $_data['password'] = $this->_getRandomPassword();

        return $_data;
    }

    protected function _prepareDob($date, $p1 = 'month', $p2 = 'day', $p3 = 'year', $separator = '/')
    {
        $date = explode($separator, $date);

        $result = [
            'year' => '0000',
            'month' => '00',
            'day' => '00'
        ];

        $result[$p1] = $date[0];
        if(isset($date[1])) $result[$p2] = $date[1];
        if(isset($date[2])) $result[$p3] = $date[2];

        return implode('-', array_values($result));
    }

    protected function _getRandomEmail()
    {
        $len = 10;
        $chars = 'abcdefghijklmnopqrstuvwxyz0123456789';
        $domain = parse_url($this->_objectManager->get('Magento\Store\Model\Store')->getBaseUrl(), PHP_URL_HOST);
        $address = \CleverSoft\CleverSocialLogin\Helper\Data::FAKE_EMAIL_PREFIX . $this->_objectManager->get('Magento\Framework\Math\Random')->getRandomString($len, $chars) .'@'. $domain;
        return $address.".com";
    }

    protected function _getRandomPassword()
    {
        $len = $this->_passwordLength;
        $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        return $this->_objectManager->get('Magento\Framework\Math\Random')->getRandomString($len, $chars);
    }

    public function setCustomerPhoto($customerId)
    {
        $upload = false;

        $fileUrl = $this->getUserData('photo');
        if(empty($fileUrl) || !is_numeric($customerId) || $customerId < 1) {
            return;
        }

        $tmpPath = $this->_photoDir . DIRECTORY_SEPARATOR . $customerId .'.tmp';
        $io = $this->_objectManager->get('Magento\Framework\Filesystem\Io\File');

        try{
            $io->mkdir($this->_photoDir);
            if($file = $this->_loadFile($fileUrl)) {
                if(file_put_contents($tmpPath, $file) > 0) {

                    $image = $this->_objectManager->create('Magento\Framework\Image', ['fileName' => $tmpPath]);
                    $image->resize($this->_photoSize);

                    $fileName = $customerId .'.'. self::PHOTO_FILE_EXT;
                    $image->save(null, $fileName);

                    $upload = true;
                }
            }
        }catch(\Exception $e) {}

        if($io->fileExists($tmpPath)) {
            $io->rm($tmpPath);
        }

        return $upload;
    }

    protected function _loadFile($url, $count = 1) {

        if ($count > 5) {
            return false;
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $data = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        if (!$data) {
            return false;
        }

        $dataArray = explode("\r\n\r\n", $data, 2);

        if (count($dataArray) != 2) {
            return false;
        }

        list($header, $body) = $dataArray;
        if ($httpCode == 301 || $httpCode == 302) {
            $matches = [];
            preg_match('/Location:(.*?)\n/', $header, $matches);

            if (isset($matches[1])) {
                return $this->_loadFile(trim($matches[1]), $count++);
            }
        } else {
            return $body;
        }
    }

    public function getButton()
    {
        // Href.
        $uri = null;
        $store = $this->_objectManager->get('Magento\Store\Model\Store');

        if($this->getProtocol() == 'OAuth' && (empty($this->_applicationId) || empty($this->_secret))) {
            $uri = null;
        }else{
            $uri = $store->getUrl('pslogin/account/douse', ['type' => $this->_type, 'refresh' => time()]);
        }

        return [
            'href' => $uri,
            'type' => $this->_type,
            'login_text' => $this->_helper->getConfig($this->_helper->getConfigSectionId() .'/'. $this->_type .'/login_btn_text'),
            'register_text' => $this->_helper->getConfig($this->_helper->getConfigSectionId() .'/'. $this->_type .'/register_btn_text'),
            'popup_width' => $this->_popupSize[0],
            'popup_height' => $this->_popupSize[1],
        ];
    }

    public function getProviderLink()
    {
        if(empty($this->_applicationId) || empty($this->_secret)) {
            $uri = null;
        }elseif(is_array($this->_buttonLinkParams)) {
            $uri = $this->_url .'?'. urldecode(http_build_query($this->_buttonLinkParams));
        }else{
            $uri = $this->_buttonLinkParams;
        }

        return $uri;
    }

    public function getProvider()
    {
        return $this->_type;
    }

    public function getProtocol()
    {
        return $this->_protocol;
    }

    public function _setLog($data, $append = false)
    {
        return;
    }

    public function request( $url, $params=false, $type="GET" )
    {
        if( $type == "GET" ){
            $url = $url . ( strpos( $url, '?' ) ? '&' : '?' ) . http_build_query($params, '', '&');
        }

        $this->http_info = array();
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL            , $url );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER , 1 );
        curl_setopt($ch, CURLOPT_TIMEOUT        , 30 );
        curl_setopt($ch, CURLOPT_USERAGENT      , "OAuth/2 Simple PHP Client v0.1.1; HybridAuth http://hybridauth.sourceforge.net/" );
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT , 30 );
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER , false );
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST , false );
        curl_setopt($ch, CURLOPT_HTTPHEADER     , array() );

        if ($this->curl_compressed){
            curl_setopt($ch, CURLOPT_ENCODING, "gzip,deflate");
        }

        if($this->curl_proxy){
            curl_setopt( $ch, CURLOPT_PROXY        , $this->curl_proxy);
        }

        if( $type == "POST" ){
            curl_setopt($ch, CURLOPT_POST, 1);
            if($params) curl_setopt( $ch, CURLOPT_POSTFIELDS, $params );
        }
        if( $type == "DELETE" ){
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
        }
        if( $type == "PATCH" ){
            curl_setopt($ch, CURLOPT_POST, 1);
            if($params) curl_setopt( $ch, CURLOPT_POSTFIELDS, $params );
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PATCH");
        }
        $response = curl_exec($ch);

        $this->http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $this->http_info = array_merge($this->http_info, curl_getinfo($ch));

        curl_close ($ch);

        return $response;
    }

    protected function _call($url, $params = [], $method = 'GET', $curlResource = null)
    {
        $result = null;
        $paramsStr = is_array($params)? urlencode(http_build_query($params)) : urlencode($params);
        if($paramsStr) {
            $url .= '?'. urldecode($paramsStr);
        }

        $curl = is_resource($curlResource)? $curlResource : curl_init();

        if($method == 'POST') {
            // POST.
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $paramsStr);
        }else{
            // GET.
            curl_setopt($curl, CURLOPT_URL, $url);
        }

        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $result = curl_exec($curl);
        curl_close($curl);

        return $result;
    }
}