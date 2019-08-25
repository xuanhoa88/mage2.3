<?php
/**
 * @category    CleverSoft
 * @package     CleverLayeredNavigation
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverLayeredNavigation\Plugin;


class StatePlugin
{
    /**
     * @var \CleverSoft\CleverLayeredNavigation\Helper\Data
     */
    protected $helper;

    public function __construct(\CleverSoft\CleverLayeredNavigation\Helper\Data $helper)
    {
        $this->helper = $helper;
    }

    public function aroundGetClearUrl(\Magento\LayeredNavigation\Block\Navigation\State $subject, \Closure $closure)
    {
        if(!$this->helper->isAjaxEnabled()) {
            return $closure();
        }

        $filterState = [];
        $filterState['isAjax'] = null;
        $filterState['_'] = null;

        foreach ($subject->getActiveFilters() as $item) {
            $filterState[$item->getFilter()->getRequestVar()] = $item->getFilter()->getCleanValue();
        }
        $params['_current'] = true;
        $params['_use_rewrite'] = true;
        $params['_query'] = $filterState;
        $params['_escape'] = true;
        return $subject->getUrl('*/*/*', $params);
    }
}
