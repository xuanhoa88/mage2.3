<?php
/**
 * @category    CleverSoft
 * @package     CleverTheme
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverTheme\Block\System\Config\Form\Button;

class Export extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * Button Label
     *
     * @var string
     */
    protected $_buttonLabel = 'Export';

    protected $_actionUrl;

    protected $_exType;

    /**
     * Set Button Label
     *
     * @param string $buttonLabel
     */
    public function setButtonLabel($buttonLabel)
    {
        $this->_buttonLabel = $buttonLabel;
        return $this;
    }

    /**
     * Get Action Url
     *
     * @return string
     */
    public function getActionUrl()
    {
        return $this->_actionUrl;
    }

    /**
     * Set Validate VAT Button Label
     *
     * @param string $vatButtonLabel
     */
    public function setActionUrl($actionUrl)
    {
        $this->_actionUrl = $actionUrl;
        return $this;
    }

    /**
     *
     * @return string
     */
    public function getExportType()
    {
        return $this->_exType;
    }

    /**
     * Set Validate VAT Button Label
     *
     * @param string $vatButtonLabel
     */
    public function setExportType($exType)
    {
        $this->_exType = $exType;
        return $this;
    }

    /**
     * Set template to itself
     *
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if (!$this->getTemplate()) {
            $this->setTemplate('system/config/export_button.phtml');
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
        $originalData = $element->getOriginalData();
        $buttonLabel = !empty($originalData['button_label']) ? $originalData['button_label'] : $this->_buttonLabel;
        $action = !empty($originalData['action_url']) ? $originalData['action_url'] : '';
        if($action) {
            $this->setActionUrl($action);
        }
        $type = uniqid();
        if($type) {
            $this->setExportType($type);
        }
        $this->addData(
            [
                'button_label' => __($buttonLabel),
                'import_type' => $type,
                'html_id' => $element->getHtmlId(),
                'ajax_url' => $this->_urlBuilder->getUrl($action),
            ]
        );

        return $this->_toHtml();
    }
}
