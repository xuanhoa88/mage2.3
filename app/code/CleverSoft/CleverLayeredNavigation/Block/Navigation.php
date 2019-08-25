<?php
/**
 * @category    CleverSoft
 * @package     CleverLayeredNavigation
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author      ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */
/**
 * Catalog layered navigation view block
 *
 */

namespace CleverSoft\CleverLayeredNavigation\Block;

use Magento\Framework\View\Element\Template;

class Navigation extends \Magento\Framework\View\Element\Template
{
    /**
     * Catalog layer
     *
     * @var \Magento\Catalog\Model\Layer
     */
    protected $_catalogLayer;

    /**
     * @var \Magento\Catalog\Model\Layer\FilterList
     */
    protected $filterList;

    /**
     * @var \Magento\Catalog\Model\Layer\AvailabilityFlagInterface
     */
    protected $visibilityFlag;

    /**
     * @param Template\Context $context
     * @param \Magento\Catalog\Model\Layer\Resolver $layerResolver
     * @param \Magento\Catalog\Model\Layer\FilterList $filterList
     * @param \Magento\Catalog\Model\Layer\AvailabilityFlagInterface $visibilityFlag
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        \Magento\Catalog\Model\Layer\FilterList $filterList,
        \Magento\Catalog\Model\Layer\AvailabilityFlagInterface $visibilityFlag,
        \CleverSoft\CleverLayeredNavigation\Helper\FilterSetting $filterSettingHelper,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Model\Session $catalogSession, 
        array $data = []
    ) {
        $this->_catalogLayer = $layerResolver->get();
        $this->filterList = $filterList;
        $this->visibilityFlag = $visibilityFlag;
        $this->filterSettingHelper = $filterSettingHelper;
        $this->request = $context->getRequest();
        $this->_registry = $registry;
        $this->catalogSession = $catalogSession;
        parent::__construct($context, $data);
    }

    /**
     * Apply layer
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $this->renderer = $this->getChildBlock('renderer');
        if (!empty($this->catalogSession->getData('list_expended'))) {
            $listExpandedFilters = [];
            $position = 0;

            foreach ($this->filterList->getFilters($this->_catalogLayer) as $filter) {
                if(empty($filter->getItemsCount())) {
                    continue;
                }
                $filter->apply($this->getRequest());
                $filterSetting = $this->filterSettingHelper->getSettingByLayerFilter($filter);
                $isApplyFilter = $this->request->getParam($filter->getRequestVar(), false);
                if($filterSetting->isExpanded() || $isApplyFilter) {
                    $listExpandedFilters[] = $position;
                }
                $position++;
            }
            $this->catalogSession->setData('list_expended', $listExpandedFilters);
            // $this->catalogSession->setData('first_load', 1);
        } else {
            foreach ($this->filterList->getFilters($this->_catalogLayer) as $filter) {
                $filter->apply($this->getRequest());
            }
        }
        $this->getLayer()->apply();
        return parent::_prepareLayout();
    }


    /**
     * Get layer object
     *
     * @return \Magento\Catalog\Model\Layer
     */
    public function getLayer()
    {
        return $this->_catalogLayer;
    }

    /**
     * Set path to template used for generating block's output.
     *
     * @param string $template
     * @return $this
     */
    public function setTemplate($template)
    {
        $this->_template = $template;
        return $this;
    }

    /**
     * Get layered navigation state html
     *
     * @return string
     */
    public function getStateHtml()
    {
        return $this->getChildHtml('state');
    }

    /**
     * Get all layer filters
     *
     * @return array
     */
    public function getFilters()
    {
        return $this->filterList->getFilters($this->_catalogLayer);
    }

    /**
     * Check availability display layer block
     *
     * @return bool
     */
    public function canShowBlock()
    {
        return $this->visibilityFlag->isEnabled($this->getLayer(), $this->getFilters());
    }

    /**
     * Get url for 'Clear All' link
     *
     * @return string
     */
    public function getClearUrl()
    {
        return $this->getChildBlock('state')->getClearUrl();
    }

    public function getLayeredType() {
        return $this->_registry->registry("layered_type");
    }
}
