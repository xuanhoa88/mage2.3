<?php
/**
 * @category    CleverSoft
 * @package     CleverSocialLogin
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverSocialLogin\Block;

class General extends \Magento\Framework\View\Element\Template
{
    protected $_objectManager;
    protected $_dataHelper;

    protected function _construct()
    {
        parent::_construct();

        $this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->_dataHelper = $this->_objectManager->get('CleverSoft\CleverSocialLogin\Helper\Data');
        }

	protected function _toHtml()
    {
        if(!$this->_dataHelper->moduleEnabled()) {
            return;
        }

        return parent::_toHtml();
    }

    public function getSkipModules()
    {
        $skipModules = $this->_dataHelper->getRefererLinkSkipModules();
        return json_encode($skipModules);
    }
}