<?php
/**
 * @category    CleverSoft
 * @package     CleverShopByBrand
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */
namespace CleverSoft\CleverShopByBrand\Block;

class Brands extends \Magento\Framework\View\Element\Template
{
    protected $_coreRegistry = null;
    
    protected $_scopeConfig = null;

    protected $_brandCollection;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\ObjectManagerInterface $objectmanager,
        \CleverSoft\CleverShopByBrand\Model\ResourceModel\Brand\CollectionFactory $collectionFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \CleverSoft\CleverShopByBrand\Helper\Data $helper,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        $this->_scopeConfig = $context->getScopeConfig();
        $this->_objectManager = $objectmanager;
        $this->_collectionFactory = $collectionFactory;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_helper = $helper;
        parent::__construct($context, $data);
    }

    public function getAlphabetTable() {
        $alphabetString = 'a,b,c,d,e,f,g,h,i,j,k,l,m,n,o,p,q,r,s,t,u,v,w,x,y,z';
        return explode(',', $alphabetString);
    }

    public function getBrands() {
        if ($this->_brandCollection === null) {
            $this->_brandCollection = $this->_collectionFactory->create()->addFieldToFilter('is_actived',1)->setOrder('brand_label' , 'ASC');;
        }

        return $this->_brandCollection;
    }

    public function getLogo($brand, array $options = [])
    {
        return $this->_helper->getBrandImage($brand, 'logo', $options);
    }

    public function getBrandPageUrl($brand)
    {
        return $this->_helper->getBrandPageUrl($brand);
    }

    public function getProductCount($brand)
    {
        $assignAttributeCode = $this->_helper->getAssignAttributeCode();
        return count($this->_productCollectionFactory->create()->addAttributeToFilter($assignAttributeCode,$brand->getId()));
    }

    public function getBrandByProduct()
    {
        $assignAttributeCode = $this->_helper->getAssignAttributeCode();
        $productId = $this->getRequest()->getParam('id');
        $product = $this->_objectManager->create('Magento\Catalog\Model\Product')->load($productId);
        $brandId = $product->getData($assignAttributeCode);
        if ($brandId) {
            return $this->_objectManager->create('CleverSoft\CleverShopByBrand\Model\Brand')->load($brandId);
        }
        return null;
    }

    public function getPageInfo()
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
}