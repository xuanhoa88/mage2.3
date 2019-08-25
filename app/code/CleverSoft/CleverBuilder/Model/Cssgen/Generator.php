<?php
/**
 * @category    CleverSoft
 * @package     CleverBuilder
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author      ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverBuilder\Model\Cssgen;

use Magento\Framework\Filesystem\DriverInterface;
use CleverSoft\CleverBuilder\Model\lessc;

class Generator extends \Magento\Framework\Model\AbstractModel{

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Catalog data
     *
     * @var Data
     */
    protected $_helperStyles = null;

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
    protected $_file;

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \CleverSoft\CleverBuilder\Helper\Styles $helperStyles,
        \Magento\Framework\Filesystem\Driver\File $file,
        \Magento\Framework\View\Layout $layout,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfigInterface,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\View\DesignInterface $design,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Magento\Framework\View\Design\Theme\ThemeProviderInterface $themeProviderInterface,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ){
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->_storeManager = $storeManager;
        $this->_helperStyles = $helperStyles;
        $this->_design = $design;

        $this->_scopeConfig = $scopeConfigInterface;
        $this->_directoryList = $directoryList;
        $this->_themeProvider = $themeProviderInterface;
        $this->layout = $layout;
        $this->messageManager = $messageManager;
        $this->_file = $file;
    }

    public function generateCss($storeCode, $panelsData, $pageId)
    {
        $prefix = '_' . $pageId;
        $design = "clever_visualpagebuilder_style";
        $filename = $design . $prefix . '.less';

        $path = 'CleverSoft_CleverBuilder::css/' . $design . '.phtml';

        $filedefault = $this->_helperStyles->getGeneratedCssDir() . $filename;
        try{

            $block = $this->layout->createBlock('\Magento\Framework\View\Element\Template')->setData(array('page_id' => $pageId, 'area' => 'frontend', 'cssgen_store' => $storeCode,'panels_data' => $panelsData))->setTemplate($path)->toHtml();
            if (empty($block)) {
                throw new \Exception( __("Template file is empty or doesn't exist: %s", $path) );
            }
            if(!$this->_file->isExists($this->_helperStyles->getGeneratedCssDir())){
                $this->_file->createDirectory($this->_helperStyles->getGeneratedCssDir(),DriverInterface::WRITEABLE_DIRECTORY_MODE);
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
            $this->messageManager->addError(__('Failed generating CSS file: '.$filename.' in ' . $this->_helperStyles->getGeneratedCssDir() ). '<br/>Message: ' . $gener->getMessage());
            $this->_logger->critical($gener);
        }

    }
}