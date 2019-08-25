<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CleverSoft\CleverBuilder\Controller\Wysiwyg\Images;

use Magento\Backend\App\Action;

/** @noinspection PhpUnnecessaryFullyQualifiedNameInspection */
class Thumbnail extends \CleverSoft\CleverBuilder\Controller\Wysiwyg\Images
{
    /**
     * @var \Magento\Framework\Controller\Result\RawFactory
     */
    protected $resultRawFactory;

    /**
     * @param Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
     */
    public function __construct(
        Action\Context $context,
        /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection */
        \Magento\Framework\Registry $coreRegistry,
        /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection */
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
    ) {
        $this->resultRawFactory = $resultRawFactory;
        parent::__construct($context, $coreRegistry);
    }

    /**
     * Generate image thumbnail on the fly
     *
     * @return \Magento\Framework\Controller\Result\Raw
     */
    /** @noinspection PhpInconsistentReturnPointsInspection */
    public function execute()
    {
        $file = $this->getRequest()->getParam('file');
        /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection */
        $file = $this->_objectManager->get(\CleverSoft\CleverBuilder\Helper\Wysiwyg\Images::class)->idDecode($file);
        $thumb = $this->getStorage()->resizeOnTheFly($file);
        /** @var \Magento\Framework\Controller\Result\Raw $resultRaw */
        $resultRaw = $this->resultRawFactory->create();
        if ($thumb !== false) {
            /** @var \Magento\Framework\Image\Adapter\AdapterInterface $image */
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection */
            $image = $this->_objectManager->get(\Magento\Framework\Image\AdapterFactory::class)->create();
            $image->open($thumb);
            $resultRaw->setHeader('Content-Type', $image->getMimeType());
            $resultRaw->setContents($image->getImage());
            return $resultRaw;
        } else {
            // todo: generate some placeholder
        }
    }
}
