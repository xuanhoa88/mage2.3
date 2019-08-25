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

class Color extends Field
{
	protected function _getElementHtml(AbstractElement $element){
		$colorpicker = $this->getViewFileUrl('CleverSoft_CleverTheme::js/mcolorpicker/');
		$html = parent::_getElementHtml($element);
		$html .= '
       		<script>
				require([
						"CleverSoft_CleverTheme/js/mcolorpicker/mcolorpicker"
                    ],function(){
                    	jQuery(document).ready(function(){
							jQuery("#'. $element->getHtmlId() .'").attr("data-hex", true).width("250px").mColorPicker({imageFolder :"'.$colorpicker .'/images/"});
                    	});

                    });
			</script>
       	';
        return $html;
    }

}
?>