<?php
/**
 * @category    CleverSoft
 * @package     CleverTheme
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author      ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverTheme\Helper;
use Magento\Customer\Model\Session as CustomerSession;
class Data extends \Magento\Framework\App\Helper\AbstractHelper{
    /**
     * Resource instance
     *
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $_resource;
    /**
     * @var CustomerSession
     */
    protected $_customerSession;
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;
    /**
     * Catalog product visibility
     *
     * @var \Magento\Catalog\Model\Product\Visibility
     */
    protected $catalogProductVisibility;
    /**
     * Product collection factory
     *
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $productCollectionFactory;
    /**
     * Initialize dependencies
     *
     * @var \Magento\Catalog\Model\Config
     */
    protected $_catalogConfig;
    /**
     * Initialize dependencies
     *
     * @var \Magento\Catalog\Model\Config
     */
    protected $_coreSession;
    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $_jsonEncoder;
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;
    /**
     * Product collection factory
     *
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $viewedModel;
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_localeDate;
    /**
     * @var \CleverSoft\CleverTheme\Helper\Image
     */
    protected $_imageHelper;
    /**
     * @var \Magento\Framework\View\ConfigInterface
     */
    protected $_viewConfig;

    protected $_priceCurrency;

    protected $_stockRegistry;

    protected $_stockState;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility,
        \Magento\Catalog\Model\Config $catalogConfig,
        \Magento\Reports\Model\Product\Index\Viewed $viewedModel,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Registry $registry,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \CleverSoft\CleverTheme\Helper\Image $imageHelper,
        \Magento\Framework\View\ConfigInterface $viewConfig,
        \Magento\Framework\Session\SessionManager $coreSession,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\CatalogInventory\Api\StockStateInterface $stockState,
        CustomerSession $customerSession
    ) {
        $this->_resource = $resource;
        $this->_customerSession = $customerSession;
        $this->_coreSession = $coreSession;
        $this->storeManager = $storeManager;
        $this->_coreRegistry = $registry;
        $this->_checkoutSession = $checkoutSession;
        $this->catalogProductVisibility = $catalogProductVisibility;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->_catalogConfig = $catalogConfig;
        $this->_objectManager = $objectManager;
        $this->_localeDate = $localeDate;
        $this->viewedModel = $viewedModel;
        $this->_imageHelper = $imageHelper;
        $this->_viewConfig = $viewConfig;
        $this->_priceCurrency = $priceCurrency;
        $this->_stockRegistry = $stockRegistry;
        $this->_stockState = $stockState;
        parent::__construct($context);
    }

    public function getConfig($optionString, $scopeCode = null)
    {
        return $this->scopeConfig->getValue($optionString, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $scopeCode);
    }

    /**
     * Get theme's main settings (single option)
     *
     * @return string
     */
    public function getCfg($optionString, $scopeCode = null)
    {
        return $this->scopeConfig->getValue('cleversofttheme/' . $optionString, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $scopeCode);
    }
    /**
     * Get theme's main settings design (single option)
     *
     * @return array
     */
    public function getCfgSectionDesign($storeId)
    {
        if ($storeId)
            return $this->scopeConfig->getValue('cleversofttheme_design', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
        else
            return $this->scopeConfig->getValue('cleversofttheme_design',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
    /**
     * Get theme's design settings (single option)
     *
     * @return string
     */

    public function getThemeDesignCfg($optionString, $scopeCode = NULL)
    {
        return $this->scopeConfig->getValue('cleversofttheme_design/' . $optionString, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $scopeCode);
    }
    /**
     * Get theme's layout settings (single option)
     *
     * @return string
     */
    public function getThemeLayoutCfg($optionString, $scopeCode = NULL)
    {
        return $this->scopeConfig->getValue('cleversofttheme/' . $optionString, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $scopeCode);
    }
    /**
     * Get config show label for product
     *
     * @return html label
     */
    public function getLabel(\Magento\Catalog\Model\Product $product)
    {
        if ( !$product instanceof  \Magento\Catalog\Model\Product )
            return ;
        $html = '';
        if (!$this->getCfg("product_labels/new") &&
            !$this->getCfg("product_labels/sale")) {
            return $html;
        }
        if ( $this->getCfg("product_labels/new") && $this->_checkNew($product) ) {
            $html .= '<div class="product-new-label">'.$this->getCfg("product_labels/new_label_text").'</div>';
        }
        if ( $this->getCfg("product_labels/sale") && $this->_checkSale($product) ) {
            if($this->getCfg("product_labels/sale_label_percent")) {
                if($product->getPrice() > 0) {
                    $percent = round(100 - $product->getSpecialPrice()/$product->getPrice()*100,0);
                    $html .= '<div class="product-sale-label">-'.$percent.'%</div>';
                }
            } else {
                $html .= '<div class="product-sale-label">'.$this->getCfg("product_labels/sale_label_text").'</div>';
            }
        }
        if ( $this->getCfg("product_labels/low") && $this->_getLowStock($product) ) {
            $html .= '<div class="product-low-of-stock">'.__('Low Stock').'</div>';
        }
        return $html;

    }
    protected  function _getLowStock($product)
    {
        $lowStockThreshold = $this->getCfg("product_labels/low_threshold");
        if($product->getTypeId() == 'configurable'){
            $products = $product->getTypeInstance()->getUsedProductIds($product);
            $num = 0;
            foreach ($products as $item) {
                $stockItem = $this->_stockRegistry->getStockItem($item,$this->storeManager->getStore()->getWebsiteId());
                $num += $stockItem->getQty();
            }
            if($num < $lowStockThreshold) return true;
            else return false;
        } else if($product->getTypeId() == 'bundle') {
            $num = 0;
            $bpo = $product->getExtensionAttributes()->getBundleProductOptions();
            foreach ($bpo as $b) {
                $pls = $b->getProductLinks();
                foreach ($pls as $pl) {
                    $num += (int)$pl->getQty();
                }
            }
            if($num < $lowStockThreshold) return true;
            else return false;
        } else {
            $num = $this->_stockState->getStockQty($product->getId(), $product->getStore()->getWebsiteId());
            if($num < $lowStockThreshold) return true;
            else return false;
        }
    }
    /**
     * Get date from new product
     *
     * @return from date and to date
     */
    protected function _checkNew($product)
    {
        $from = strtotime($product->getData('news_from_date'));
        $to = strtotime($product->getData('news_to_date'));
        return $this->_checkDate($from, $to);
    }
    /**
     * Get date from sale product
     *
     * @return from date and to date
     */
    protected function _checkSale($product)
    {
        $from = strtotime($product->getData('special_from_date'));
        $to = strtotime($product->getData('special_to_date'));
        return $this->_checkDate($from, $to);
    }
    /**
     * Check date time locale
     *
     * @return true or false
     */
    protected function _checkDate($from, $to)
    {
        $today = strtotime(
            $this->_localeDate->date()->setTime(0, 0, 0)->format('Y-m-d H:i:s')
        );
        if ($from && $today < $from) {
            return false;
        }
        if ($to && $today > $to) {
            return false;
        }
        if (!$to && !$from) {
            return false;
        }
        return true;
    }
    /**
     * Get type for product
     *
     * @return true or false
     */
    public function getType(\Magento\Catalog\Model\Product $product)
    {
        if ( 'Product' != get_class($product) )
            return;
        foreach ($product->getOptionsType() as $o) {
            $optionType = $o['type'];
            if ($optionType == 'file') {
                return true;
            }
        }
        return false;
    }
    public function formatCurrency($amount)
    {
        return $this->_priceCurrency->format($amount, false, \Magento\Framework\Pricing\PriceCurrencyInterface::DEFAULT_PRECISION);
    }
    /**
     * Get alternative image HTML of the given product
     *
     * @param Product   $product        Product
     * @param int                           $w              Image width
     * @param int                           $h              Image height
     * @param string                        $imgVersion     Image version: image, small_image, thumbnail
     * @return string
     */
    public function getAltImgHtml($product, $w, $h, $imgVersion='small_image')
    {
        $url = $this->getAltImgUrl($product, $w, $h, $imgVersion='small_image');

        if(!empty($url))
            $html =
                '<img class="img-responsive alt-img product-image-photo" src="' . $url . '" alt="' . $product->getName() . '"/>';
        else
            $html = '';
        return $html;
    }
    public function getAltImgUrl($product, $w, $h, $imgVersion='small_image')
    {
        $product->load('media_gallery_images');
        $images = $product->getMediaGalleryImages();
        $column = $this->getCfg('category/alt_image_column');
        $value = $this->getCfg('category/alt_image_column_value');
        $galleryImages = [];
        foreach ($images as $image) {
            /* @var \Magento\Framework\DataObject $image */
            $galleryImages[] = $this->_imageHelper->getImg($product, $w, $h, $imgVersion, $image->getFile());
        }
        $value = $this->getCfg('category/alt_image_column_value');
        if(isset($galleryImages[($value-1)]))
            $url = $galleryImages[($value-1)];
        else
            $url = '';
        return $url;
    }

    public function getPreviousProduct()
    {
        $currentProduct = $this->_coreRegistry->registry('current_product');
        if (!$currentProduct) {
            return false;
        }
        $prodId = $currentProduct->getId();
        //$positions = $this->_coreSession->getPrevNextProductCollection();
        //if (!$positions) {
        $currentCategory = $this->_coreRegistry->registry('current_category');
        if (!$currentCategory) {
            $categoryIds = $currentProduct->getCategoryIds();
            $categoryId = current($categoryIds);
            $categoryModel = $this->_objectManager->create('Magento\Catalog\Model\Category');
            $currentCategory = $categoryModel->load($categoryId);
            $this->_coreRegistry->register('current_category', $currentCategory);
        }
        $positions = array_reverse(array_keys($this->_coreRegistry->registry('current_category')->getProductsPosition()));
        //}
        $cpk = @array_search($prodId, $positions);
        $slice = array_reverse(array_slice($positions, 0, $cpk));
        foreach ($slice as $productId) {
        	$modelProduct = $this->_objectManager->create('Magento\Catalog\Model\Product');
            $product = $modelProduct->load($productId);
            if ($product && $product->getId() && $product->isVisibleInCatalog() && $product->isVisibleInSiteVisibility()) {
                return $product;
            }
        }
        return false;
    }
    public function getNextProduct()
    {
        $currentProduct = $this->_coreRegistry->registry('current_product');
        if (!$currentProduct) {
            return false;
        }
        $prodId = $currentProduct->getId();
        //$positions = $this->_coreSession->getPrevNextProductCollection();
        //if (!$positions) {
        $currentCategory = $this->_coreRegistry->registry('current_category');
        if (!$currentCategory) {
            $categoryIds = $currentProduct->getCategoryIds();
            $categoryId = current($categoryIds);
            $categoryModel = $this->_objectManager->create('Magento\Catalog\Model\Category');
            $currentCategory = $categoryModel->load($categoryId);
            $this->_coreRegistry->register('current_category', $currentCategory);
        }
        $positions = array_reverse(array_keys($this->_coreRegistry->registry('current_category')->getProductsPosition()));
        //}
        $cpk = @array_search($prodId, $positions);
        $slice = array_slice($positions, $cpk + 1, count($positions));
        foreach ($slice as $productId) {
        	$modelProduct = $this->_objectManager->create('Magento\Catalog\Model\Product');
            $product = $modelProduct->load($productId);
            if ($product && $product->getId() && $product->isVisibleInCatalog() && $product->isVisibleInSiteVisibility()) {
                return $product;
            }
        }
        return false;
    }

    public function getMediaUrl($addPath = ''){
        return $this->_urlBuilder
            ->getBaseUrl(['_type' => \Magento\Framework\UrlInterface::URL_TYPE_MEDIA]).$addPath;
    }

    public function getCartData() {
        $quoteId = $this->_objectManager->create('Magento\Checkout\Model\Session')->getQuoteId();
        if ($quoteId) {
            $cartData = $this->_objectManager->get('Magento\Quote\Model\QuoteRepository')->get($quoteId);
            return $cartData;
        }
        return null;
    }

    public function getSubtotal() {
        if ($this->getCartData()) {
            return $this->formatCurrency($this->getCartData()->getSubtotal());
        }
        return $this->formatCurrency(0);
    }

    public function getCountItem() {
        if ($this->getCartData()) {
            return count($this->getCartData()->getAllVisibleItems());
        }
        return null;
    }
}