<?php

namespace CleverSoft\CleverCookieLaw\Controller\Adminhtml\Request;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Cancel extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'CleverSoft_CleverCookieLaw::request';

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\ObjectManagerInterface $objectmanager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \CleverSoft\CleverCookieLaw\Helper\Data $helper
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->_coreRegistry = $registry;
        $this->_objectManager = $objectmanager;
        $this->_storeManager = $storeManager;
        $this->_helper = $helper;
    }

    /**
     * Index action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        if ($data['id']) {
            switch ($data['action']) {
                case "rectify":
                    $model = $this->_objectManager->create("CleverSoft\CleverCookieLaw\Model\Rectify")->load($data['id']);
                    break;
                case "complaint":
                    $model = $this->_objectManager->create("CleverSoft\CleverCookieLaw\Model\Complaint")->load($data['id']);
                    break;
                case "delete":
                    $model = $this->_objectManager->create("CleverSoft\CleverCookieLaw\Model\Delete")->load($data['id']);
                    break;
                default:
                    $model = $this->_objectManager->create("CleverSoft\CleverCookieLaw\Model\Rectify")->load($data['id']);
            }
            
            try {
                $model->setStatus("canceled");
                $model->save();
                $this->sendMail($data);
                $this->messageManager->addSuccess("This request has been canceled!");
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            }
        }
        
        return $this->resultRedirectFactory->create()->setPath(
                        '*/*/index',
                        ['_secure' => $this->getRequest()->isSecure()]
                    );
    }

    private function sendMail($data)
    {
        $helper = $this->_helper;

        $emailTempVariables = [];
        $adminStoremail = $helper->getStoreEmail();
        $adminEmail = $adminStoremail ?
            $adminStoremail : $helper->getDefaultTransEmailId();
        $adminStorename = $helper->getStorename();
        $userEmail = $data['email'];
        $userName = preg_replace('/@.*/', '', $userEmail);
        
        $emailTempVariables['customer_name'] = $userName;
        $emailTempVariables['templateSubject'] = "Your request has been canceled.";
        $emailTempVariables['request_mess'] = "For some reason, We canceled your" . " ". $data['action'] . "request.";
        

        $senderInfo = [
            'name' => $adminStorename,
            'email' => $adminEmail,
        ];
        $receiverInfo = [
            'name' => $userName,
            'email' => $userEmail,
        ];
        
        $helper->sendConfirmMail(
                    $emailTempVariables,
                    $senderInfo,
                    $receiverInfo
                );
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed(self::ADMIN_RESOURCE);
    }
}
