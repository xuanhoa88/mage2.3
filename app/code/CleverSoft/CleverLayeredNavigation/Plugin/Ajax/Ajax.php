<?php
/**
 * @category    CleverSoft
 * @package     CleverLayeredNavigation
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverLayeredNavigation\Plugin\Ajax;
use \Magento\Framework\View\Result\PageFactory;

class Ajax
{
    /**
     * @var \CleverSoft\CleverLayeredNavigation\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\Controller\Result\RawFactory
     */
    protected $resultRawFactory;


    public $resultPageFactory;

    /**
     * CategoryViewAjax constructor.
     *
     * @param \CleverSoft\CleverLayeredNavigation\Helper\Data                      $helper
     * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
     */
    public function __construct(
        \CleverSoft\CleverLayeredNavigation\Helper\Data $helper,
        PageFactory $factory,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
    ) {
        $this->helper = $helper;
        $this->resultPageFactory = $factory;
        $this->resultRawFactory = $resultRawFactory;
    }

    /**
     * @param $controller
     *
     * @return bool
     */
    public function isAjax($controller)
    {
        $isAjax = $controller->getRequest()->isAjax();
        return $this->helper->isAjaxEnabled() && $isAjax;
    }

    /**
     * @param \Magento\Framework\View\Result\Page $page
     *
     * @return array
     */
    public function getAjaxResponseData(\Magento\Framework\View\Result\Page $page)
    {
        $products = $page->getLayout()->getBlock('category.products');
        $navigation = $page->getLayout()->getBlock('catalog.leftnav');
        $h1 = $page->getLayout()->getBlock('page.main.title');
        $title = $page->getConfig()->getTitle();

        $htmlCategoryData = '';
        $children = $page->getLayout()->getChildNames('category.view.container');
        foreach ($children as $child) {
            $htmlCategoryData .= $page->getLayout()->renderElement($child);
        }
        $htmlCategoryData = '<div class="category-view">' . $htmlCategoryData . '</div>';

        $shopbyCollapse = $page->getLayout()->getBlock('catalog.navigation.collapsing');
        $shopbyCollapseHtml = '';
        if($shopbyCollapse) {
            $shopbyCollapseHtml = $shopbyCollapse->toHtml();
        }


        $responseData = [
            'categoryProducts'=>$products->toHtml(),
            'navigation' => $navigation->toHtml().$shopbyCollapseHtml,
            'h1' => $h1 ? $h1->toHtml() : '',
            'title' => $title->get(),
            'catalogSearch' => false,
            'categoryData' => $htmlCategoryData,
            'type'=> $this->helper->getAjaxScrollType()
        ];

        return $responseData;
    }

    /**
     * @param \Magento\Framework\View\Result\Page $page
     *
     * @return array
     */
    public function getAjaxSearchResponseData(\Magento\Framework\View\Result\Page $page)
    {
        $products = $page->getLayout()->getBlock('search.result');
        $navigation = $page->getLayout()->getBlock('catalogsearch.leftnav');
        $h1 = $page->getLayout()->getBlock('page.main.title');
        $title = $page->getConfig()->getTitle();

        $shopbyCollapse = $page->getLayout()->getBlock('catalog.navigation.collapsing');
        $shopbyCollapseHtml = '';
        if($shopbyCollapse) {
            $shopbyCollapseHtml = $shopbyCollapse->toHtml();
        }

        $responseData = [
            'categoryProducts'=>$products->toHtml(),
            'navigation' => $navigation->toHtml().$shopbyCollapseHtml,
            'title' => $title->get(),
            'h1' => $h1->toHtml(),
            'catalogSearch' => true,
            'type'=> $this->helper->getAjaxScrollType()
        ];

        return $responseData;
    }

    /**
     * @param array $data
     *
     * @return \Magento\Framework\Controller\Result\Raw
     */
    public function prepareResponse(array $data)
    {
        $response = $this->resultRawFactory->create();
        $response->setHeader('Content-type', 'text/plain');
        $response->setContents(json_encode($data));
        return $response;
    }
}
