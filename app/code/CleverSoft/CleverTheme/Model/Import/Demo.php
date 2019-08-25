<?php
/**
 * @category    CleverSoft
 * @package     CleverTheme
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverTheme\Model\Import;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use SimpleXMLElement;
class Demo
{
    /**
     * @var ScopeConfigInterface
     */
    protected $_scopeConfig;
    
    protected $_storeManager;
    
    private $_importPath;

    protected $modelConfigFactory;

    protected $_parser;
    
    protected $_configFactory;
    
    protected $_objectManager;
    
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\App\Config\ConfigResource\ConfigInterface $configFactory,
        \Magento\Config\Model\Config\Factory $modelConfigFactory
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->_storeManager = $storeManager;
        $this->_configFactory = $configFactory;
        $this->_objectManager= $objectManager;
        $this->_importPath = BP . '/app/code/CleverSoft/CleverTheme/etc/import/config/';
        $this->_parser = new \Magento\Framework\Xml\Parser();
        $this->modelConfigFactory = $modelConfigFactory;
    }

    public function importDemo($demo_version,$store=NULL)
    {

        // Default response
        $gatewayResponse = new DataObject([
            'is_valid' => false,
            'import_path' => '',
            'request_success' => false,
            'request_message' => __('Error during Import '.str_replace("_"," ",$demo_version).'.'),
        ]);

        if (empty($demo_version)) {
            $gatewayResponse->setRequestMessage(
                __("Please choose a demo version")
            );
            return $gatewayResponse;
        }

        try {
            $xmlPath = $this->_importPath . $demo_version . '.xml';
            $overwrite = true;
            
            if (!is_readable($xmlPath)) {
                throw new \Exception(
                    __("Can't get the data file for import ".str_replace("_"," ",$demo_version).": ".$xmlPath)
                );
            }

            //get scope
            if (FALSE === strstr($store, '@')){
                throw new \Exception('Incorrect format of scope/scopeId value.');
            }

            //Split input value to get scope and scope id
            $values = explode('@', $store);

            $scope	= $values[0];
            $scope_id	= $values[1];

            $this->_import($scope, $scope_id, $xmlPath);

            $gatewayResponse->setIsValid(true);
            $gatewayResponse->setRequestSuccess(true);

            if ($gatewayResponse->getIsValid()) {
                $gatewayResponse->setRequestMessage(__('Success to Import '.str_replace("_"," ",$demo_version).'.'));
            } else {
                $gatewayResponse->setRequestMessage(__('Error during Import '.str_replace("_"," ",$demo_version).'.'));
            }
        } catch (\Exception $exception) {
            $gatewayResponse->setIsValid(false);
            $gatewayResponse->setRequestMessage($exception->getMessage());
        }

        return $gatewayResponse;
    }

    protected function _import($scope, $scopeId, $filepath) {
        //Get root node
        $rootNode = $this->_getXmlFromFile($filepath);
        if (!$rootNode) {
            throw new \Exception("Unable to read XML data from file - empty file or invalid format.");
        }

        $configDataTemplate = $this->getConfigDataTemplateArray($scope, $scopeId);

        ///$this->_logLoggerInterface->debug('-- import scope: ' . $scope .' + '. $scopeId);
        foreach ($rootNode->children() as $section)
        {
            $configData = $configDataTemplate;
            $configData['section'] = $section->getName();
            $configData['groups']   = [];

            ///$this->_logLoggerInterface->debug($section->getName());
            foreach ($section->children() as $group)
            {
                $configData['groups'][$group->getName()] = [
                    'fields'=>[]];

                $configData['groups'][$group->getName()]['fields'];
                ///$this->_logLoggerInterface->debug('-- ' . $group->getName());
                foreach ($group->children() as $option)
                {
                    ///$this->_logLoggerInterface->debug('-- import to: ' . $section->getName() . '/' . $group->getName() . '/' . $option->getName() . ' <==' . $option);

                    //IMPORTANT: omit this node if it has deeper levels
                    if ($option->hasChildren())
                    {
                        ///$this->_logLoggerInterface->debug('---- omit, this node has children: ' . $option->getName() . ' ==> ...'); ///
                        continue;
                    }

                    //If option value is NULL (after retrieving it's an empty string), then import NULL
                    $optionValue = (string) $option;
                    if ('' === $optionValue)
                    {
                        $optionValue = NULL;
                    }
                    $configData['groups'][$group->getName()]['fields'][$option->getName()] =
                        ['value'=>$optionValue];
                }
            }

            $configModel = $this->modelConfigFactory->create(['data' => $configData]);
            try{
                $configModel->save();
            } catch (\Exception $exception) {
                throw new \Exception(
                    __("Can't save this demo : ".$filepath)
                );
            }

        } //end: foreach
    }

    /**
     * Get XML data from a file and load it as XML object
     *
     * @param string
     * @return \Magento\Framework\Simplexml\Element
     */
    protected function _getXmlFromFile($file) {
        $content = file_get_contents($file);
        return simplexml_load_string($content, "Magento\Framework\Simplexml\Element");
    }

    protected function getConfigDataTemplateArray($scope, $scopeId) {
        $configDataTemplate = [
            'website'   => null,
            'store'     => null
        ];
        if($scope == 'stores') {
            $configDataTemplate['store'] = $scopeId;
        }  else if($scope == 'websites')  {
            $configDataTemplate['website'] = $scopeId;
        }
        return $configDataTemplate;
    }
}
