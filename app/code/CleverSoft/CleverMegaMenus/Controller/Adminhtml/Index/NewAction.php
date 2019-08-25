<?php
/**
 * @category    CleverSoft
 * @package     CleverMegaMenus
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverMegaMenus\Controller\Adminhtml\Index;
use Magento\Backend\App\Action;

class NewAction extends \Magento\Backend\App\Action {
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    public function __construct(
        Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Registry $registry
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->_coreRegistry = $registry;
        parent::__construct($context);
    }

    protected function _initAction() {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('CleverSoft_CleverMegaMenus::megamenu')
            ->addBreadcrumb(__('Megamenu'), __('Megamenu'))
            ->addBreadcrumb(__('Menus Manager'), __('Menus Manager'));
        return $resultPage;
    }

    public function execute(){
        $id = $this->getRequest()->getParam('id');
        $model = $this->_objectManager->create('CleverSoft\CleverMegaMenus\Model\Megamenu');
        $model->setMenuId($id);
        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addError(__('This menu no longer exists.'));
                /** \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/manager');
            }
        }

        $data = $this->_objectManager->get('Magento\Backend\Model\Session')->getFormData(true);
        if (!empty($data)) {
            $model->setData($data);
        }

        $this->_coreRegistry->register('megamenu', $model);

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->_initAction();
        $resultPage->addBreadcrumb(
            $id ? __('Edit Menu') : __('New Menu'),
            $id ? __('Edit Menu') : __('New Menu')
        );
        $resultPage->getConfig()->getTitle()->prepend(__('Megamenu'));
        $resultPage->getConfig()->getTitle()
            ->prepend($model->getId() ? $model->getName() : __('New Menu'));

        return $resultPage;
    }
}