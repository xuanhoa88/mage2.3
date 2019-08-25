<?php
/**
 * @category    CleverSoft
 * @package     CleverLayeredNavigation
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverLayeredNavigation\Controller;


use CleverSoft\CleverLayeredNavigation\Helper\Url;
use CleverSoft\CleverLayeredNavigation\Helper\UrlParser;
use Magento\Framework\Module\Manager;

class Router implements \Magento\Framework\App\RouterInterface
{
    /** @var \Magento\Framework\App\ActionFactory */
    protected $actionFactory;

    /** @var \Magento\Framework\App\ResponseInterface */
    protected $_response;

    /** @var  Url */
    protected $urlHelper;

    /** @var  \Magento\Framework\Registry */
    protected $registry;

    /** @var  UrlParser */
    protected $urlParser;

    /** @var \Magento\Framework\App\ResponseInterface */
    protected $response;
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /** @var  Manager */
    protected $moduleManager;

    public function __construct(
        \Magento\Framework\App\ActionFactory $actionFactory,
        \Magento\Framework\App\ResponseInterface $response,
        \Magento\Framework\Registry $registry,
        Url $urlHelper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        UrlParser $urlParser,
        Manager $moduleManager
    ) {
        $this->actionFactory = $actionFactory;
        $this->_response = $response;
        $this->registry = $registry;
        $this->urlHelper = $urlHelper;
        $this->urlParser = $urlParser;
        $this->response = $response;
        $this->scopeConfig = $scopeConfig;
        $this->moduleManager = $moduleManager;
    }

    public function match(\Magento\Framework\App\RequestInterface $request)
    {
        //LayeredNavigation
        $shopbyPageUrl = $this->scopeConfig->getValue('clevershopby/general/url',  \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $identifier = trim($request->getPathInfo(), '/');

        $brandUrlKeyMatched = false;
        if ($this->moduleManager->isEnabled('CleverSoft_CleverLayeredNavigation')) {
            $urlKey = $this->scopeConfig->getValue('clevershopby/brand_filter/url_key');
            $brandUrlKeyMatched = $urlKey == $identifier;
        }

        if($identifier == $shopbyPageUrl || $brandUrlKeyMatched) {
            // Forward LayeredNavigation
            if ($this->isRouteAllowed($request)) {
                $request->setModuleName('clevershopby')->setControllerName('index')->setActionName('index');
                $request->setAlias(\Magento\Framework\Url::REWRITE_REQUEST_PATH_ALIAS, $identifier);
                return $this->actionFactory->create('Magento\Framework\App\Action\Forward');
            }
        }

        if ($this->moduleManager->isEnabled('CleverSoft_CleverLayeredNavigation') && $this->scopeConfig->getValue('clevershopby/url/mode')) {
            $params = $this->urlParser->parseSeoPart($identifier);
            if ($params) {
                $this->registry->register('clevershopby_parsed_params', $params);

                // Forward to very short brand-like url
                if ($this->isRouteAllowed($request)) {
                    $request->setModuleName('clevershopby')->setControllerName('index')->setActionName('index');
                    $request->setAlias(\Magento\Framework\Url::REWRITE_REQUEST_PATH_ALIAS, $shopbyPageUrl);

                    $params = array_merge($params, $request->getParams());
                    $request->setParams($params);
                    return $this->actionFactory->create('Magento\Framework\App\Action\Forward');
                }
            }
        }


        //LayeredNavigationSEO
        if (!$this->urlHelper->isSeoUrlEnabled()) {
            return;
        }

        $identifier = trim($request->getPathInfo(), '/');
        if (!preg_match('@^(.*)/([^/]+)@', $identifier, $matches))
            return;

        $seoPart = $this->urlHelper->removeCategorySuffix($matches[2]);
        $category = ($seoPart == $matches[2]) ? $matches[1] : $this->urlHelper->addCategorySuffix($matches[1]);

        $params = $this->urlParser->parseSeoPart($seoPart);
        if ($params === false) {
            return;
        }

        $this->registry->register('clevershopby_parsed_params', $params);

        $request->setParams($params);

        $request->setPathInfo($category);

        /*
         * We have match and now we will forward action
         */
        return $this->actionFactory->create('Magento\Framework\App\Action\Forward');
    }

    protected function isRouteAllowed(\Magento\Framework\App\RequestInterface $request)
    {
        if ($this->scopeConfig->isSetFlag('clevershopby/general/enabled')) {
            return true;
        }
        $attribute_code = $this->scopeConfig->getValue('clevershopby/brand_filter/attribute_code');
        if (!$attribute_code) {
            return false;
        }

        $seoParams = $this->registry->registry('clevershopby_parsed_params');
        $seoBrandPresent = isset($seoParams) && array_key_exists($attribute_code, $seoParams);
        return $request->getParam($attribute_code) || $seoBrandPresent;
    }

}
