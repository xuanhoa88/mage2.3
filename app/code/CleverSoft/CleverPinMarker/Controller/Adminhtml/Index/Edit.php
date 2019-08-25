<?php
/**
 * Copyright © 2017 CleverSoft, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace CleverSoft\CleverPinMarker\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use Magento\Framework\View\Result\PageFactory;

class Edit extends \Magento\Backend\App\Action
{
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
	public function execute()
    {
		$entityId = $this->getRequest()->getParam('id');
		$model = $this->_objectManager->create('CleverSoft\CleverPinMarker\Model\PinMarker');
		$storeId = (int)$this->getRequest()->getParam('store');
		if ($entityId) {
			$model->setStore($storeId);
			$model->setStoreId($storeId);
			$model->load($entityId);
		}
	
		$data = $this->_objectManager->get('Magento\Backend\Model\Session')->getFormData(true);
		if (!empty($data)) {
			$model->setData($data);
		}
	
		$this->_coreRegistry->register('pinmarker', $model);
	
		/** @var \Magento\Backend\Model\View\Result\Page $resultPage */
		$resultPage = $this->_initAction();
		if($entityId) {
            $resultPage->addBreadcrumb(
                __('Edit Pin'),
                __('Edit Pin')
            );

            $resultPage->getConfig()->getTitle()->prepend(__('Edit Pin'));
            $resultPage->getConfig()->getTitle()
                ->prepend($model->getPinMarkerLabel() ? $model->getPinMarkerLabel() : __('Edit Pin'));
        } else {
            $resultPage->addBreadcrumb(
                __('New Pin'),
                __('New Pin')
            );

            $resultPage->getConfig()->getTitle()->prepend(__('New Pin'));
            $resultPage->getConfig()->getTitle()
                ->prepend($model->getPinMarkerLabel() ? $model->getPinMarkerLabel() : __('New Pin'));
        }
		return $resultPage;
    }
	
	protected function _initAction()
	{
		$resultPage = $this->resultPageFactory->create();
		$resultPage->setActiveMenu('CleverSoft_CleverPinMarker::pinmarker');
		return $resultPage;
	}
	
	protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('CleverSoft_CleverPinMarker::edit');
    }
}