<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CleverSoft\CLeverBuilder\Controller\Page;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\Action;

/**
 * Custom page for storefront. Needs to be accessible by POST because of the store switching.
 */
class View extends \Magento\Cms\Controller\Page\View
{
    /**
     * @var \Magento\Framework\Controller\Result\ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\ForwardFactory $resultForwardFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Controller\Result\ForwardFactory $resultForwardFactory,
        \CleverSoft\CleverBuilder\Helper\Data $helper
    ) {
        parent::__construct($context, $resultForwardFactory);
        $this->helper = $helper;
    }

    /**
     * View CMS page action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $pageId = $this->getRequest()->getParam('page_id', $this->getRequest()->getParam('id', false));
        
        $resultPage = $this->_objectManager->get(\Magento\Cms\Helper\Page::class)->prepareResultPage($this, $pageId);
        if ($this->helper->isEnabledBuilder()) {
            $resultPage->setHeader('pragma', 'no-cache', true);
            $resultPage->setHeader('cache-control', 'no-store, no-cache, must-revalidate, max-age=0', true);
        }
        
        if (!$resultPage) {
            $resultForward = $this->resultForwardFactory->create();
            return $resultForward->forward('noroute');
        }
        return $resultPage;
    }
}
