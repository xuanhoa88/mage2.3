<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CleverSoft\CleverBuilder\Block\Wysiwyg\Images\Content;

/**
 * Uploader block for Wysiwyg Images
 *
 * @api
 * @since 100.0.2
 */
class Uploader extends \Magento\Backend\Block\Media\Uploader
{
    /**
     * @var \CleverSoft\CleverBuilder\Model\Wysiwyg\Images\Storage
     */
    protected $_imagesStorage;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\File\Size $fileSize,
        \CleverSoft\CleverBuilder\Model\Wysiwyg\Images\Storage $imagesStorage,
        array $data = []
    ) {
        $this->_imagesStorage = $imagesStorage;
        $this->_fileSizeService = $fileSize;

        parent::__construct($context,$fileSize, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $type = $this->_getMediaType();
        $allowed = $this->_imagesStorage->getAllowedExtensions($type);
        $labels = [];
        $files = [];
        foreach ($allowed as $ext) {
            $labels[] = '.' . $ext;
            $files[] = '*.' . $ext;
        }
        $this->getConfig()->setUrl(
            $this->_urlBuilder->addSessionParam()->getUrl('cleverbuilder/*/upload', ['type' => $type])
        )->setFileField(
            'image'
        )->setFilters(
            ['images' => ['label' => __('Images (%1)', implode(', ', $labels)), 'files' => $files]]
        );
    }

    /**
     * Return current media type based on request or data
     *
     * @return string
     */
    protected function _getMediaType()
    {
        if ($this->hasData('media_type')) {
            return $this->_getData('media_type');
        }
        return $this->getRequest()->getParam('type');
    }
}
