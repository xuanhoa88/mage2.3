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

class Row extends Content
{
    /*
     * @var array - default value.
     */
    protected $default = array(
        'size'=>'s',
        'height'=>'600',
        'width'=>'container_grid',
        'col_depth'=>'0',
        'col_hover_depth'=>'0',
    );
    /*
     * set template for generating html
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('content/row.phtml');
        $this->setDefaultData();
    }
}
