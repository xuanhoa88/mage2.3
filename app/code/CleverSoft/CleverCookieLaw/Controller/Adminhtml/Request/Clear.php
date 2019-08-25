<?php

namespace CleverSoft\CleverCookieLaw\Controller\Adminhtml\Request;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Clear extends \Magento\Backend\App\Action
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
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \CleverSoft\CleverCookieLaw\Model\RectifyFactory $rectifyFactory,
        \CleverSoft\CleverCookieLaw\Model\ComplaintFactory $complaintFactory,
        \CleverSoft\CleverCookieLaw\Model\DeleteFactory $deleteFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \CleverSoft\CleverCookieLaw\Helper\Data $helper
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->_coreRegistry = $registry;
        $this->_objectManager = $objectmanager;
        $this->_customerFactory = $customerFactory;
        $this->_rectifyFactory = $rectifyFactory;
        $this->_complaintFactory = $complaintFactory;
        $this->_deleteFactory = $deleteFactory;
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
        switch ($data['action']) {
            case "rectify":
                $collection = $this->_rectifyFactory->create()->getCollection();
                break;
            case "complaint":
                $collection = $this->_complaintFactory->create()->getCollection();
                break;
            case "delete":
                $collection = $this->_deleteFactory->create()->getCollection();
                break;
            default:
                $collection = $this->_rectifyFactory->create()->getCollection();
        }
        
        if ($data['type'] == 'resolved') {
            $collection->addFieldToFilter('status',array('in' => array("canceled","resolved")));
        }
        if (count($collection)) {
            try {
                foreach ($collection as $item) {
                    $item->delete();
                }
                $this->messageManager->addSuccess("Requests has been cleaned!");
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            }
        }  
        
        return $this->resultRedirectFactory->create()->setPath(
                        '*/*/index',
                        ['_secure' => $this->getRequest()->isSecure()]
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
