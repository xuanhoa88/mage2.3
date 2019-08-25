<?php
/**
 * @category    CleverSoft
 * @package     CleverLayeredNavigation
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverLayeredNavigation\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Module\Manager;

class Url extends AbstractHelper
{
    /** @var  Data */
    protected $helper;

    /** @var  Manager */
    protected $moduleManager;

    protected $brandAttributeCode;
    protected $isBrandFilterActive;

    public function __construct(
        Context $context,
        Dataseo $helper
    )
    {
        parent::__construct($context);
        $this->helper = $helper;
        $this->moduleManager = $context->getModuleManager();

        $this->brandAttributeCode = $this->moduleManager->isEnabled('CleverSoft_CleverLayeredNavigation')
        && $this->scopeConfig->getValue('clevershopby/brand_filter/attribute_code')
            ? $this->scopeConfig->getValue('clevershopby/brand_filter/attribute_code') : null;
    }

    public function seofyUrl($url)
    {
        $key = $this->scopeConfig->getValue('clevershopby/general/url');
        $url = str_replace('clevershopby/index/index/', $key, $url);

        if (!preg_match('@^([^/]*//[^/]*/)(.*)$@', $url, $globalParts)) {
            return $url;
        }

        $delimiter = strpos($url, '&amp;') === false ? '&' : '&amp;';

        $nativeParts = explode('?', $globalParts[2], 2);

        $routeUrl = $this->removeCategorySuffix($nativeParts[0]);
        $appendSuffix = $routeUrl != $nativeParts[0];
        $endsWithLine = strlen($routeUrl) && $routeUrl[strlen($routeUrl) - 1] == '/';
        if ($endsWithLine) {
            return $url;
        }
        $resultPath = $routeUrl;

        $query = [];
        $hashPart = '';
        if (isset($nativeParts[1])) {
            $paramPart = $nativeParts[1];
            $hashPosition = strpos($paramPart, '#');
            if ($hashPosition !== false) {
                $hashPart = substr($paramPart, $hashPosition);
                $paramPart = substr($paramPart, 0, $hashPosition);
            }
            if (strlen($paramPart)) {
                $query = explode($delimiter, $paramPart);
                $seoAliases = $this->query2Aliases($query);
                if ($seoAliases) {
                    $resultPath = $this->injectAliases($resultPath, $seoAliases);
                }
            }
        }

        $resultPath = $this->cutReplaceExtraLayeredNavigation($resultPath);
        $resultPath = ltrim($resultPath, '/');

        if ($appendSuffix) {
            $resultPath = $this->addCategorySuffix($resultPath);
        }

        $result = $query ? ($resultPath . '?' . implode($delimiter, $query)) : $resultPath;
        $result .= $hashPart;
        return $globalParts[1] . $result;
    }

    protected function query2Aliases(array &$query)
    {
        $this->isBrandFilterActive = false;
        $optionsData = $this->helper->getOptionsSeoData();

        $seoAliases = [];
        foreach ($query as $key => $queryArgument) {
            $argumentParts = explode('=', $queryArgument, 2);
            if (count($argumentParts) == 2) {
                $paramName = $argumentParts[0];
                if (isset($this->brandAttributeCode) && $this->brandAttributeCode == $paramName) {
                    $this->isBrandFilterActive = true;
                }
                if ($this->isParamSeoSignificant($paramName)) {
                    $values = explode(',', str_replace('%2C', ',', $argumentParts[1]));
                    foreach ($values as $value) {
                        if (!array_key_exists($value, $optionsData)) {
                            continue;
                        }
                        $alias = $optionsData[$value]['alias'];
                        unset($query[$key]);
                        $seoAliases[] = $alias;
                    }
                }
            }
        }

        return $seoAliases;
    }

    protected function isParamSeoSignificant($param)
    {
        $seoList = $this->helper->getSeoSignificantUrlParameters();
        return in_array($param, $seoList);
    }

    protected function injectAliases($routeUrl, array $aliases)
    {
        $result = $routeUrl;
        if ($aliases) {
            $result .= '/' . implode('-', $aliases);
        }

        return $result;
    }

    protected function cutReplaceExtraLayeredNavigation($url)
    {
        $cut = false;
        $allProductsEnabled = $this->moduleManager->isEnabled('CleverSoft_CleverLayeredNavigation') && $this->scopeConfig->isSetFlag('clevershopby/general/enabled');
        if ($allProductsEnabled || $this->moduleManager->isEnabled('CleverSoft_CleverLayeredNavigation'))
        {
            $key = $this->scopeConfig->getValue('clevershopby/general/url');
            $l = strlen($key);
            if (substr($url, 0, $l) == $key && strlen($url) > $l && $url[$l] != '?' && $url[$l] != '#') {
                $url = substr($url, strlen($key));
                $cut = true;
            }
        }

        if ($cut) {
            if ($this->isBrandFilterActive) {
                $key = trim($this->scopeConfig->getValue('clevershopby/brand_filter/url_key'));
                $url = $key . $url;
            }
        }
        return $url;
    }

    public function addCategorySuffix($url)
    {
        $suffix = $this->scopeConfig->getValue('catalog/seo/category_url_suffix');
        if (strlen($suffix)) {
            $url .= $suffix;
        }
        return $url;
    }

    public function removeCategorySuffix($url)
    {
        $suffix = $this->scopeConfig->getValue('catalog/seo/category_url_suffix');
        if (strlen($suffix)) {
            $p = strrpos($url, $suffix);
            if ($p !== false && $p == strlen($url) - strlen($suffix)) {
                $url = substr($url, 0, $p);
            }
        }
        return $url;
    }

    public function isSeoUrlEnabled()
    {
        return !!$this->scopeConfig->getValue('clevershopby/url/mode');
    }
}
