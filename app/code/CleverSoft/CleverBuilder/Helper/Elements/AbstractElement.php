<?php
/**
 * @category    CleverSoft
 * @package     CleverBuilder
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverBuilder\Helper\Elements;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Component\ComponentRegistrarInterface;

class AbstractElement extends AbstractHelper {
    const FOLDER = 'Elements';
    const BUILDER = 'CleverSoft\\CleverBuilder\\Block\\Builder\\';
    /**
     * @var \Magento\Framework\Module\Dir\Reader
     */
    protected $_componentRegistrar;
    /*
     *@param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(Context $context, ComponentRegistrarInterface $componentRegistrarInterface) {
        parent::__construct($context);
        $this->_componentRegistrar = $componentRegistrarInterface;
    }
    /*
     * return module absolute path
     */
    public function getAbsolutePatchModule() {
        return $this->_componentRegistrar->getPath(ComponentRegistrar::MODULE, 'CleverSoft_CleverBuilder');
    }
    /*
     * return all available elements supported from Clever Visual Page Builder
     */
    public function getElementsByJsonFile() {
        $json_files = $this->getJsonFiles();
        if(empty($json_files)) return array();
        $json_files = $this->cleanJsonFiles($json_files);
        if(!empty($json_files)) {
            return $this->convertToPanelData($json_files);
        }
        return array();
    }
    /*
     * convert json data into panel data
     */
    protected function convertToPanelData($json_files){
        $panelData = array();
        foreach ($json_files as $key=>$js) {
            foreach ($js as $k=>$j) {
                try{
                    $data = json_decode(file_get_contents($this->getAbsolutePatchModule().'/'.self::FOLDER . '/' . ucfirst($key) . '/' .$j), true);
                    $panelData[$key][$data['type']] = $data;
                } catch (\Exception $e) {
                    $this->_logger->critical($e->getMessage());
                }
            }

        }
        return $panelData;
    }
    /*
     * remove unavailable json file
     */
    protected function cleanJsonFiles($files) {
        foreach($files as $key=> $file) {
            foreach ($file as $k=>$f) {
                $file_path = $this->getAbsolutePatchModule().'/'.self::FOLDER . '/' . ucfirst($key) . '/' . $f;
                if (is_file($file_path)) {
                    $info = pathinfo($file_path);
                    if ($info['extension'] != 'json') unset($files[$key][$k]) ;
                } else unset($files[$key][$k]) ;
            }
        }
        return $files;
    }
    /*
     * return all json files in Elements folder
     */
    protected function getJsonFiles($path = false, $keyPrev = false) {
        $files = array();
        if (!$path) $path = $this->getAbsolutePatchModule().'/'.self::FOLDER;
        if(is_dir($path)) {
            $dirFiles = scandir($path);
            if (false === $dirFiles) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    new \Magento\Framework\Phrase('Can\'t scan dir: %1', [$path])
                );
            }
            array_shift($dirFiles);
            /* remove  './'*/
            array_shift($dirFiles);
            /* remove  '../'*/
            foreach ($dirFiles as $key=>$dif) {
                if(is_dir($path.'/'.$dif)) {
                    $tempFiles = $this->getJsonFiles($path.'/'.$dif , strtolower($dif));
                    $files[strtolower($dif)] = $tempFiles[strtolower($dif)];
                } elseif($keyPrev) {
                    $files[$keyPrev][] = $dif;
                } else {
                    //do nothing
                }
            }
            return $files;
        }
        return array();
    }

    public function getPrebuiltLayoutJsonFile() {
        $json_files = $this->getJsonFiles();
        if(!isset($json_files['prebuiltlayout'])) return array();
        $files = $json_files['prebuiltlayout'];
        $filesSort = array();
        foreach($files as $key=> $file) {
            $info = pathinfo($file);
            $filesSort[$info['filename']] = $file;
        }

        return $filesSort;
    }
}