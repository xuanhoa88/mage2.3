<?php
/**
 * Copyright © 2017 CleverSoft, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace CleverSoft\CleverShopByBrand\Controller\Adminhtml\Index;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Index extends \Magento\Backend\App\Action
{
	protected $resultPageFactory;
	public function __construct(Context $context, PageFactory $resultPageFactory)
	{
		parent::__construct($context);
		$this->resultPageFactory = $resultPageFactory;
	}
	public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('CleverSoft_CleverShopByBrand::shopbybrand');
        $resultPage->addBreadcrumb(__('Brands Manager'), __('Brands Manager'));
        $resultPage->addBreadcrumb(__('Brands Manager'), __('Brands Manager'));
        $resultPage->getConfig()->getTitle()->prepend(__('Brands Manager'));

        return $resultPage;
    }
	/**
     * Is the user allowed to view the menu grid.
     *
     * @return bool
     */
	protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('CleverSoft_CleverShopByBrand::index');
    }
}