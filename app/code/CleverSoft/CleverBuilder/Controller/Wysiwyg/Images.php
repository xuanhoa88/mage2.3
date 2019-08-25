<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CleverSoft\CleverBuilder\Controller\Wysiwyg;
use Magento\Framework\App\Action;

abstract class Images extends \Magento\Framework\App\Action\Action
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param \Magento\Framework\App\Action\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     */
    public function __construct(Action\Context $context, \Magento\Framework\Registry $coreRegistry)
    {
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context);
    }

    /**
     * Init storage
     *
     * @return $this
     */
    protected function _initAction()
    {
        $this->getStorage();
        return $this;
    }

    /**
     * Register storage model and return it
     *
     * @return \CleverSoft\CleverBuilder\Model\Wysiwyg\Images\Storage
     */
    public function getStorage()
    {
        if (!$this->_coreRegistry->registry('storage')) {
            $storage = $this->_objectManager->create(\CleverSoft\CleverBuilder\Model\Wysiwyg\Images\Storage::class);
            $this->_coreRegistry->register('storage', $storage);
        }
        return $this->_coreRegistry->registry('storage');
    }
}
