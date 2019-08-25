<?php

namespace CleverSoft\CleverPinMarker\Controller\Adminhtml\Collection;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Pins extends \Magento\Backend\App\Action
{
    protected $resultPageFactory;
	public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory
    )
	{
		parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->_resultLayoutFactory = $resultLayoutFactory;
    }
    
    public function execute()
    {
        $resultLayout = $this->_resultLayoutFactory->create();
        $resultLayout->getLayout()->getBlock('pinmarker.collection.edit.tab.pins')
                     ->setInPin($this->getRequest()->getPost('pin', null));

        return $resultLayout;
    }
}
