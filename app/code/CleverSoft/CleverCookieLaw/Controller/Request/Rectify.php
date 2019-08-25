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

class Rectify extends \Magento\Framework\App\Action\Action
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
        \Magento\Framework\ObjectManagerInterface $objectmanager,
        \CleverSoft\CleverCookieLaw\Model\RectifyFactory $requestRectifyFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $date
    )
    {
        $this->_helper = $helper;
        $this->_registry = $registry;
        $this->_customerFactory = $customerFactory;
        $this->_storeManager = $storeManager;
        $this->_objectManager = $objectmanager;
        $this->_requestRectifyFactory = $requestRectifyFactory;
        $this->_date = $date;
        
        parent::__construct($context);
    }

    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        
        $request = $this->getRequest()->getParams();
        if ($request) {
            try {
                $email = $request['email'];
                $hashKey = $request['key'];

                $websiteId = $this->_storeManager->getStore()->getWebsiteId();
                $user = $this->_customerFactory->create()->setWebsiteId($websiteId)->loadByEmail($email);
                $key = $user->getData('password_hash');

                if ($hashKey == $key) {
                    $this->sendRequest($request);
                    $this->messageManager->addSuccess("Your request has been sent!");
                } else {
                    $this->messageManager->addError("Permission denied!");
                    return $this->resultRedirectFactory->create()->setPath(
                        '',
                        ['_secure' => $this->getRequest()->isSecure()]
                    );
                }
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            }
            
        }
        
        return $this->resultRedirectFactory->create()->setPath(
                        '',
                        ['_secure' => $this->getRequest()->isSecure()]
                    );
    }

    protected function sendRequest($request) {
        $id = $this->_requestRectifyFactory->create()->getCollection()->addFieldToFilter("email", $request['email'])->getFirstItem()->getId();
        $model = $this->_objectManager->create("CleverSoft\CleverCookieLaw\Model\Rectify");
        if ($id) {
            $model->load($id);
        }
        $model->setEmail($request['email']);
        $model->setInformation($request['data']);
        $model->setCreatedAt($this->_date->gmtDate());
        $model->setStatus("");
        try {
            $model->save();
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        }
    }
}
