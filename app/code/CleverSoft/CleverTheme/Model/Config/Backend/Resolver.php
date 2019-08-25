<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CleverSoft\CleverTheme\Model\Config\Backend;

/**
 * Class Resolver
 * @package Magento\Framework\View\Element\Template\File
 */
class Resolver extends \Magento\Framework\View\Element\Template\File\Resolver{

    /*
     * Reset templateFileMap then its empty
     */
    public function resetTemplateMap(){
       return $this->_templateFilesMap = [];
   }

    /*
     * Get all current template files in map
     */
    public function getTemplateMap() {
        return $this->_templateFilesMap;
    }

    /*
     * set Map to a new value from changes
     */
    public function setTemplateMap($temp){
        return $this->_templateFilesMap = $temp;
    }
}
