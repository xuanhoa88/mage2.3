<?php
/**
 * @category    CleverSoft
 * @package     CleverShopByBrand
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverShopByBrand\Model;

class Widget extends \Magento\Widget\Model\Widget
{
    public function getWidgetDeclaration($type, $params = [], $asIs = true)
    {
        $directive = '{{widget type="' . $type . '"';
        $navigation_prev = '';
        $navigation_next = '';
        foreach ($params as $name => $value) {
            if($name != 'navigation_prev' && $name != 'navigation_next') {
                // Retrieve default option value if pre-configured
                if ($name == 'conditions') {
                    $name = 'conditions_encoded';
                    $value = $this->conditionsHelper->encode($value);
                } elseif (is_array($value)) {
                    $value = implode(',', $value);
                } elseif (trim($value) == '') {
                    $widget = $this->getConfigAsObject($type);
                    $parameters = $widget->getParameters();
                    if (isset($parameters[$name]) && is_object($parameters[$name])) {
                        $value = $parameters[$name]->getValue();
                    }
                }
                if (isset($value)) {
                    $directive .= sprintf(' %s="%s"', $name, $this->escaper->escapeQuote($value));
                }
            }
            if ($name == 'navigation_prev') {
                $navigation_prev = ' navigation_prev="'.$value.'"';
            }
            if ($name == 'navigation_next') {
                $navigation_next = ' navigation_next="'.$value.'"';
            }
        }

        $directive .= $this->getWidgetPageVarName($params);
        $directive .= $navigation_prev;
        $directive .= $navigation_next;
        $directive .= '}}';

        if ($asIs) {
            return $directive;
        }

        $html = sprintf(
            '<img id="%s" src="%s" title="%s">',
            $this->idEncode($directive),
            $this->getPlaceholderImageUrl($type),
            $this->escaper->escapeUrl($directive)
        );
        return $html;
    }

    /**
     * @param array $params
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getWidgetPageVarName($params = [])
    {
        $pageVarName = '';
        if (array_key_exists('show_pager', $params) && (bool)$params['show_pager']) {
            $pageVarName = sprintf(
                ' %s="%s"',
                'page_var_name',
                'p' . $this->getMathRandom()->getRandomString(5, \Magento\Framework\Math\Random::CHARS_LOWERS)
            );
        }
        return $pageVarName;
    }
}
