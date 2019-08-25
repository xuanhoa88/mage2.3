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

class RouterSeo implements \Magento\Framework\App\RouterInterface
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

    public function __construct(
        \Magento\Framework\App\ActionFactory $actionFactory,
        \Magento\Framework\App\ResponseInterface $response,
        \Magento\Framework\Registry $registry,
        UrlParser $urlParser,
        Url $urlHelper
    ) {
        $this->actionFactory = $actionFactory;
        $this->_response = $response;
        $this->registry = $registry;
        $this->urlHelper = $urlHelper;
        $this->urlParser = $urlParser;
    }

    public function match(\Magento\Framework\App\RequestInterface $request)
    {
        if (!$this->urlHelper->isSeoUrlEnabled()) {
            return;
        }

        $identifier = trim($request->getPathInfo(), '/');
        if (!preg_match('@^(.*)/([^/]+)@', $identifier, $matches))
        {
            return;
        }


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

}
