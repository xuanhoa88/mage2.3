<?php
/**
 * @category    CleverSoft
 * @package     Base
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author      ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\Base\Block\System\Config;

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
        array $data = []
    ) {
        parent::__construct($context, $authSession, $jsHelper, $data);
        $this->_moduleList = $moduleList;
        $this->moduleResource = $moduleResource;
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

	protected function _getInfo()
	{
		$html = '<div class="cleversoft-store-info">';
		$html .= '	<h3>Company</h3>';
		$html .= '	<p>Founded in 2008, <a href="http://cleversoft.co/" title="CleverSoft" target="_blank">CleverSoft</a> quickly confirmed its position as one of the innovative young companies in the field of Website Design & Development. Our experience, expertise, best-equipped facility, professional management and commitment have been bringing our customers utmost satisfaction and in return rewarded us with rapid and sustainable growths over the past 8 years.

We focus on creating cutting edge designs using the latest in web technologies and tools. With every new project, we take the opportunity to discover new innovative potential. The excitement of the next web project, never ceases to exist as we challenge our creative think tanks to give every web site that “WOW” factor, our clients so keenly search for. We do make the web a better looking place and can help you achieve the same with your web site. We curb our insight, dedication and expertise to challenge the boundaries of creativity and deliver on tomorrow’s.

CleverSoft Solutions’ guiding rule is very simple, we offer expert skill, innovative creations, full commitment, assets and excellent customer support to all our clients and help them to progress their business to the peak.</p>';
		$html .= '	<p></p>';
		$html .= '	<h3>Follow Us</h3>';
		$html .= '	<div class="cleversoft-follow">
            <a href="https://cleveraddon.com/" title="CleverSoft" target="_blank"><img src="' . $this->getViewFileUrl('CleverSoft_Base::images/logo.png') . '" alt="CleverSoft"/></a>
            <a href="https://themeforest.net/user/cleversoft" title="CleverSoft" target="_blank"><img src="' . $this->getViewFileUrl('CleverSoft_Base::images/themeforest.png') . '" alt="Themefores CleverSoft"/></a>
            <a href="https://www.facebook.com/cleversoft.co/" title="CleverSoft" target="_blank"><img src="' . $this->getViewFileUrl('CleverSoft_Base::images/facebook.png') . '" alt="Facebook CleverSoft"/></a>
            <a href="https://twitter.com/cleversoftco?lang=en" title="CleverSoft" target="_blank"><img src="' . $this->getViewFileUrl('CleverSoft_Base::images/twitter.png') . '" alt="Twitter CleverSoft"/></a>
        </div>';
        $html .= '  <p></p>';
        $html .= '  <h3>Featured Item</h3>';
        $html .= '  <div class="cleversoft-featured-item">
            <div class="cleversoft-button-wrapper">
                <a href="https://cleveraddon.com/cleverbuilder-page-builder-magento-2/" class="cleversoft-button-link cleversoft-button" role="button">
                    <span class="cleversoft-button-content-wrapper">
                            <span class="cleversoft-button-text">Get Started</span>
                    </span>
                </a>
            </div>
            <iframe width="860" height="500" style="margin:0 auto;display: block" src="https://www.youtube.com/embed/yAav0OU7DkY" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
        </div>';
		$html .= '</div>';

		return $html;
	}
}