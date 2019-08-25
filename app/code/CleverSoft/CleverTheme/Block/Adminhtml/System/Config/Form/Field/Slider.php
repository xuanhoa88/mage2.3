<?php
/**
 * @category    CleverSoft
 * @package     CleverTheme
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author      ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverTheme\Block\Adminhtml\System\Config\Form\Field;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class Slider extends Field
{
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        return $this->_decorateRowHtml($element, "<td class='label'>".$element->getLabel()."</td><td>" . $this->renderHtml($element) . '</td><td></td>');
    }

    public function renderHtml($element) {
        $value = $element->getEscapedValue() ? $element->getEscapedValue() : 0;
        $options = array();
        if ($element->getComment()) {
            $options = explode("|",$element->getComment());
        }
        $min = isset($options[0]) ? $options[0] : 0;
        $max = isset($options[1]) ? $options[1] : 100;
        $step = isset($options[2]) ? $options[2] : 1;

        $html = '<div id="slider-'.$element->getHtmlId().'"></div>';
        $html.= '<span id="span-'.$element->getHtmlId().'">'.$value.'</span>';
        $html.= '<input id="'.$element->getHtmlId().'" type="hidden" name="'.$element->getName().'" value="'.$value.'">';
        $html .= '
            <script>
                require([
                        "jquery",
                        "jquery/ui"
                    ],function($){
                        $( function() {
                            $( "#slider-'.$element->getHtmlId().'" ).slider({
                                min: '.$min.',
                                max: '.$max.',
                                step: '.$step.',
                                value:  '.$value.',
                                create: function() {
                                    $("#span-'.$element->getHtmlId().'").text('.$value.');
                                    $("#'.$element->getHtmlId().'").val($( this ).slider( "value" ));
                                },
                                slide: function( event, ui ) {
                                    $("#span-'.$element->getHtmlId().'").text(ui.value);
                                    $("#'.$element->getHtmlId().'").val(ui.value);
                                }
                            });
                          } );
                    });
            </script>
        ';
        return $html; 
    }
}