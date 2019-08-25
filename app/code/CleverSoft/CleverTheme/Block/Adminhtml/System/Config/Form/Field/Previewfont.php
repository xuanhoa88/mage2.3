<?php
/**
 * @category    CleverSoft
 * @package     CleverTheme
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverTheme\Block\Adminhtml\System\Config\Form\Field;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class Previewfont extends Field
{
	protected function _getElementHtml(AbstractElement $element)
	{
		$html = parent::_getElementHtml($element);
       	$html .= '<br/><div id="font_'.$element->getHtmlId().'" class="font_preview" style="font-size: 13px; padding: 10px; 0">The quick brown fox jumps over the lazy dog</div>';
       	$html .= '
       			 <script>
                    require([
                        "jquery"
                    ],function(){
                        jQuery(document).ready(function(){
							var font = jQuery("#'.$element->getHtmlId().'").val();
							changeFont'.$element->getHtmlId().'(font);
							jQuery("#'.$element->getHtmlId().'").bind("change", function() {
								value = jQuery("#'.$element->getHtmlId().'").val();
								changeFont'.$element->getHtmlId().'(value);
							});
							function changeFont'.$element->getHtmlId().'(val){
								var link = jQuery("<link>", {
									type: "text/css",
									rel: "stylesheet",
									href: "//fonts.googleapis.com/css?family=" + val,
								}).appendTo("head");
								jQuery("#font_'.$element->getHtmlId().'").css("font-family", val);
							}
    					});
                    });
                </script>
       			';
        return $html;
    }
}
?>