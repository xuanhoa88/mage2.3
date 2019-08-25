<?php
/**
 * @category    CleverSoft
 * @package     CleverPageBuilder
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author      ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */
namespace CleverSoft\CleverBuilder\Helper\Panels;

class PanelsRendererLegacy extends \CleverSoft\CleverBuilder\Helper\Panels\PanelsRenderer 
{
    public function front_css_url(){
        return $this->_abstractBlock->getViewFileUrl('CleverSoft_CleverBuilder::css/front-legacy.min.css');
    }
}