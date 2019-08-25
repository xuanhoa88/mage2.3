<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace CleverSoft\CleverBuilder\Plugin\Builder;
use  CleverSoft\CleverBuilder\Helper\Panels\Panels as PanelsHelper;
/**
 * Plugin for authorization role model
 */
class Page {
    /*
     *@var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    /*
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;
    /*
     * @var \CleverSoft\CleverBuilder\Helper\Panels\Panels
     */
    protected $_panelsHelper;
    /*
     * @var $_filterProvider
     */
    protected $_filterProvider;
    /**
     * Escaper
     *
     * @var \Magento\Framework\Escaper
     */
    protected $_escaper;
    /*
     *@var $storeManager \Magento\Store\Model\StoreManagerInterface
     *@var $messageManager \Magento\Framework\Message\ManagerInterface
     *@var $messageManager \Magento\Framework\Message\ManagerInterface
     *@var $panels \CleverSoft\CleverBuilder\Helper\Panels\Panels
     */
    public function __construct(\Magento\Framework\View\Element\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager, \Magento\Framework\Message\ManagerInterface $messageManager, PanelsHelper $panels , \CleverSoft\CleverBuilder\Helper\Widget\Template\FilterProvider $filterProvider) {
        $this->_storeManager     = $storeManager;
        $this->messageManager = $messageManager;
        $this->_panelsHelper = $panels;
        $this->_escaper = $context->getEscaper();
        $this->_filterProvider = $filterProvider;
    }
    /**
     * render the page html
     */
    public function afterGetPage( \Magento\Cms\Block\Page $subject ) {
        $panelData = $this->_panelsHelper->post_metadata( $this->_panelsHelper->getDataHelper()->getPanelsData() , $subject->getData('page')->getId() , 'panels_data') ? $this->_panelsHelper->post_metadata( $this->_panelsHelper->getDataHelper()->getPanelsData() , $subject->getData('page')->getId() , 'panels_data') : $this->_panelsHelper->getDataHelper()->getPanelsData();
        if (!empty($panelData)) {
            $pageHtml = $this->_panelsHelper->renderer()->render(intval( $subject->getData('page')->getId() ), true, $panelData);
            $refresh = $this->_panelsHelper->renderer()->render(intval( $subject->getData('page')->getId() ), false, $panelData);
            if($pageHtml) {
                $subject->getData('page')->setContent($pageHtml);
            }
            if($refresh) {
                $subject->getData('page')->setBuilder($refresh);
            }
        }
        // X-XSS-Protection - this error can throw in console (The XSS Auditor refused to execute a script in '...' because its source code was found within the request. )
        $subject->getData('page')->setContent($this->_filterProvider->filterPage($subject->getData('page')->getContent()));

        return $subject->getData('page');
    }
}
