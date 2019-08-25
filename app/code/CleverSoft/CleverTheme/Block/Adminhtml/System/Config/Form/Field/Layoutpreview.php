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

class Layoutpreview extends Field
{

	protected function _getElementHtml(AbstractElement $element)
	{
        $aid = explode('_',$element->getHtmlId());
        $id = array_pop($aid);
		$html = parent::_getElementHtml($element);
        $html .= '<div id="layout_cleversofttheme_footer_preview_'.$id.'" class="layout-preview"></div>';
        $html .= "
            <script type='text/javascript'>
                require([
                    'CleverSoft_CleverBlock/js/widget',
                    'prototype'
                ], function(){

                    document.observe('dom:loaded', function(){
                        window.layout_{$this->getData("target")}_object = new MTLayoutPreview('layout_cleversofttheme_footer_preview_{$id}', 'cleversofttheme_footer', '{$id}');
                    });
                    setTimeout(function(){
                        window.layout_{$this->getData("target")}_object = new MTLayoutPreview('layout_cleversofttheme_footer_preview_{$id}', 'cleversofttheme_footer', '{$id}');
                    }, 100);
                });
            </script>
            <style>
            #cleversofttheme_footer_block_xl, #cleversofttheme_footer_block_lg, #cleversofttheme_footer_block_md, #cleversofttheme_footer_block_sm {display: none;}
            </style>
            ";
        return $html;
    }

}
?>