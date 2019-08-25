<?php

namespace CleverSoft\CleverPinMarker\Block\Adminhtml\Pincollection\Renderer;

class Image extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * Store manager.
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * pinMarkerFactory
     *
     * @var \CleverSoft\CleverPinMarker\Model\PinMarkerFactory
     */
    protected $_pinMarkerFactory;

    /**
     * [__construct description].
     *
     * @param \Magento\Backend\Block\Context              $context
     * @param \Magento\Store\Model\StoreManagerInterface  $storeManager
     * @param \CleverSoft\CleverPinMarker\Model\PinMarkerFactory $pinMarkerFactory
     * @param array                                       $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \CleverSoft\CleverPinMarker\Model\PinMarkerFactory $pinMarkerFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_storeManager = $storeManager;
        $this->_pinMarkerFactory = $pinMarkerFactory;
    }

    /**
     * Render action.
     *
     * @param \Magento\Framework\DataObject $row
     *
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $storeViewId = $this->getRequest()->getParam('store');
        $pinMarker = $this->_pinMarkerFactory->create()->load($row->getId());
        $srcImage = $this->_storeManager->getStore()->getBaseUrl(
                \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
            ) . $pinMarker->getImage();

        return '<image width="150" height="50" src ="'.$srcImage.'" alt="'.$pinMarker->getImage().'" >';
    }
}
