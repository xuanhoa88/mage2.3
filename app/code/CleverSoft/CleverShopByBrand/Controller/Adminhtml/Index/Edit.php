<?php
/**
 * Copyright © 2017 CleverSoft, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace CleverSoft\CleverShopByBrand\Controller\Adminhtml\Index;

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
		$model = $this->_objectManager->create('CleverSoft\CleverShopByBrand\Model\Brand');
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
	
		$this->_coreRegistry->register('brand', $model);
	
		/** @var \Magento\Backend\Model\View\Result\Page $resultPage */
		$resultPage = $this->_initAction();
		if($entityId) {
            $resultPage->addBreadcrumb(
                __('Edit Brand'),
                __('Edit Brand')
            );

            $resultPage->getConfig()->getTitle()->prepend(__('Edit Brand'));
            $resultPage->getConfig()->getTitle()
                ->prepend($model->getBrandLabel() ? $model->getBrandLabel() : __('Edit Brand'));
        } else {
            $resultPage->addBreadcrumb(
                __('New Brand'),
                __('New Brand')
            );

            $resultPage->getConfig()->getTitle()->prepend(__('New Brand'));
            $resultPage->getConfig()->getTitle()
                ->prepend($model->getBrandLabel() ? $model->getBrandLabel() : __('New Brand'));
        }
		return $resultPage;
    }
	
	protected function _initAction()
	{
		$resultPage = $this->resultPageFactory->create();
		$resultPage->setActiveMenu('CleverSoft_CleverShopByBrand::shopbybrand');
		return $resultPage;
	}
	
	protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('CleverSoft_CleverShopByBrand::edit');
    }
}