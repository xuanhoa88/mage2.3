<?php
/**
 * @category    CleverSoft
 * @package     CleverBuilder
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */
namespace CleverSoft\CleverBuilder\Controller\Product;



class Ajaxload extends \Magento\Framework\App\Action\Action
{
	protected $resultPageFactory;
	
	public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \CleverSoft\CleverBuilder\Helper\Product\Data $helperData,
		\Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        $this->_helperData = $helperData;
		$this->resultPageFactory = $resultPageFactory;
		parent::__construct($context);	
    }
   
    public function execute()
    {
        $data = $this->getRequest()->getParams();
        if ($data) {
            $page = $this->resultPageFactory->create(false, ['isIsolated' => true]);
            $layout = isset($data['layout_product']) ? $data['layout_product'] : '';
            $mode = isset($data['mode']) ? $data['mode'] : '';
            $data['collection'] = $this->_getProductCollection('collections', $mode);
            switch ($layout) {
                case 'list':
                    $this->getResponse()->setBody($page->getLayout()
                        ->createBlock('CleverSoft\CleverBuilder\Block\Builder\Element\Render\Products\GridProducts')
                        ->setData($data)
                        ->setTemplate('CleverSoft_CleverBuilder::element/product/cases/list.phtml')
                        ->toHtml()
                    ); 
                    break;
                case 'grid':
                    $this->getResponse()->setBody($page->getLayout()
                        ->createBlock('CleverSoft\CleverBuilder\Block\Builder\Element\Render\Products\GridProducts')
                        ->setData($data)
                        ->setTemplate('CleverSoft_CleverBuilder::element/product/cases/grid.phtml')
                        ->toHtml()
                    ); 
                    break;
                case 'carousel':
                    $this->getResponse()->setBody($page->getLayout()
                        ->createBlock('CleverSoft\CleverBuilder\Block\Builder\Element\Render\Products\CarouselProducts')
                        ->setData($data)
                        ->setTemplate('CleverSoft_CleverBuilder::element/product/cases/carousel.phtml')
                        ->toHtml()
                    );
                    break;
                case 'carousel-vertical':
                    $this->getResponse()->setBody($page->getLayout()
                        ->createBlock('CleverSoft\CleverBuilder\Block\Builder\Element\Render\Products\CarouselProducts')
                        ->setData($data)
                        ->setTemplate('CleverSoft_CleverBuilder::element/product/cases/carousel_vertical.phtml')
                        ->toHtml()
                    );
                    break;
                default:
                    $this->getResponse()->setBody($page->getLayout()
                        ->createBlock('CleverSoft\CleverBuilder\Block\Builder\Element\Render\Products\GridProducts')
                        ->setData($data)
                        ->setTemplate('CleverSoft_CleverBuilder::element/product/cases/grid.phtml')
                        ->toHtml()
                    );
                    break;
            }
            
        }
		
	}

    protected function _getProductCollection($type, $value){
        $data = $this->getRequest()->getParams();
        $params = [];
        if(isset($data['enable_countdown']) && (bool) $data['enable_countdown'] == 1  ) {
            $params['countdown_filter'] =1;
        }
        if (isset($data['category_ids']) && $data['category_ids']){
            $params['category_ids'] = explode(',', $data['category_ids']);
        }
        if (isset($data['product_ids']) && $data['product_ids']){
            $params['product_ids'] = explode(',', $data['product_ids']);
        }

        $limit = isset($data['limit']) && $data['limit'] ? $data['limit'] : 6;
        $this->_productCollection = $this->_helperData->getProducts($type, $value, $params, $limit);
        return $this->_productCollection;
    }
}
