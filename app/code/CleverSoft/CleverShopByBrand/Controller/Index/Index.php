<?php
/**
 * @category    CleverSoft
 * @package     CleverShopByBrand
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */
 
namespace CleverSoft\CleverShopByBrand\Controller\Index;

use Magento\Framework\View\Result\PageFactory;
use \Magento\Framework\Exception\NotFoundException;

class Index extends \Magento\Framework\App\Action\Action
{
    protected $_scopeConfig;
    
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        PageFactory $resultPageFactory,
        \CleverSoft\CleverShopByBrand\Model\BrandFactory $brandFactory,
        \CleverSoft\CleverShopByBrand\Helper\Data $helper
    ) {
        parent::__construct($context);
        $this->_scopeConfig = $this->_objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface');
        $this->_storeManager = $storeManager;
        $this->_coreRegistry = $coreRegistry;
        $this->resultPageFactory = $resultPageFactory;
        $this->_brandFactory = $brandFactory;
        $this->_helper = $helper;
    }
    
    protected function _initBrandPage()
    {
        if (!$this->_coreRegistry->registry('all_brands_info')) {
            $brands = new \Magento\Framework\DataObject([
                'title'                     => $this->_scopeConfig->getValue('cleversoft_shopbybrand/all_brand_page/title'),
                'description'               => $this->_scopeConfig->getValue('cleversoft_shopbybrand/all_brand_page/description')?:'',
                'display_featured_brands'   => $this->_scopeConfig->getValue('cleversoft_shopbybrand/all_brand_page/display_featured_brands'),
                'display_brand_search'      => $this->_scopeConfig->getValue('cleversoft_shopbybrand/all_brand_page/display_brand_search'),
                'meta_title'                => $this->_scopeConfig->getValue('cleversoft_shopbybrand/all_brand_page/meta_title'),
                'meta_keywords'             => $this->_scopeConfig->getValue('cleversoft_shopbybrand/all_brand_page/meta_keywords'),
                'meta_description'          => $this->_scopeConfig->getValue('cleversoft_shopbybrand/all_brand_page/meta_description')
            ]);
            $this->_coreRegistry->register('all_brands_info', $brands);
        }
        return $this->_coreRegistry->registry('all_brands_info');
    }
    
    public function execute()
    {
        if (!$this->_helper->getConfig('cleversoft_shopbybrand/all_brand_page/general')) {
            throw new NotFoundException(__('Page not found!'));
        }
        
        $page = $this->resultPageFactory->create();
        $brand = $this->_initBrandPage();
        $pageConfig = $page->getConfig();
        
        $title = $brand->getData('title');
        
        if ($title) {
            $pageConfig->getTitle()->set($title);
            $pageMainTitle = $page->getLayout()->getBlock('page.main.title');
            if ($pageMainTitle) {
                $pageMainTitle->setPageTitle($title);
            }
        }
        $pageConfig->setKeywords($brand->getData('meta_keywords'));
        $pageConfig->setDescription($brand->getData('meta_description'));
        return $page;
    }
    
}