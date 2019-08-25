<?php
/**
 * @category    CleverSoft
 * @package     CleverLayeredNavigation
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverLayeredNavigation\Block\Product\ProductList;


use Magento\Framework\View\Element\Template;

class Ajax extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \CleverSoft\CleverLayeredNavigation\Helper\Data
     */
    protected $helper;

    public function __construct(Template\Context $context, \CleverSoft\CleverLayeredNavigation\Helper\Data $helper, array $data = [])
    {
        $this->helper = $helper;
        parent::__construct($context, $data);
    }


    public function canShowBlock()
    {
        return $this->helper->isAjaxEnabled();
    }
}
