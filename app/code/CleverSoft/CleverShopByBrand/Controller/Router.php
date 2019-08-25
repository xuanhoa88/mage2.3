<?php
/**
 * @category    CleverSoft
 * @package     CleverShopByBrand
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverShopByBrand\Controller;

class Router implements \Magento\Framework\App\RouterInterface
{
    /**
     * @var \Magento\Framework\App\ActionFactory
     */
    protected $actionFactory;
 
    /**
     * Response
     *
     * @var \Magento\Framework\App\ResponseInterface
     */
    protected $_response;
 
    /**
     * @param \Magento\Framework\App\ActionFactory $actionFactory
     * @param \Magento\Framework\App\ResponseInterface $response
     */
    public function __construct(
        \Magento\Framework\App\ActionFactory $actionFactory,
        \Magento\Framework\App\ResponseInterface $response,
        \CleverSoft\CleverShopByBrand\Helper\Data $helper
    ) {
        $this->actionFactory = $actionFactory;
        $this->_response = $response;
        $this->_helper = $helper;
    }
 
    /**
     * Validate and Match
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @return bool
     */
    public function match(\Magento\Framework\App\RequestInterface $request)
    {
        if (!$this->_helper->getConfig('cleversoft_shopbybrand/all_brand_page/general')) {
            return null;
        }

        $identifier = trim($request->getPathInfo(), '/');
        if(strpos($identifier, 'brands/view/index/id') !== false) {
                return null ;
        }
		else if(strpos($identifier, 'brands/') !== false) {
			$patharr = explode("/",$identifier);
            $urlpath = end($patharr);
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
			$modelcollection = $objectManager->get('\CleverSoft\CleverShopByBrand\Model\BrandFactory')->create()->getCollection();
			$modelcollection->addFieldToFilter('url_key' , $urlpath);
			if($modelcollection->count() >=1 && $brand = $modelcollection->getFirstItem()){
				$request->setModuleName('brands')->setControllerName('view')->setActionName('index')->setParam('id',$brand->getId());
				$request->setAlias(\Magento\Framework\Url::REWRITE_REQUEST_PATH_ALIAS, $identifier);
                $request->setAlias(\Magento\Framework\UrlInterface::REWRITE_REQUEST_PATH_ALIAS, $identifier);
                $request->setPathInfo('/' . $identifier);
                return ;
			}
			else
			{
                return null;
			}
		}
		else {
            return null;
        }
    }
}