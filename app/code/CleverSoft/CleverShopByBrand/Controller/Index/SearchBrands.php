<?php
/**
 * @category    CleverSoft
 * @package     CleverShopByBrand
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */
 
namespace CleverSoft\CleverShopByBrand\Controller\Index;

use Magento\Framework\View\Result\LayoutFactory;

class SearchBrands extends \Magento\Framework\App\Action\Action
{
    
    protected $_brandObject;
    
    protected $_context;
    
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        LayoutFactory $resultLayoutFactory,
        \CleverSoft\CleverShopByBrand\Model\BrandFactory $brandFactory,
        \CleverSoft\CleverShopByBrand\Helper\Data $helper
    ) {
        parent::__construct($context);
        $this->_urlManager = $context->getUrl();
        $this->_storeManager = $storeManager;
        $this->_coreRegistry = $coreRegistry;
        $this->resultLayoutFactory = $resultLayoutFactory;
        $this->_brandFactory = $brandFactory;
        $this->_mediaUrl = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        $this->_imageHelper = $this->_objectManager->get('CleverSoft\CleverShopByBrand\Helper\Image');
        $this->_helper = $helper;
    }
    
    public function getUrl($urlKey, $params = null)
    {
        return $this->_urlManager->getUrl($urlKey, $params);
    }
    
    public function getAllBrandsArray($query = false)
    {
        if (!$this->_brandObject) {
            $this->_brandObject = $this->_brandFactory->create()->getCollection();

            if ($query) {
                $this->_brandObject = $this->_brandObject->addFieldToFilter('brand_label', array('like' => '%' . $query. '%'));
            }
		}
		return $this->_brandObject;
    }
    
    public function execute()
    {
        $brandLabels = [];
        $query = $this->getRequest()->getParam('term', false);
        $brandData = $this->getAllBrandsArray($query);
        if (count($brandData)) {
            foreach ($brandData as $brand) {
                $brandLabels[] = [
                    'label' => $brand->getData('brand_label'),
                    'value' => $brand->getData('brand_label'),
                    'url'   => $this->_helper->getBrandPageUrl($brand),
                    'img'   => $this->getThumbnailImage($brand, ['width' => 50, 'height' => 50])
                ];
            }
        }
        echo json_encode($brandLabels); die();
    }
    
    public function getThumbnailImage($brand, array $options = []) {
		if (!($brandThumb = $brand->getLogo())) {
			$brandThumb = 'cleversoft/brand/placeholder_thumbnail.jpg';
        }
        if (isset($options['width']) || isset($options['height'])) {
            if(!isset($options['width']))
                $options['width'] = null;
            if(!isset($options['height']))
                $options['height'] = null;
            return $this->_imageHelper->init($brandThumb)->resize($options['width'],$options['height'])->__toString();
        } else {
            return $this->_mediaUrl.$brandThumb;
        }
	}
    
}