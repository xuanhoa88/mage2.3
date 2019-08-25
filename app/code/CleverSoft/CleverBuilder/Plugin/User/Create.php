<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace CleverSoft\CleverBuilder\Plugin\User;
use CleverSoft\CleverBuilder\Helper\User\Create as FrontEndBuilderCreate;
use Magento\User\Model\User;
use Magento\Framework\Controller\Result\RedirectFactory as RedirectFactory;
use Magento\Framework\Controller\ResultFactory as ResultFactory;

/**
 * Plugin for authorization role model
 */
class Create
{
    protected $_createFrontendBuilder;
    protected $_resultFactory;
    protected $_storeManager;
    protected $_redirectFactory;
    protected $messageManager;
    /*
     *
     */
    public function __construct(FrontEndBuilderCreate  $createFrontendBuilder,ResultFactory $resultFactory, /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection */
                                \Magento\Store\Model\StoreManagerInterface $storeManager, RedirectFactory $redirectFactory, /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection */
                                \Magento\Framework\Message\ManagerInterface $messageManager) {
        $this->_createFrontendBuilder = $createFrontendBuilder;
        $this->_resultFactory = $resultFactory;
        $this->_storeManager     = $storeManager;
        $this->_redirectFactory = $redirectFactory;
        $this->messageManager = $messageManager;
    }

    /**
     *
     */

    public function afterSave(User $user) {
        if(!$user->getId()) {
            $this->messageManager->addError(__('User not found in %1 (website).', $this->_storeManager->getWebsite()->getName()));
        }
        if(!$user->getAccountIsABuilder()) return;
        /*
         *
         */
        $customer = $this->_createFrontendBuilder->builderAccountCreater($user->getId());

        //save customer
        try{
            $customer->save();
            $this->messageManager->addSuccess(__('Front-end Builder account have been created for %1 (website).', $this->_storeManager->getWebsite()->getName()));
        }catch(\Exception $e){
            $this->messageManager->addError(__($e->getMessage()));
        }
    }
}
