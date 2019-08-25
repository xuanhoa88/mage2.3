<?php
/**
 * @category    CleverSoft
 * @package     CleverTheme
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverTheme\Model\Export;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Store\Model\ScopeInterface;
use SimpleXMLElement;
use Magento\Cms\Model\ResourceModel\Block\CollectionFactory as BlockCollectionFactory;
use Magento\Cms\Model\BlockFactory as BlockFactory;
use Magento\Cms\Model\ResourceModel\Block as BlockResourceBlock;
use Magento\Cms\Model\ResourceModel\Page\CollectionFactory as PageCollectionFactory;
use Magento\Cms\Model\PageFactory as PageFactory;
use Magento\Cms\Model\ResourceModel\Page as PageResourceBlock;
use Symfony\Component\Config\Definition\Exception\Exception;

class Export
{
    /**
     * @var ScopeConfigInterface
     */
    protected $_scopeConfig;

    protected $_storeManager;

    private $_exportPath;

    protected $_dirReader;

    protected $_parser;

    protected $_blockCollectionFactory;

    protected $_blockRepository;

    protected $_blockFactory;

    protected $_pageCollectionFactory;

    protected $_pageRepository;

    protected $_modules = [
            'CleverSoft_CleverTheme', 'CleverSoft_CleverTheme'
    ];

    protected $_configScopeConfigInterface;

    protected $_pageFactory;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        BlockCollectionFactory $blockCollectionFactory,
        \Magento\Cms\Api\BlockRepositoryInterface $blockRepository,
        BlockFactory $blockFactory,
        \Magento\Framework\Module\Dir\Reader $dirReader,
        \Magento\Framework\App\Config\ScopeConfigInterface $configScopeConfigInterface,
        PageCollectionFactory $pageCollectionFactory,
        \Magento\Cms\Api\PageRepositoryInterface $pageRepository,
        PageFactory $pageFactory
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->_storeManager = $storeManager;
        $this->_blockCollectionFactory = $blockCollectionFactory;
        $this->_blockFactory = $blockFactory;
        $this->_blockRepository = $blockRepository;
        $this->_configScopeConfigInterface = $configScopeConfigInterface;
        $this->_pageCollectionFactory = $pageCollectionFactory;
        $this->_pageFactory = $pageFactory;
        $this->_dirReader = $dirReader;
        $this->_pageRepository = $pageRepository;
        $this->_exportPath = BP . '/app/code/CleverSoft/CleverTheme/etc/export/';
        $this->_parser = new \Magento\Framework\Xml\Parser();
    }

    public function export($type,$store) {
        $type = strip_tags($type);
        $type = str_replace('"','',$type);
        if (is_array($type)){
            throw new \Exception("Export Type retrieved as array. Expected string.");
        }

        if (is_array($store)){
            throw new \Exception("Website/Store ID retrieved as array. Expected string.");
        }
        // Default response
        $gatewayResponse = new DataObject([
            'is_valid' => false,
            'ex_path' => '',
            'request_success' => false,
            'request_message' => __('Error during Export CMS Sample Datas - One of selections was not selected.'),
        ]);

        if(empty($type)) return $gatewayResponse;

        switch ($type) {
            case 'pages' :
            case 'blocks':
                $file = $this->_exportPath.$type.'.xml';
                $this->_createExportDir($file);
                try {
                    $this->_exportCms($type,$store,$file);
                    $gatewayResponse->setIsValid(true);
                    $gatewayResponse->setRequestSuccess(true);
                    $gatewayResponse->setRequestMessage(__('Exported '.$type.' Successfully'));
                } catch (\Exception $exception){
                    $gatewayResponse->setIsValid(false);
                    $gatewayResponse->setRequestMessage($exception->getMessage());
                }

                break;
            case 'theme_setting':
                $group = $this->_storeManager->getStore(intval($store))->getStoreGroupId();
                $group_name = str_replace(' ','_',$this->_storeManager->getGroup(intval($group))->getName());
                $store_name = str_replace(' ','_',$this->_storeManager->getStore(intval($store))->getName());
                $file = $this->_exportPath.$group_name.'_'.$store_name.'.xml';
                if (intval($store) == 0) $file = $this->_exportPath.'default_config.xml';
                $this->_createExportDir($file);
                try {
                    $this->_exportThemeSetting($file,$store);
                    $gatewayResponse->setIsValid(true);
                    $gatewayResponse->setRequestSuccess(true);
                    $gatewayResponse->setRequestMessage(__('Exported Theme Settings Successfully'));
                }catch (\Exception $exception) {
                    $gatewayResponse->setIsValid(false);
                    $gatewayResponse->setRequestMessage($exception->getMessage());
                }
                break;
        }
        return $gatewayResponse;
    }

    /*
     * export pages processing
     */
    protected function _exportPages($pages,$file){
        if (count($pages->getItems()) == 0) throw new \Exception("Nothing to export.");
        $rootXmlZero = simplexml_load_string("<root></root>", "Magento\Framework\Simplexml\Element",LIBXML_NOCDATA);
        $rootXml = $rootXmlZero->addChild('pages');
        foreach ($pages->getItems() as $item) {
            $itemxml = $rootXml->addChild('cms_page');
            $itemxml->addChild('title',$item->getData('title'));
            $itemxml->addChild('identifier',$item->getData('identifier'));
            $itemxml->addChild('content');
            foreach ($itemxml->xpath('content') as $xpath) {
                $cdata_content = dom_import_simplexml($xpath);
                $cdata_content->appendChild($cdata_content->ownerDocument->createCDATASection($item->getData('content')));
            }
            $itemxml->addChild('is_active',$item->getData('is_active'));
            $itemxml->addChild('page_layout',($item->getData('page_layout')? $item->getData('page_layout') : 'empty'));
            $itemxml->addChild('stores',implode(',',$item->getData('store_id')));
        }
        $dom = dom_import_simplexml($rootXmlZero)->ownerDocument;
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        if (!$dom->save($file))  {
            throw new \Exception("Unable to write file.");
        }

    }
     /*
     * export block processing
     */

    protected function _exportBlocks($blocks,$file){
        $rootXmlZero = simplexml_load_string("<root></root>", "Magento\Framework\Simplexml\Element",LIBXML_NOCDATA);
        $rootXml = $rootXmlZero->addChild('blocks');
        foreach ($blocks->getItems() as $item) {
            $itemxml = $rootXml->addChild('cms_block');
            $itemxml->addChild('title',$item->getData('title'));
            $itemxml->addChild('identifier',$item->getData('identifier'));
            $itemxml->addChild('content');
            foreach ($itemxml->xpath('content') as $xpath) {
                $cdata_content = dom_import_simplexml($xpath);
                $cdata_content->appendChild($cdata_content->ownerDocument->createCDATASection($item->getData('content')));
            }
            $itemxml->addChild('is_active',$item->getData('is_active'));
            $itemxml->addChild('stores',implode(',',$item->getData('store_id')));
        }
        $dom = dom_import_simplexml($rootXmlZero)->ownerDocument;
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        if (!$dom->save($file))  {
            throw new \Exception("Unable to write file.");
        }
    }

    /*
     *export blocks, pages
     */

    protected function _exportCms($type,$store,$file) {
        switch ($type) {
            case 'pages':
                $pages = $this->_pageFactory->create()->getCollection();
                $this->_exportPages($pages,$file);
                break;
            case 'blocks' :
                $blocks = $this->_blockFactory->create()->getCollection();
                $this->_exportBlocks($blocks,$file);
                break;
        }
    }

    /*
     * export theme and module setting from global configuration
     */
    protected function _exportThemeSetting($file,$store,$flag_ifOptionNullReplaceWithDefaultConfig=TRUE, $flag_ifDefaultConfigNullOmit = FALSE) {
        $modules = $this->_modules;
        $path = 'default';
        $rootNode = $this->_getModulesConfigDefaultValues($modules, $path);
        $nodesToRemove = [];

        foreach ($rootNode->children() as $section) {
            foreach ($section->children() as $group) {
                foreach ($group->children() as $option) {
                    if ($option->hasChildren()) {
                        continue; //Omit this node
                    }
                    $optionPath = $section->getName() . '/' . $group->getName() . '/' . $option->getName();
                    $valueFromConfig = $this->_configScopeConfigInterface->getValue(
                        $optionPath,
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                        $store
                    );
                    //and if option value is NULL (after retrieving from db it's an empty string)
                    if ($store > 0 && '' === $valueFromConfig) {
                        if ($flag_ifOptionNullReplaceWithDefaultConfig) {
                            //Get option value from Default Config
                            $valueFromConfig_DEFAULT = $this->_configScopeConfigInterface->getValue(
                                $optionPath,
                                0
                            );
                            $valueFromConfig = $valueFromConfig_DEFAULT;
                            ///$this->_logLoggerInterface->debug('     NULL, replace with Default=' . $valueFromConfig_DEFAULT); ///

                            if ($flag_ifDefaultConfigNullOmit) {
                                if ('' === $valueFromConfig_DEFAULT)  {
                                    //If Default Config option value is NULL too, omit this node and don't export.
                                    //So the node has to be removed from the main XML object.
                                    ///$this->_logLoggerInterface->debug('     value from Default also NULL. Remove this path: '.$optionPath); ///
                                    $nodesToRemove[] = $optionPath;
                                    continue;
                                }
                            }
                        }
                    }

                    //Add exported value to the XML
                    $group->{$option->getName()} = $valueFromConfig;

                } //end: foreach
            } //end: foreach
        } //end: foreach

        //Remove nodes selected to be removed
        foreach ($nodesToRemove as $nodePath) {
            $node = $rootNode->xpath($nodePath);
            unset($node[0][0]);
        }

        //Save
        $niceXml = $rootNode->asNiceXml();
        if (!file_put_contents($file, $niceXml))  {
            throw new \Exception("Unable to write file.");
        }
    }

    /**
     * Check if directory of the file path exists and is writable. If not, create writable directory.
     *
     * @param string
     * @return bool
     */
    protected function _createExportDir($filepath)
    {
        $mode = 0777;
        $dir = dirname($filepath);
        if (is_dir($dir)){
            if (!is_writable($dir)) {
                chmod($dir, $mode);
            }
        }  else  {
            //Create directory
            if (!mkdir($dir, $mode, true)) {
                return FALSE;
            }
        }

        return TRUE;
    }


    /**
     * Get and collect configuration (by node specified in $path param) of all specified modules
     *
     * @param array
     * @param string
     * @return \Magento\Framework\Simplexml\Element
     */
    protected function _getModulesConfigDefaultValues($modules, $path)
    {
        $rootXml = simplexml_load_string("<default></default>", "Magento\Framework\Simplexml\Element");
        //$rootXml = new \Magento\Framework\Simplexml\Element("<default></default>");
        foreach ($modules as $module){
            //Get config nodes matching the path.
            //Nodes are inside the main node which is named the same as path.
            $node = $this->_getModuleConfig($module, $path);

            if ($node && ($node instanceof SimpleXMLElement)){
                //Get children of the main node and append them to the container node
                foreach ($node->children() as $child) {
                    $rootXml->appendChild($child);
                }
            }
        }

        return $rootXml;
    }

    protected function _getModuleConfig($module, $path)
    {
        $configFile = $this->_dirReader->getModuleDir("etc", $module) .
            DIRECTORY_SEPARATOR . "config.xml";

        //If file (and module) exists
        if (file_exists($configFile)) {
            $content = file_get_contents($configFile);

            //If file content successfully retrieved
            if ($content !== FALSE) {
                $xml = simplexml_load_string($content, "Magento\Framework\Simplexml\Element");
                //Get selected node
                $node = $xml->descend($path);
                return $node;
            }
        }
        return NULL;
    }
}
