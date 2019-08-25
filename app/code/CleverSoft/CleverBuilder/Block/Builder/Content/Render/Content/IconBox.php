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

class IconBox extends Content
{

    /*
     * set template for generating html
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('content/iconbox.phtml');
        $this->setDefaultData();
    }
}
