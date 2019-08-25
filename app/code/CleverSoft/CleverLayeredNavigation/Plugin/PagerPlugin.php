<?php
/**
 * @category    CleverSoft
 * @package     CleverLayeredNavigation
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverLayeredNavigation\Plugin;


class PagerPlugin
{
    protected $helper;

    public function __construct(\CleverSoft\CleverLayeredNavigation\Helper\Data $helper)
    {
        $this->helper = $helper;
    }

    public function aroundGetPagerUrl(\Magento\Theme\Block\Html\Pager $subject, \Closure $closure, $params = [])
    {
        if($this->helper->isAjaxEnabled()) {
            $params['isAjax'] = null;
            $params['_'] = null;
        }

        return $closure($params);
    }
}
