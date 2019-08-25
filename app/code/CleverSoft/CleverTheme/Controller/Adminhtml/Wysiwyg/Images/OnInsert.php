<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CleverSoft\CleverTheme\Controller\Adminhtml\Wysiwyg\Images;

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
        if ($element == 'settings[header][logo][value]' || $element == 'settings[header][logo_mobile][value]' || $element == 'settings[header][sticky_logo][value]') {
            $cmsHelper = $this->_objectManager->get('Magento\Cms\Helper\Wysiwyg\Images');
            $menuHelper = $this->_objectManager->get('CleverSoft\CleverMegaMenus\Helper\Wysiwyg\Images');
            $storeId = $action->getRequest()->getParam('store');
    
            $filename = $action->getRequest()->getParam('filename');
            $filename = $cmsHelper->idDecode($filename);
    
            $this->_objectManager->get('Magento\Catalog\Helper\Data')->setStoreId($storeId);
            $cmsHelper->setStoreId($storeId);
    
            $image = $menuHelper->getImageRelativeUrl($filename);
    
            $this->_backendSession->unsetElement();
            /** @var \Magento\Framework\Controller\Result\Raw $resultRaw */
            $resultRaw = $this->resultRawFactory->create();
            return $resultRaw->setContents($image);
        } else {
            return $page;
        }
    }
}
