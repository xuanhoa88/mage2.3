<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CleverSoft\CleverBuilder\Controller\Wysiwyg\Images;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Upload image.
 */

/** @noinspection PhpUnnecessaryFullyQualifiedNameInspection */
class Upload extends \CleverSoft\CleverBuilder\Controller\Wysiwyg\Images
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Magento\Framework\App\Filesystem\DirectoryResolver
     */
    private $directoryResolver;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Framework\App\Filesystem\DirectoryResolver|null $directoryResolver
     */
    public function __construct(
        /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection */
        \Magento\Backend\App\Action\Context $context,
        /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection */
        \Magento\Framework\Registry $coreRegistry,
        /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection */
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection */
        \Magento\Framework\App\Filesystem\DirectoryResolver $directoryResolver = null
    ) {
        parent::__construct($context, $coreRegistry);
        $this->resultJsonFactory = $resultJsonFactory;
        /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection */
        $this->directoryResolver = $directoryResolver
            ?: $this->_objectManager->get(\Magento\Framework\App\Filesystem\DirectoryResolver::class);
    }

    /**
     * Files upload processing.
     *
     * @return \Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        try {
            $this->_initAction();
            $path = $this->getStorage()->getSession()->getCurrentPath();
            if (!$this->directoryResolver->validatePath($path, DirectoryList::MEDIA)) {
                /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection */
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Directory %1 is not under storage root path.', $path)
                );
            }
            $result = $this->getStorage()->uploadFile($path, $this->getRequest()->getParam('type'));
        } catch (\Exception $e) {
            $result = ['error' => $e->getMessage(), 'errorcode' => $e->getCode()];
        }
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        
        return $resultJson->setData($result);
    }
}
