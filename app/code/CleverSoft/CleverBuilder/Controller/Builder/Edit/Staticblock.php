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
use CleverSoft\CleverBuilder\Helper\Data as Data;

class Staticblock extends \Magento\Framework\App\Action\Action  {
    /*
   *
   */
    protected $_customerSession;
    protected $_helperData;
    /*
     *
     */
	public function __construct(
		\Magento\Framework\App\Action\Context $context,
		\Magento\Customer\Model\Session $customerSession,
        Data $data
	)     
	{
		$this->_customerSession = $customerSession;
		$this->_helperData = $data;
		parent::__construct($context);
	}
    
    public function execute()
    {
		if($this->_helperData->isEnabledBuilder()){
			$this->_view->loadLayout();
			$this->_view->renderLayout();
		}else{
			$resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
			$resultRedirect->setUrl($this->_redirect->getRefererUrl());
			return $resultRedirect;
		}
        
    }
}
