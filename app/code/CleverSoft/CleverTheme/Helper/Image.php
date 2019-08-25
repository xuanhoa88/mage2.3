<?php
/**
 * @category    CleverSoft
 * @package     CleverTheme
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author      ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverTheme\Helper;

class Image extends \Magento\Framework\App\Helper\AbstractHelper{

    protected $_catalogImageHelper;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Catalog\Helper\Image $imageHelper
    ){
        parent::__construct($context);
        $this->_catalogImageHelper = $imageHelper;
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

    public function getImg($product, $w, $h, $imgVersion='image', $file=NULL)
    {
        if ($h <= 0)
        {

            return $url = $this->_catalogImageHelper
                ->init($product, $imgVersion)
                ->setImageFile($file)
                ->constrainOnly(false)
                ->keepAspectRatio(true)
                ->keepFrame(false)
                ->resize($w)
                ->getUrl();
        }
        else
        {
            return $url = $this->_catalogImageHelper
                ->init($product, $imgVersion)
                ->setImageFile($file)
                ->resize($w, $h)
                ->getUrl();
        }
    }

}
