<?php
/**
 * @category    CleverSoft
 * @package     CleverPageBuilder
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author      ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */
namespace CleverSoft\CleverBuilder\Controller\Adminhtml\User;
use CleverSoft\CleverBuilder\Helper\User\Create as FrontEndBuilderCreate;
use Magento\Framework\Controller\ResultFactory;
use CleverSoft\CleverBuilder\Helper\Customer\Reindex;

class Create extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    protected $_userFactory;
    protected $_createFrontendBuilder;
    protected $_reindexer;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $_customerFactory;

    /**
     * @param \Magento\Framework\App\Action\Context      $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Customer\Model\CustomerFactory    $customerFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\User\Model\UserFactory $userFactory,
        FrontEndBuilderCreate  $createFrontendBuilder,
        Reindex $reindex,
        \Magento\Customer\Model\CustomerFactory $customerFactory
    ) {
        $this->_storeManager     = $storeManager;
        $this->_userFactory = $userFactory;
        $this->_createFrontendBuilder = $createFrontendBuilder;
        $this->_customerFactory  = $customerFactory;
        $this->_reindexer  = $reindex;

        parent::__construct($context);
    }

    public function execute()
    {
        // Get Website ID
        $user_id = $this->getRequest()->getParam('user_id');
        if ($user_id) {
            $customer = $this->_createFrontendBuilder->builderAccountCreater($user_id);
            // Save data
            try{
                if($customer->save()) {
                    $this->_reindexer->reindexAll();
                }
                $this->messageManager->addSuccess(__('Front-end Builder account have been created for %1 (website).', $this->_storeManager->getWebsite()->getName()));
            }catch(\Exception $e){
                $this->messageManager->addError(__($e->getMessage()));
            }

        }

        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        return $resultRedirect;
    }
}