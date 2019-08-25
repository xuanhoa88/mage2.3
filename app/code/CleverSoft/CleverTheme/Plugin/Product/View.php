<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CleverSoft\CleverTheme\Plugin\Product;

use CleverSoft\CleverTheme\Helper\Data;
use CleverSoft\CleverTheme\Model\System\Config\Source\Product\Pagelayout;


class View
{
    protected $helper;
    protected $pageLayout;

    /*
     * contructor
     */

    public function __construct(Data $helper, Pagelayout $pagelayout){
        $this->helper = $helper;
        $this->pageLayout = $pagelayout;
    }

    public function beforeInitProductLayout(\Magento\Catalog\Helper\Product\View $subject, $page) {
        if( $page instanceof \Magento\Framework\View\Result\Page ) {
            $layout = $this->helper->getCfg('product_page/page_layout');
            $pageLayouts = $this->pageLayout->toOptionArray();
            $pageLayouts = array_column($pageLayouts,'value');
            if(!in_array($layout,$pageLayouts)) {
                $layout = $pageLayouts[0];
            }
            $page->addPageLayoutHandles(['type' => $layout], null, false);
        }
    }
}
