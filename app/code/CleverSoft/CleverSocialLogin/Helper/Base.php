<?php
/**
 * @category    CleverSoft
 * @package     CleverSocialLogin
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverSocialLogin\Helper;

class Base extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $_objectManager;
    protected $_link;

    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Customer\Block\Account\Link $link,
        \Magento\Framework\App\Helper\Context $context
    ) {

        $this->_objectManager = $objectManager;
        $this->_link = $link;
        parent::__construct($context);
    }


    public function getConfigSectionId()
    {
        return $this->_configSectionId;
    }

    public function getUrlCustomer() {
        return $this->_link->getHref();
    }

    public function getConfig($path, $store = null, $scope = null)
    {
        if ($scope === null) {
            $scope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        }
        return $this->scopeConfig->getValue($path, $scope, $store);
    }


    public static function backtrace($title = 'Debug Backtrace:', $echo = true)
    {
        $output     = "";
        $output .= "<hr /><div>" . $title . '<br /><table border="1" cellpadding="2" cellspacing="2">';

        $stacks     = debug_backtrace();

        $output .= "<thead><tr><th><strong>File</strong></th><th><strong>Line</strong></th><th><strong>Function</strong></th>".
            "</tr></thead>";
        foreach($stacks as $_stack)
        {
            if (!isset($_stack['file'])) $_stack['file'] = '[PHP Kernel]';
            if (!isset($_stack['line'])) $_stack['line'] = '';

            $output .=  "<tr><td>{$_stack["file"]}</td><td>{$_stack["line"]}</td>".
                "<td>{$_stack["function"]}</td></tr>";
        }
        $output .=  "</table></div><hr /></p>";
        if ($echo) {
            echo $output;
        } else {
            return $output;
        }
    }

    public function moduleExists($moduleName)
    {
        $hasModule = $this->_objectManager->get('Magento\Framework\Module\Manager')->isEnabled('CleverSoft_' . $moduleName);
        if($hasModule) {
            return $this->_objectManager->get('CleverSoft\\'. $moduleName .'\Helper\Data')->moduleEnabled()? 2 : 1;
        }

        return false;
    }

}
