<?php
/**
 * @category    CleverSoft
 * @package     Base
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author      ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverTheme\Block\Adminhtml\System\Config;

class HeaderBuilder extends \Magento\Config\Block\System\Config\Form\Field
{
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\App\Request\Http $request,
        \CleverSoft\CleverTheme\Model\ResourceModel\Block\CollectionFactory $collectionFactory,
        \Magento\Framework\ObjectManagerInterface $objectmanager,
        array $data = []
    ) {
        $this->request = $request;
        $this->_collectionFactory = $collectionFactory;
        $this->_objectManager = $objectmanager;
        parent::__construct($context, $data);
    }

    /**
     * Set template to itself
     *
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if (!$this->getTemplate()) {
            $this->setTemplate('system/config/header_builder.phtml');
        }
        return $this;
    }

    /**
     * Unset some non-related element parameters
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    /**
     * Get the button and scripts contents
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        return $this->_toHtml();
    }

    public function getHeaderData() {
        $storeId = $this->getCurrentStore();
        if ($this->_collectionFactory->create()->addFieldToFilter('store_id',$storeId)->getFirstItem()->getId()) {
            return $this->_collectionFactory->create()->addFieldToFilter('store_id',$storeId)->getFirstItem();
        }
        return $this->_collectionFactory->create()->addFieldToFilter('store_id',0)->getFirstItem();
    }

    public function getHeaderDesktopData() {
        return $this->getHeaderData()->getData('header_desktop_data');
    }

    public function getHeaderMobileData() {
        return $this->getHeaderData()->getData('header_mobile_data');
    }

    public function getCurrentStore() {
        return $this->request->getParam('store');
    }
}
