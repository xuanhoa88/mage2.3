<?php
/**
 * @category    CleverSoft
 * @package     CleverSocialLogin
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverSocialLogin\Block\Adminhtml\System\Config\Form\Field;

use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Config\Block\System\Config\Form\Field as FormField;
use CleverSoft\CleverSocialLogin\Helper\Social as SocialHelper;
use Magento\Backend\Block\Template\Context;

/**
 * Backend system config datetime field renderer
 */
class Redirect extends FormField
{
	/**
	 */
	protected $socialHelper;
	protected $socialType = '';

	/**
	 * @param \Magento\Backend\Block\Template\Context $context
	 * @param array $data
	 */
	public function __construct(
		Context $context,
		SocialHelper $socialHelper,
		array $data = []
	)
	{
		$this->socialHelper = $socialHelper;
		parent::__construct($context, $data);
	}

	/**
	 * @param AbstractElement $element
	 * @return string
	 */
	protected function _getElementHtml(AbstractElement $element)
	{
		$html_id     = $element->getHtmlId();
		$redirectUrl = $this->socialHelper->getAuthUrl($this->socialType);
		$html        = '<input style="opacity:1;" readonly id="' . $html_id . '" class="input-text admin__control-text" value="' . $redirectUrl . '" onclick="this.select()" type="text">';

		return $html;
	}
}
