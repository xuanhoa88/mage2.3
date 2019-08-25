<?php
/**
 * @category    CleverSoft
 * @package     CleverCookieLaw
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author      ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverCookieLaw\Block\Adminhtml\Request;

class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        \CleverSoft\CleverCookieLaw\Model\DeleteFactory $requestDeleteFactory,
        \CleverSoft\CleverCookieLaw\Model\RectifyFactory $requestRectifyFactory,
        \CleverSoft\CleverCookieLaw\Model\ComplaintFactory $requestComplaintFactory,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        $this->_requestDeleteFactory = $requestDeleteFactory;
        $this->_requestRectifyFactory = $requestRectifyFactory;
        $this->_requestComplaintFactory = $requestComplaintFactory;
        parent::__construct($context, $data);
    }

    /**
     * Initialize Account edit block
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId = 'id';
        $this->_blockGroup = 'CleverSoft_CleverCookieLaw';
        $this->_controller = 'adminhtml_request';

        parent::_construct();

        $this->buttonList->remove('save');
        $this->buttonList->remove('reset');
    }

    /**
     * Retrieve text for header element depending on loaded blocklist
     *
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        return __('Requests');
    }

    /**
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }

    public function getRequestDelete() {
        $collection = $this->_requestDeleteFactory->create()->getCollection()->setOrder("created_at", "desc");
        return $collection;
    }

    public function getRequestRectify() {
        $collection = $this->_requestRectifyFactory->create()->getCollection()->setOrder("created_at", "desc");
        return $collection;
    }

    public function getRequestComplaint() {
        $collection = $this->_requestComplaintFactory->create()->getCollection()->setOrder("created_at", "desc");
        return $collection;
    }
}
