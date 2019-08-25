<?php
/**
 * @category    CleverSoft
 * @package     CleverMegaMenus
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */
 
namespace CleverSoft\CleverMegaMenus\Controller\Adminhtml\Wysiwyg\Images;

class OnInsert
{
    private $_objectManager;

    public function __construct(
        \Magento\Backend\Model\Session $backendSession,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        \Magento\Framework\ObjectManagerInterface $objectmanager
    ){
        $this->_backendSession = $backendSession;
        $this->resultRawFactory = $resultRawFactory;
        $this->_objectManager = $objectmanager;
    }

    public function afterExecute(\Magento\Cms\Controller\Adminhtml\Wysiwyg\Images\OnInsert $action, $page)
    {
        $element = $this->_backendSession->getElement();
        if ($element == 'megamenu-image') {
            $cmsHelper = $this->_objectManager->get('Magento\Cms\Helper\Wysiwyg\Images');
            $menuHelper = $this->_objectManager->get('CleverSoft\CleverMegaMenus\Helper\Wysiwyg\Images');
            $storeId = $action->getRequest()->getParam('store');
    
            $filename = $action->getRequest()->getParam('filename');
            $filename = $cmsHelper->idDecode($filename);
    
            $this->_objectManager->get('Magento\Catalog\Helper\Data')->setStoreId($storeId);
            $cmsHelper->setStoreId($storeId);
    
            $image = '{{media url="'.$menuHelper->getImageRelativeUrl($filename).'"}}';
    
            $this->_backendSession->unsetElement();
            /** @var \Magento\Framework\Controller\Result\Raw $resultRaw */
            $resultRaw = $this->resultRawFactory->create();
            return $resultRaw->setContents($image);
        } else {
            return $page;
        }
	}
}