<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CleverSoft\CleverBuilder\Block\Wysiwyg\Images;

/**
 * Directory tree renderer for Cms Wysiwyg Images
 *
 * @api
 * @since 100.0.2
 */
class Tree extends \Magento\Backend\Block\Template
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * Cms wysiwyg images
     *
     * @var \CleverSoft\CleverBuilder\Helper\Wysiwyg\Images
     */
    protected $_cleverbuilderWysiwygImages = null;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    private $serializer;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \CleverSoft\CleverBuilder\Helper\Wysiwyg\Images $cleverbuilderWysiwygImages
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     * @param \Magento\Framework\Serialize\Serializer\Json|null $serializer
     * @throws \RuntimeException
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \CleverSoft\CleverBuilder\Helper\Wysiwyg\Images $cleverbuilderWysiwygImages,
        \Magento\Framework\Registry $registry,
        array $data = [],
        \Magento\Framework\Serialize\Serializer\Json $serializer = null
    ) {
        $this->_coreRegistry = $registry;
        $this->_cleverbuilderWysiwygImages = $cleverbuilderWysiwygImages;
        $this->serializer = $serializer ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Framework\Serialize\Serializer\Json::class);
        parent::__construct($context, $data);
    }

    /**
     * Json tree builder
     *
     * @return string
     */
    public function getTreeJson()
    {
        $storageRoot = $this->_cleverbuilderWysiwygImages->getStorageRoot();
        $collection = $this->_coreRegistry->registry(
            'storage'
        )->getDirsCollection(
            $this->_cleverbuilderWysiwygImages->getCurrentPath()
        );
        $jsonArray = [];
        foreach ($collection as $item) {
            $jsonArray[] = [
                'text' => $this->_cleverbuilderWysiwygImages->getShortFilename($item->getBasename(), 20),
                'id' => $this->_cleverbuilderWysiwygImages->convertPathToId($item->getFilename()),
                'path' => substr($item->getFilename(), strlen($storageRoot)),
                'cls' => 'folder',
            ];
        }
        return $this->serializer->serialize($jsonArray);
    }

    /**
     * Json source URL
     *
     * @return string
     */
    public function getTreeLoaderUrl()
    {
        return $this->getUrl('cleverbuilder/*/treeJson');
    }

    /**
     * Root node name of tree
     *
     * @return \Magento\Framework\Phrase
     */
    public function getRootNodeName()
    {
        return __('Storage Root');
    }

    /**
     * Return tree node full path based on current path
     *
     * @return string
     */
    public function getTreeCurrentPath()
    {
        $treePath = ['root'];
        if ($path = $this->_coreRegistry->registry('storage')->getSession()->getCurrentPath()) {
            $path = str_replace($this->_cleverbuilderWysiwygImages->getStorageRoot(), '', $path);
            $relative = [];
            foreach (explode('/', $path) as $dirName) {
                if ($dirName) {
                    $relative[] = $dirName;
                    $treePath[] = $this->_cleverbuilderWysiwygImages->idEncode(implode('/', $relative));
                }
            }
        }
        return $treePath;
    }

    /**
     * Get tree widget options
     *
     * @return array
     */
    public function getTreeWidgetOptions()
    {
        return [
            "folderTree" => [
                "rootName" => $this->getRootNodeName(),
                "url" => $this->getTreeLoaderUrl(),
                "currentPath" => array_reverse($this->getTreeCurrentPath()),
            ]
        ];
    }
}
