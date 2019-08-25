<?php
/**
 * @category    CleverSoft
 * @package     CleverMegaMenus
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author      ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverMegaMenus\Block\System\Config\Form\Field;

class Information extends \Magento\Config\Block\System\Config\Form\Fieldset
{
    /**
     * @var \Magento\Config\Block\System\Config\Form\Field
     */
    protected $_fieldRenderer;

    /**
     * @var \Magento\Framework\Module\ModuleListInterface
     */
    protected $_moduleList;

    /**
     * @var \Magento\Framework\Module\ModuleResource
     */
    private $moduleResource;

    /**
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param \Magento\Framework\View\Helper\Js $jsHelper
     * @param \Magento\Framework\Module\ModuleListInterface $moduleList
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Framework\View\Helper\Js $jsHelper,
        \Magento\Framework\Module\ModuleListInterface $moduleList,
        \Magento\Framework\Module\ModuleResource $moduleResource,
        \Magento\Framework\App\Request\Http $request,
        array $data = []
    ) {
        parent::__construct($context, $authSession, $jsHelper, $data);
        $this->_moduleList = $moduleList;
        $this->moduleResource = $moduleResource;
        $this->_request = $request;
    }

    /**
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $html = $this->_getHeaderHtml($element);

        $html .= $this->_getInfo();

        $html .= $this->_getFooterHtml($element);

        return $html;
    }

    /**
     * Return header title part of html for fieldset
     *
     * @param AbstractElement $element
     * @return string
     */
    protected function _getHeaderTitleHtml($element)
    {
        $html = '<a id="' .
            $element->getHtmlId() .
            '-head" href="#' .
            $element->getHtmlId() .
            '-link" >' . $element->getLegend() . '</a>';
        $html .= ' <style>
                    #'. $element->getHtmlId() .'-head:before { content: none;}
                </style>';

        return $html;
    }

	protected function _getInfo()
	{
		$html = '<div class="cleversoft-info-block">
            <div class="cleversoft-module-version">
                <div><span class="version-title">CleverMegaMenus <span class="module-version last-version">'.$this->moduleResource->getDbVersion('CleverSoft_CleverMegaMenus').'</span> by </span>
                    <a href="https://cleveraddon.com/" title="CleverSoft" target="_blank"><img class="cleversoft-logo" src="' . $this->getViewFileUrl('CleverSoft_Base::images/logo.png') . '" alt="CleverSoft"/></a>
                </div>
            </div>
            <div class="cleversoft-user-guide">
                <span class="message success">Need help with the settings?  Please  consult the <a target="_blank" href="https://doc.zooextension.com/magento/clevermegamenu">user guide</a> to configure the extension properly.</span></div>
        </div>';

		return $html;
	}
}