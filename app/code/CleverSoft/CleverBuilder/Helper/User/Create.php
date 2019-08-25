<?php
/**
 * @category    CleverSoft
 * @package     CleverLayeredNavigation
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */
namespace CleverSoft\CleverBuilder\Helper\User;

use Magento\Framework\Filesystem;

class Create extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $_storeManager;
    protected $_userFactory;
    protected $_customerFactory;
    protected $_customerSession;
    protected $_objectManager;
    protected $_userRolesFactory;
    protected $scopeConfig;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Customer\Model\CustomerFactory $customerFactory ,
        \Magento\User\Model\UserFactory $userFactory ,
        \Magento\Customer\Model\Session $customerSession
    ) {
        $this->scopeConfig = $context->getScopeConfig();
        $this->_storeManager = $storeManager;
        $this->_objectManager = $objectManager;
        $this->_customerFactory = $customerFactory;
        $this->_customerSession = $customerSession;
        $this->_userFactory = $userFactory;
        parent::__construct($context);
    }

    /*
     * Create Frontend buider account.
     */

    public function builderAccountCreater($user_id) {
        $websiteId  = $this->_storeManager->getWebsite()->getWebsiteId();
        //load user data by id
        $user = $this->_userFactory->create()->load($user_id);
        $userPassword = $user->getPassword();
        $firstname = $user->getFirstname();
        $lastname = $user->getLastname();
        $userEmail = $user->getEmail();

        // Instantiate object (this is the most important part) to save as a customer
        $customer = $this->_customerFactory->create();
        $customer->setWebsiteId($websiteId);
        // Preparing data for new customer
        $customer->setEmail($userEmail);
        $customer->setFirstname($firstname);
        $customer->setLastname($lastname);
        $customer->setPasswordHash($userPassword);
        $customer->setAccountIsABuilder(1);
        return $customer;
    }
}