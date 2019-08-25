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

class Gap extends Content
{
    /*
     * @var array - default value.
     */
    protected $default = array(
        'title' => 'Gap Title',
        'height'=> 'height'
    );
    /*
     * set template for generating html
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('content/gap.phtml');
        $this->setDefaultData();
    }
}
