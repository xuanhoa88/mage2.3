<?php
/**
 * @category    CleverSoft
 * @package     CleverBuilder
 * @copyright   Copyright © 2018 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverBuilder\Block\Builder\Content\Render\Content;

use CleverSoft\CleverBuilder\Block\Builder\Content\Render\Content;

class Banner extends Content
{
    /*
     * @var array - default value.
     */
    protected $default = array(
        'title' => 'Banner Title',
        'style'=> 'simple',
        'type'=>'horizontal',
        'size'=>'xs',
        'tabs_align'=>'left',
        'nav_style'=>'normal'
    );
    /*
     * set template for generating html
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('content/banner.phtml');
        $this->setDefaultData();
    }
}
