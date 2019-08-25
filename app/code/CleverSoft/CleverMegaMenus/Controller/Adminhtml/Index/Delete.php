<?php
/**
 * @category    CleverSoft
 * @package     CleverMegaMenus
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */
 
namespace CleverSoft\CleverMegaMenus\Controller\Adminhtml\Index;
class Delete extends \Magento\Backend\App\Action{
	public function execute(){
		$menu_id = $this->getRequest()->getParam('id');
		$redirect = $this->resultRedirectFactory->create();
		if($menu_id){
			try {	
				$this->_objectManager->create('CleverSoft\CleverMegaMenus\Model\Megamenu')->load($menu_id)->delete();
				$this->messageManager->addSuccessMessage(__('Menu has been deleted.'));
				return $redirect->setPath('megamenu/*/manager');
			}catch(\Exception $e){
				$this->messageManager->addErrorMessage($e->getMessage());
				return $redirect->setPath('megamenu/*/new', ['id' => $menu_id]);
			}
		}
		$this->messageManager->addErrorMessage(__('The menu not found.'));
		return $redirect->setPath('megamenu/*/manager');
	}
}