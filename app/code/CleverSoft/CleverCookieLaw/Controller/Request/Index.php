<?php
/**
 * @category    CleverSoft
 * @package     CleverCookieLaw
 * @copyright   Copyright © 2018 CleverSoft., JSC. All Rights Reserved.
 * @author      ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverCookieLaw\Controller\Request;

use Magento\Framework\App\Action;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Controller\ResultFactory;

class Index extends \Magento\Framework\App\Action\Action
{

    /**
     * @var \Magento\Framework\Registry
     */

    protected $_registry;

    /**
     * @param Action\Context $context
     */
    public function __construct(
        Action\Context $context,
        \CleverSoft\CleverCookieLaw\Helper\Data $helper,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\UrlInterface $urlInterface
    )
    {
        $this->_helper = $helper;
        $this->_registry = $registry;
        $this->_customerFactory = $customerFactory;
        $this->_storeManager = $storeManager;
        $this->_urlInterface = $urlInterface;
        
        parent::__construct($context);
    }

    public function execute()
    {
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        
        $request = $this->getRequest()->getParams();

        if ($request) {
            try {
                $this->sendMail($request);
                $this->messageManager->addSuccess("Your request has been sent!");
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            }
            
        }
        
        return $resultRedirect;
    }

    private function sendMail($request)
    {
        $helper = $this->_helper;

        $emailTempVariables = [];
        $adminStoremail = $helper->getStoreEmail();
        $adminEmail = $adminStoremail ?
            $adminStoremail : $helper->getDefaultTransEmailId();
        $adminStorename = $helper->getStorename();

        $userEmail = $request['user_email'];
        $userName = preg_replace('/@.*/', '', $userEmail);

        $websiteId = $this->_storeManager->getStore()->getWebsiteId();
        $user = $this->_customerFactory->create()->setWebsiteId($websiteId)->loadByEmail($userEmail);

        $key = $user->getData('password_hash');

        $linkReset = $this->_urlInterface->getUrl("customer/account/forgotpassword?email=$userEmail", ['_current' => true, '_use_rewrite' => true]);

        switch ($request["type"]) {
            case 'delete':
                $emailTempVariables['templateSubject'] = "Someone requested to close your account.";
                $emailTempVariables['link1'] = urldecode($this->_urlInterface->getUrl("cookielaw/request/delete", ['_query' => ['key' => $key, 'email' => $userEmail]]));

                break;
            case 'rectify':
                $emailTempVariables['templateSubject'] = "Someone requested that we rectify data of your account.";
                $emailTempVariables['request_mess'] = $request['data'];
                $emailTempVariables['link1'] = urldecode($this->_urlInterface->getUrl("cookielaw/request/rectify", ['_query' => ['key' => $key, 'email' => $userEmail, 'data' => $request['data']]]));

                break;
            case 'complaint':
                $emailTempVariables['templateSubject'] = "Someone made complaint on behalf of your account.";
                $emailTempVariables['request_mess'] = $request['data'];
                $emailTempVariables['link1'] = urldecode($this->_urlInterface->getUrl("cookielaw/request/complaint", ['_query' => ['key' => $key, 'email' => $userEmail, 'data' => $request['data']]]));


                break;
            case 'export-data':
                $emailTempVariables['templateSubject'] = "Someone requested to download your data.";
                $emailTempVariables['link1'] = urldecode($this->_urlInterface->getUrl("cookielaw/request/download", ['_query' => ['key' => $key, 'email' => $userEmail, 'type' => 'csv']]));
                $emailTempVariables['link1'] = urldecode($this->_urlInterface->getUrl("cookielaw/request/download", ['_query' => ['key' => $key, 'email' => $userEmail, 'type' => 'json']]));

                break;
            
            default:
                $emailTempVariables['templateSubject'] = "Someone requested that we rectify data of your account.";
                $emailTempVariables['link1'] = urldecode($this->_urlInterface->getUrl("cookielaw/request/rectify", ['_query' => ['key' => $key, 'email' => $userEmail]]));

                break;
        }

        $emailTempVariables['customer_name'] = $userName;
        $emailTempVariables['link_reset'] = $linkReset;
        

        $senderInfo = [
            'name' => $adminStorename,
            'email' => $adminEmail,
        ];
        $receiverInfo = [
            'name' => $userName,
            'email' => $userEmail,
        ];

        switch ($request["type"]) {
            case 'delete':
                $helper->sendDeleteMail(
                    $emailTempVariables,
                    $senderInfo,
                    $receiverInfo
                );
                break;
            case 'rectify':
                $helper->sendRectifyMail(
                    $emailTempVariables,
                    $senderInfo,
                    $receiverInfo
                );
                break;
            case 'complaint':
                $helper->sendComplaintMail(
                    $emailTempVariables,
                    $senderInfo,
                    $receiverInfo
                );
                break;
            case 'export-data':
                $helper->sendDataMail(
                    $emailTempVariables,
                    $senderInfo,
                    $receiverInfo
                );
                break;
            
            default:
                $helper->sendRectifyMail(
                    $emailTempVariables,
                    $senderInfo,
                    $receiverInfo
                );
                break;
        }
    }
}
