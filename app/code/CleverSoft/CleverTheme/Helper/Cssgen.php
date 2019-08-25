<?php
/**
 * @category    CleverSoft
 * @package     CleverTheme
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author      ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverTheme\Helper;

use Magento\Framework\App\Filesystem\DirectoryList;

class Cssgen extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Path and directory of the automatically generated CSS
     *
     * @var string
     */
    protected $_generatedCssFolder;
    protected $_generatedCssDir;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var Filesystem
     */
    protected $_fileSystem;
    protected $_resolver;
    protected $_designInterface;


    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Filesystem $fileSystem,
        \Magento\Framework\Locale\Resolver $resolver,
        \Magento\Framework\View\DesignInterface $designInterface,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    )
    {
        $this->_storeManager = $storeManager;
        $this->_fileSystem = $fileSystem;
        $this->_resolver = $resolver;
        $this->_designInterface = $designInterface;

        //Create paths
        $this->_generatedCssFolder = 'css/_config/';
        $this->_generatedCssDir = $this->_fileSystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath('cleversoft/web') . '/' . $this->_generatedCssFolder;

        parent::__construct($context);
    }

    /**
     * Get automatically generated CSS directory
     *
     * @return string
     */
    public function getGeneratedCssDir()
    {
        return $this->_generatedCssDir;
    }

    /*
    * get static css Folder
    */
    public function getStaticCssPath() {
        $meidaUrl = $this->_storeManager->getStore()->getBaseUrl(
            \Magento\Framework\UrlInterface::URL_TYPE_STATIC
        );
        $theme_path = $this->_designInterface->getDesignTheme()->getFullPath();
        $locate =  $this->_resolver->getLocale();
        return $meidaUrl.$theme_path.'/'.$locate.'/css/';
    }

    /**
     * Get file path: CSS design
     *
     * @return string
     */
    public function getDesignFile()
    {

        $meidaUrl = $this->_storeManager->getStore()->getBaseUrl(
            \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
        );
        $url = $meidaUrl . 'cleversoft/web/css/_config/design_' . $this->_storeManager->getStore()->getCode() . '.css';

        return $this->removeProtocol($url);
    }

    /**
     * Get file path: CSS layout
     *
     * @return string
     */
    public function getLayoutFile()
    {
        $meidaUrl = $this->_storeManager->getStore()->getBaseUrl(
            \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
        );
        $url =  $meidaUrl . 'cleversoft/web/css/_config/layout_' . $this->_storeManager->getStore()->getCode() . '.css';

        return $this->removeProtocol($url);
    }

    protected function removeProtocol($url){
        $remove = array("http:","https:");
        return str_replace($remove,"",$url);
    }
}
