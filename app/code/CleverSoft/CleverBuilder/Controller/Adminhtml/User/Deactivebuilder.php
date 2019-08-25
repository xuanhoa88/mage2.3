<?php
/**
 * @category    CleverSoft
 * @package     CleverPageBuilder
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author      ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */
namespace CleverSoft\CleverBuilder\Controller\Adminhtml\User;
use Magento\Framework\Controller\ResultFactory;
use CleverSoft\CleverBuilder\Helper\Customer\Reindex;

class Deactivebuilder extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;
    protected $_userFactory;
    protected $_reindexer;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;

    /**
     * @param \Magento\Framework\App\Action\Context      $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Customer\Model\CustomerFactory    $customerFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\User\Model\UserFactory $userFactory,
        \Magento\Framework\Registry $registry,
        Reindex $reindex,
        \Magento\Customer\Model\CustomerFactory $customerFactory
    ) {
        $this->storeManager     = $storeManager;
        $this->_userFactory = $userFactory;
        $this->registry = $registry;
        $this->_reindexer  = $reindex;
        $this->customerFactory  = $customerFactory;

        parent::__construct($context);
    }

    public function execute()
    {
        // Get Website ID
        $user_id = $this->getRequest()->getParam('user_id');
        if ($user_id) {
            $websiteId  = $this->storeManager->getWebsite()->getWebsiteId();
            //load user data by id
            $user = $this->_userFactory->create()->load($user_id);
            $userEmail = $user->getEmail();

            $this->registry->register('isSecureArea', true);
            $customer = $this->customerFactory->create()->setWebsiteId($websiteId)->loadByEmail($userEmail);
            if ($customer->getId()){
                // Save data
                try{
                    if($customer->delete()){
                        $this->_reindexer->reindexAll();
                    }
                    $this->messageManager->addSuccess(__('Front-end Builder account have been deactived for %1 (website).', $this->storeManager->getWebsite()->getName()));
                }catch(\Exception $e){
                    $this->messageManager->addError(__($e->getMessage()));
                }
            } else {
                $this->messageManager->addSuccess(__('Front-end Builder account does not exist for %1 (website).', $this->storeManager->getWebsite()->getName()));
            }
        }

        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        return $resultRedirect;
    }
}