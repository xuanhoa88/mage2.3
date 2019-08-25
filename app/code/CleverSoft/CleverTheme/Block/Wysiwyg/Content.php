<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CleverSoft\CleverTheme\Block\Wysiwyg;

use Magento\Store\Model\StoreManagerInterface;
 
class Content extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;
     
    /**
     * @var \Magento\Cms\Model\Template\FilterProvider
     */
    protected $_filterProvider;
 
    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
 
    /**
     * @param \Magento\Backend\Block\Template\Context    $context
     * @param array                                      $data
     */
    public function __construct(
        \Magento\Cms\Model\Template\FilterProvider $filterProvider,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectmanager,
        array $data = []
    ) {
        $this->_scopeConfig = $context->getScopeConfig();
        $this->_storeManager = $context->getStoreManager();
        $this->_filterProvider = $filterProvider;
        $this->_objectManager = $objectmanager;
        parent::__construct($context, $data);
    }
 
    /**
     * Prepare layout.
     *
     * @return this
     */
    public function _prepareLayout()
    {
        $pageMainTitle = $this->getLayout()->getBlock('page.main.title');
        if ($pageMainTitle) {
            $pageMainTitle->setPageTitle('WYSIWYG Content');
        }
 
        return parent::_prepareLayout();
    }
 
     
    /**
     * Prepare HTML content
     *
     * @return string
     */
    public function getCmsFilterContent($value='')
    {
        $html = $this->_filterProvider->getPageFilter()->filter($value);
        return $html;
    }

    public function getTotalFreeShipping() {
        return $this->_objectManager->create('CleverSoft\CleverTheme\Block\Cart\CalculateShipping')->getNeededTotal();
    }
}