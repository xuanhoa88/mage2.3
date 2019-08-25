<?php
/**
 * @category    CleverSoft
 * @package     CleverLayeredNavigation
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */
 
namespace CleverSoft\CleverBuilder\Controller\Builder\Edit;
use Magento\Framework\Controller\ResultFactory;

class Header extends \Magento\Framework\App\Action\Action {
    /*
     *
     */
    protected $_customerSession;
    /*
     *
     */
	public function __construct(
		\Magento\Framework\App\Action\Context $context,
		\Magento\Customer\Model\Session $customerSession
	)     
	{
		$this->_customerSession = $customerSession;
		parent::__construct($context);
	}
    
    public function execute()
    {
		if($this->_customerSession->getUsePanel() == 1){
			$this->_view->loadLayout();
			$this->_view->renderLayout();
		}else{
			$resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
			$resultRedirect->setUrl($this->_redirect->getRefererUrl());
			return $resultRedirect;
		}
        
    }
}
