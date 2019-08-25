<?php
/**
 * Copyright © 2017 CleverSoft, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace CleverSoft\CleverPinMarker\Controller\Adminhtml\Collection;

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
		$model = $this->_objectManager->create('CleverSoft\CleverPinMarker\Model\PinCollection');
		$storeId = (int)$this->getRequest()->getParam('store');
		if ($entityId) {
			$model->load($entityId);
		}
	
		$data = $this->_objectManager->get('Magento\Backend\Model\Session')->getFormData(true);
		if (!empty($data)) {
			$model->setData($data);
		}
	
		$this->_coreRegistry->register('pincollection', $model);
	
		/** @var \Magento\Backend\Model\View\Result\Page $resultPage */
		$resultPage = $this->_initAction();
		if($entityId) {
            $resultPage->addBreadcrumb(
                __('Edit Collection'),
                __('Edit Collection')
            );

            $resultPage->getConfig()->getTitle()->prepend(__('Edit Collection'));
            $resultPage->getConfig()->getTitle()
                ->prepend($model->getCollectionName() ? $model->getCollectionName() : __('Edit Collection'));
        } else {
            $resultPage->addBreadcrumb(
                __('New Collection'),
                __('New Collection')
            );

            $resultPage->getConfig()->getTitle()->prepend(__('New Collection'));
            $resultPage->getConfig()->getTitle()
                ->prepend($model->getCollectionName() ? $model->getCollectionName() : __('New Collection'));
        }
		return $resultPage;
    }
	
	protected function _initAction()
	{
		$resultPage = $this->resultPageFactory->create();
		$resultPage->setActiveMenu('CleverSoft_CleverPinMarker::pincollection');
		return $resultPage;
	}
	
	protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('CleverSoft_CleverPinMarker::collection');
    }
}