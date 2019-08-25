<?php
/**
 * @category    CleverSoft
 * @package     CleverTheme
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */
/**
 * Page header block
 */

namespace CleverSoft\CleverTheme\Block\Product;

use CleverSoft\CleverTheme\Helper\Data as HelperData;
use CleverSoft\CleverTheme\Helper\Template\Catalog\Product\View as HelperTemplateProductView;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Registry;
use CleverSoft\CleverTheme\Model\System\Config\Source\Product\Pagelayout;

class View extends \Magento\Framework\View\Element\Template
{
    /**
     * Theme helper
     *
     * @var HelperData
     */
    protected $theme;

    protected $pagelayout;

    /**
     * Product view helper
     *
     * @var HelperTemplateProductView
     */
    protected $helperProductView;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @param Context $context
     * @param HelperData $helperData
     * @param HelperTemplateProductView $helperTemplateProductView
     * @param array $data
     */
    public function __construct(
        Context $context,
        HelperData $helperData,
        HelperTemplateProductView $helperTemplateProductView,
        Registry $registry,
        Pagelayout $pagelayout,
        array $data = []
    ) {
        $this->theme = $helperData;
        $this->pagelayout = $pagelayout;
        $this->helperProductView = $helperTemplateProductView;
        $this->registry = $registry;

        parent::__construct($context, $data);
    }

    /**
     * Get helper
     *
     * @return HelperData
     */
    public function getHelperTheme()
    {
        return $this->theme;
    }

    /**
     * Get helper
     *
     * @return HelperTemplateProductView
     */
    public function getHelperProductView()
    {
        return $this->helperProductView;
    }

    /**
     * Retrieve currently viewed product object
     *
     * @return \Magento\Catalog\Model\Product
     */
    public function getProduct()
    {
        return $this->registry->registry('product');
    }

    public function getTemplate(){
        $layout = $this->theme->getCfg('product_page/page_layout');
        $pageLayouts = $this->pagelayout->toOptionArray();
        $pageLayouts = array_column($pageLayouts,'value');
        if(!in_array($layout,$pageLayouts)) {
            $layout = $pageLayouts[0];
        }
        return 'CleverSoft_CleverTheme::product/view/layouts/view_'.$layout.'.phtml';
    }

}
