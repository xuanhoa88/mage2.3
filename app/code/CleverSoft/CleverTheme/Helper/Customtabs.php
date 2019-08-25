<?php
/**
 * @category    CleverSoft
 * @package     CleverTheme
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */
 
namespace Smartwave\Porto\Helper;

class Customtabs extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $_filterProvider;
    protected $_storeManager;
    protected $_blockFactory;
    
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Cms\Model\Template\FilterProvider $filterProvider,
        \Magento\Cms\Model\BlockFactory $blockFactory
    ) {

        $this->_filterProvider = $filterProvider;
        $this->_blockFactory = $blockFactory;
        $this->_storeManager = $storeManager;
        
        parent::__construct($context);
    }
    
    public function getConfig($config_path)
    {
        return $this->scopeConfig->getValue(
            $config_path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    
    public function subval_sort($a,$subkey) {
        foreach($a as $k=>$v) {
            $b[$k] = strtolower($v[$subkey]);
        }
        asort($b);
        foreach($b as $key=>$val) {
            $c[] = $a[$key];
        }
        return $c;
    }
    public function checkShowingTab($tab_cat_ids, $parent_cat_ids, $tab_prod_skus, $prod_sku) {
		if(!$tab_cat_ids && !$tab_prod_skus)
            return true;
        $tab_cat_ids = explode(",",$tab_cat_ids);
        $tab_prod_skus = explode(",",$tab_prod_skus);
        if(count($tab_prod_skus)>0 && count($tab_cat_ids)>0){
            if(in_array($prod_sku, $tab_prod_skus) || count(array_intersect($tab_cat_ids, $parent_cat_ids))>0)
                return true;
        }
        if(count($tab_prod_skus)>0 && in_array($prod_sku, $tab_prod_skus))
            return true;
        if(count($tab_cat_ids)>0 && count(array_intersect($tab_cat_ids, $parent_cat_ids))>0)
            return true;
        
        return false;
    }

    public function getBlockContent($content = '') {
        if(!$this->_filterProvider)
            return $content;
        return $this->_filterProvider->getBlockFilter()->filter(trim($content));
    }
}
