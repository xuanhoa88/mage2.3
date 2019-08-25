<?php
/**
 * Copyright © 2017 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace CleverSoft\CleverBuilder\Model\Config\Source;

class Widgets implements \Magento\Framework\Option\ArrayInterface
{
    public function __construct(
        \Magento\Widget\Model\WidgetFactory $widgetFactory
    )
    {
        $this->_widgetFactory = $widgetFactory;
    }

	public function toOptionArray()
    {
  		$allWidgets = $this->_widgetFactory->create()->getWidgetsArray();
  		$list = array();
  		foreach ($allWidgets as $widget) {
  			array_push($list,['value' => $widget['code'], 'label' => $widget['name']]);
  		}

  		return $list;
    }	
	
}
