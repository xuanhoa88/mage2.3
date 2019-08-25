<?php
/**
 * @category    CleverSoft
 * @package     CleverLayeredNavigation
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverLayeredNavigation\Observer\Admin;

use Magento\Catalog\Block\Adminhtml\Product\Attribute\Edit\Tabs;
use Magento\Framework\Event\ObserverInterface;

class ViewBlockAbstractToHtmlBefore implements ObserverInterface
{
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $block = $observer->getBlock();
        if($block instanceof Tabs) {
            /** @var Tabs $block */
            $block->addTabAfter(
                'cleversoft_shopby',
                [
                    'label' => __('Clever Layered Navigation'),
                    'title' => __('Clever Layered Navigation'),
                    'content' => $block->getChildHtml('clevershopby'),

                ],
                'front'
            );
        }
    }
}
