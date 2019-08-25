<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CleverSoft\CleverMegamenus\Controller\Adminhtml\Wysiwyg\Images;

class Index
{
    public function __construct(
        \Magento\Backend\Model\Session $backendSession
    ){
        $this->_backendSession = $backendSession;
    }
    public function afterExecute(\Magento\Cms\Controller\Adminhtml\Wysiwyg\Images\Index $action, $page)
    {
        $this->_backendSession->setElement($action->getRequest()->getParam('element_name'));
	    return $page;
    }
}

