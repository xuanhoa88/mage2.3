<?php
/**
 * @category    CleverSoft
 * @package     CleverLayeredNavigation
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverLayeredNavigation\Plugin;

use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;

class ToolbarPlugin
{
    /** @var  Registry */
    protected $registry;

    public function __construct(Registry $registry)
    {
        $this->registry = $registry;
    }

    public function beforeGetPagerUrl(Template $subject, $params = [])
    {
        $seo_parsed = $this->registry->registry('clevershopby_parsed_params');
        if (is_array($seo_parsed)) {
            $params = array_merge($seo_parsed, $params);
        }
        return [$params];
    }
}
