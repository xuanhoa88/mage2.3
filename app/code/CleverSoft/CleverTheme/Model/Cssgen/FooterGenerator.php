<?php
/**
 * @category    CleverSoft
 * @package     CleverTheme
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverTheme\Model\Cssgen;

use Magento\Framework\Filesystem\DriverInterface;
use CleverSoft\CleverTheme\Model\lessc;

class FooterGenerator extends \Magento\Framework\Model\AbstractModel{

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Catalog data
     *
     * @var Data
     */
    protected $_helperFooterCssgen = null;

    /**
     * @var \Magento\Framework\View\Layout
     */
    protected $layout;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;
    protected $_scopeConfig;
    protected $_themeProvider;
    protected $_directoryList;
    protected $_design;
    protected $resolver;
    protected $_file;

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \CleverSoft\CleverTheme\Helper\FooterCssgen $helperFooterCssgen,
        \Magento\Framework\Filesystem\Driver\File $file,
        \Magento\Framework\View\Layout $layout,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfigInterface,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \CleverSoft\CleverTheme\Model\Config\Backend\Resolver $resolver,
        \Magento\Framework\View\DesignInterface $design,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Magento\Framework\View\Design\Theme\ThemeProviderInterface $themeProviderInterface,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ){
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->_storeManager = $storeManager;
        $this->_helperFooterCssgen = $helperFooterCssgen;
        $this->resolver = $resolver;
        $this->_design = $design;

        $this->_scopeConfig = $scopeConfigInterface;
        $this->_directoryList = $directoryList;
        $this->_themeProvider = $themeProviderInterface;
        $this->layout = $layout;
        $this->messageManager = $messageManager;
        $this->_file = $file;
    }

    protected function getThemeData($storeID,$template){
        $themeId = $this->_scopeConfig->getValue(
            \Magento\Framework\View\DesignInterface::XML_PATH_THEME_ID,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeID
        );
        $templateMap = $this->resolver->getTemplateMap();
        $params = array('module'=>'','area'=>'frontend');
        $key = $template . '_' . serialize($params);
        if(isset($templateMap[$key])) {
            unset($templateMap[$key]);
            $this->resolver->setTemplateMap($templateMap);
        }
        $theme = $this->_themeProvider->getThemeById($themeId);
        $this->_design->setDesignTheme($theme->getData('theme_path'),'frontend');
        return $theme->getData();
    }

    public function generateCss($storeCode)
    {
        if (!$this->_storeManager->getStore($storeCode)->getIsActive()) return;
        $prefix = '_' . $storeCode;
        $design = "clever_footerbuilder_style";
        $filename = $design . $prefix . '.less';

        $path = 'CleverSoft_CleverTheme::css/' . $design . '.phtml';
        try{
            $themeData = $this->getThemeData($this->_storeManager->getStore($storeCode)->getId(),$path);
        } catch (\Exception $e) {
            echo 'Caught exception: ',  $e->getMessage(), "\n";
            exit;
        }

        $filedefault = $this->_helperFooterCssgen->getGeneratedCssDir() . $filename;

        try{

            $block = $this->layout->createBlock('\Magento\Framework\View\Element\Template')->setData(array('area' => 'frontend', 'cssgen_store' => $storeCode))->setTemplate($path)->toHtml();
            if (empty($block)) {
                throw new \Exception( __("Template file is empty or doesn't exist: %s", $path) );
            }
            if(!$this->_file->isExists($this->_helperFooterCssgen->getGeneratedCssDir())){
                $this->_file->createDirectory($this->_helperFooterCssgen->getGeneratedCssDir(),DriverInterface::WRITEABLE_DIRECTORY_MODE);
            }
            $resourceFile = $this->_file->fileOpen($filedefault, 'w+');
            $this->_file->fileLock($resourceFile);
            $this->_file->fileWrite($resourceFile, $block);
            $this->_file->fileUnlock($resourceFile);
            $this->_file->fileClose($resourceFile);

            $cssFile  = substr($filedefault, 0, -5) . '.css';
            try {
                $newCache = lessc::cexecute($filedefault);
            } catch (\Exception $e) {
                throw $e;
            }

            if (!is_string($result = $this->_file->isWritable($cssFile))) {
                file_put_contents($cssFile, $newCache['compiled']);
            } else {
                throw new \Exception($result);
            }

        }catch (\Exception $gener){
            $this->messageManager->addError(__('Failed generating CSS file: '.$filename.' in ' . $this->_helperFooterCssgen->getGeneratedCssDir() ). '<br/>Message: ' . $gener->getMessage());
            $this->_logger->critical($gener);
        }

    }
}