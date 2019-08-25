<?php
/**
 * @category    CleverSoft
 * @package     CleverBuilder
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */
namespace CleverSoft\CleverBuilder\Controller\Element;



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

            $type = isset($data['element_type']) ? $data['element_type'] : '';
            switch ($type) {
                case 'slider':
                    $this->getResponse()->setBody($page->getLayout()
                        ->createBlock('CleverSoft\CleverBuilder\Block\Builder\Content\Render\Content\Slider')
                        ->setData($data)
                        ->setTemplate('CleverSoft_CleverBuilder::content/slider/content.phtml')
                        ->toHtml()
                    ); 
                    break;
            }
            
        }
		
	}
}
