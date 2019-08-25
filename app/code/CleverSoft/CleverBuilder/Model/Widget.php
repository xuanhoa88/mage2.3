<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CleverSoft\CleverBuilder\Model;

/**
 * Widget model for different purposes
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Widget extends \Magento\Widget\Model\Widget
{
    /**
     * @var \Magento\Widget\Model\Config\Data
     */
    protected $dataStorage;

    /**
     * @var \Magento\Framework\App\Cache\Type\Config
     */
    protected $configCacheType;

    /**
     * @var \Magento\Framework\View\Asset\Repository
     */
    protected $assetRepo;

    /**
     * @var \Magento\Framework\View\Asset\Source
     */
    protected $assetSource;

    /**
     * @var \Magento\Framework\View\FileSystem
     */
    protected $viewFileSystem;

    /**
     * @var \Magento\Framework\Escaper
     */
    protected $escaper;

    /**
     * @var array
     */
    protected $widgetsArray = [];

    /**
     * @var \Magento\Widget\Helper\Conditions
     */
    protected $conditionsHelper;

    /**
     * @var \Magento\Framework\Math\Random
     */
    private $mathRandom;

    public function getWidgetDeclaration($type, $params = [], $asIs = true)
    {
        $directive = '[cb-row][cb-column width="12/12"][cb-column-text width="12/12"]{{widget type="' . $type . '"';

        foreach ($params as $name => $value) {
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
            if ($value) {
                $directive .= sprintf(' %s="%s"', $name, $value);
            }
        }

        $directive .= $this->getWidgetPageVarName($params);

        $directive .= '}}[cb-column-text][/cb-column][/cb-row]';

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

    /**
     * @return \Magento\Framework\Math\Random
     *
     * @deprecated
     */
    private function getMathRandom()
    {
        if ($this->mathRandom === null) {
            $this->mathRandom = \Magento\Framework\App\ObjectManager::getInstance()
                ->get('\Magento\Framework\Math\Random');
        }
        return $this->mathRandom;
    }
}
