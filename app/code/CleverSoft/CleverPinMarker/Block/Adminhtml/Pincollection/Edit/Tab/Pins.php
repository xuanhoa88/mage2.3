<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CleverSoft\CleverPinMarker\Block\Adminhtml\Pincollection\Edit\Tab;

use Magento\Backend\Block\Widget\Grid\Column;

/**
 * Acl role user grid.
 *
 * @api
 * @since 100.0.2
 */
class Pins extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * Factory for user role model
     *
     * @var \CleverSoft\CleverPinMarker\Model\PinCollectionFactory
     */
    protected $_pinCollectionFactory;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $_jsonEncoder;

    /**
     * @var \CleverSoft\CleverPinMarker\Model\ResourceModel\PinMarker\CollectionFactory
     */
    protected $_pinMarkerFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \CleverSoft\CleverPinMarker\Model\PinCollectionFactory $pinCollectionFactory
     * @param \CleverSoft\CleverPinMarker\Model\ResourceModel\PinMarker\CollectionFactory $pinMarkerFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\Registry $coreRegistry,
        \CleverSoft\CleverPinMarker\Model\PinCollectionFactory $pinCollectionFactory,
        \CleverSoft\CleverPinMarker\Model\ResourceModel\PinMarker\CollectionFactory $pinMarkerFactory,
        array $data = []
    ) {
        parent::__construct($context, $backendHelper, $data);
        $this->_jsonEncoder = $jsonEncoder;
        $this->_coreRegistry = $coreRegistry;
        $this->_pinCollectionFactory = $pinCollectionFactory;
        $this->_pinMarkerFactory = $pinMarkerFactory;
    }

    /**
     * Class constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setDefaultSort('id');
        $this->setDefaultDir('asc');
        $this->setId('pinGrid');
        $this->setUseAjax(true);
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        $collection = $this->_pinMarkerFactory->create()->addFieldToFilter('is_actived',1);
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        echo $this->getLayout()->createBlock("Magento\Backend\Block\Template")->setTemplate('CleverSoft_CleverPinMarker::pinmarker/pins_grid_js.phtml')->toHtml();
        return parent::_prepareLayout();
    }

    /**
     * @return $this
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'pin_ids',
            [
                'header_css_class' => 'a-center',
                'type' => 'checkbox',
                'name' => 'pin_ids',
                'values' => $this->_getSelectedPins(),
                'align' => 'center',
                'index' => 'id'
            ]
        );

        $this->addColumn(
            'pin_id',
            [
                'header' => __('Pin ID'),
                'type' => 'number',
                'index' => 'id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id',
            ]
        );

        $this->addColumn(
            'pinmarker_label',
            ['header' => __('Pin Label'), 'align' => 'left', 'index' => 'pinmarker_label']
        );

        $this->addColumn(
            'image',
            [
                'header' => __('Image'),
                'filter' => false,
                'class' => 'xxx',
                'width' => '50px',
                'renderer' => 'CleverSoft\CleverPinMarker\Block\Adminhtml\Pincollection\Renderer\Image',
            ]
        );

        return parent::_prepareColumns();
    }

    protected function _getSelectedPins()
    {
        $collectionId = $this->getRequest()->getParam('id');
        if (!isset($collectionId)) {
            return [];
        }
        $pinCollection = $this->_pinCollectionFactory->create()->load($collectionId);
        $pins = $pinCollection->getPinIds();
        $pinIds = [];
        
        if ($pins) {
            $pinIds = explode(",",$pins); 
        } 
        return $pinIds;
    }
}
