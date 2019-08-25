<?php
/**
 * @category    CleverSoft
 * @package     CleverSocialLogin
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverSocialLogin\Block;

class Buttons extends \Magento\Framework\View\Element\Template
{
    protected $_countFullButtons = 6;
    protected $_output2js = false;
    protected $_checkPosition = null;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context
    )
    {
        parent::__construct($context);
    }

    public function getHelper()
    {
        return \Magento\Framework\App\ObjectManager::getInstance()->get('CleverSoft\CleverSocialLogin\Helper\Data');
    }

    public function getPreparedButtons($part = null)
    {
        return $this->getHelper()->getPreparedButtons($part);
    }

    public function hasButtons()
    {
        return (bool)$this->getPreparedButtons();
    }

    public function showLoginFullButtons()
    {
        $visible = $this->getPreparedButtons('visible');
        return count($visible) <= $this->_countFullButtons;
    }

    public function showRegisterFullButtons()
    {
        return $this->showFullButtons();
    }

    public function showFullButtons()
    {
        $all = $this->getPreparedButtons();
        return count($all) <= $this->_countFullButtons;
    }

    public function setFullButtonsCount($count)
    {
        if(is_numeric($count) && $count >= 0) {
            $this->_countFullButtons = $count;
        }
        return $this;
    }

    /*public function isAutocompleteDisabled()
    {
        return true;
    }*/

    public function setOutput2js($flag = true)
    {
        $this->_output2js = (bool)$flag;
    }


    public function checkPosition($position = null)
    {
        $this->_checkPosition = $position;
    }

    public function _afterToHtml($html)
    {
        if ($this->_checkPosition) {
            if (!$this->getHelper()->modulePositionEnabled($this->_checkPosition)) {
                $html = '';
            }
        }

        if ($this->_output2js && trim($html)) {
            $html = '<script>'
                . 'window.psloginButtons = \'' . str_replace(["\n", 'script',"\r",], ['', "scri'+'pt",''], $this->escapeJsQuote($html)) . '\';'
                . '</script>';
        }

        return parent::_afterToHtml($html);
    }
}