<?php
/**
 * Copyright © 2017 CleverSoft, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace CleverSoft\CleverPinMarker\Block\Adminhtml;

use Magento\Framework\Escaper;

class AbstractHtmlField extends \Magento\Backend\Block\Widget\Form\Renderer\Fieldset\Element implements
    \Magento\Framework\Data\Form\Element\Renderer\RendererInterface
{
    protected $assetRepo;
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\App\Request\Http $request,
        \CleverSoft\CleverPinMarker\Model\PinMarkerFactory $pinMarkerFactory,
        array $data = [])
    {
        $this->assetRepo = $context->getAssetRepository();
        $this->_objectManager = $objectManager;
        $this->request = $request;
        $this->pinMarkerFactory = $pinMarkerFactory;
        parent::__construct($context, $data);
    }
    protected $_element;

    /**
     * @var string
     */
    protected $_template = 'pinmarker/fieldset/element.phtml';

    /**
     * Retrieve an element
     *
     * @return \Magento\Framework\Data\Form\Element\Fieldset
     */
    public function getElement()
    {
        return $this->_element;
    }

    /**
     * Render element
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $this->_element = $element;
        return $this->toHtml();
    }

    protected function _getButtonHtml($data)
    {
        $html = '<button type="button" class="edit__image p--a"';
        $html .= isset($data['onclick']) ? ' onclick="' . $data['onclick'] . '"' : '';
        $html .= isset($data['id']) ? ' id="' . $data['id'] . '"' : '';
        $html .= '>';
        $html .= '<i class="pin__icon--image"></i>';
        $html .= '<span class="p--a br--3">Change Image</span>';
        $html .= '</button>';

        return $html;
    }

    public function getImage() {
        $element = $this->_element;
        
        if( !empty( $element->getValue() ) ){
            $imageUrl = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA).$element->getValue();
        }else{
            $imageUrl = ''; 
        }
        
        return $imageUrl;
    }

    public function getHintHtml()
    {
        $element = $this->_element;
        $elementId = $element->getHtmlId();
        
        $html = $this->_getButtonHtml(
            [
                'onclick' => "MediabrowserUtility.openDialog(this, '" . $this->getUrl('pinmarker/wysiwyg_images',
                    ['target_element_id' => $elementId])
                    . "', null, null,'" . $this->escapeQuote(
                        __('Upload Images'), true) . "');"
            ]
        );
        return $html;
    }

    public function getDataObject()
    {
        return $this->getElement()->getForm()->getDataObject();
    }
    public function getAttribute()
    {
        return $this->getElement()->getEntityAttribute();
    }
    public function getAttributeCode()
    {
        return $this->getElement()->getName();
    }
    public function canDisplayUseDefault()
    {
        if (!$this->isScopeGlobal() &&
            $this->getDataObject() &&
            $this->getDataObject()->getId() &&
            $this->getDataObject()->getStore()
        ) {
            return true;
        }
        return false;
    }
    public function usedDefault()
    {
        $attributeCode = $this->getElement()->getName();
        $defaultValue = $this->getDataObject()->getDefaultValue($attributeCode);
        
        if (!$this->getDataObject()->getExistsStoreValueFlag($attributeCode)) {
            return true;
        } elseif ($this->getElement()->getValue() == $defaultValue &&
            $this->getDataObject()->getStore() != $this->_getDefaultStoreId()
        ) {
            return false;
        }
        if ($defaultValue === false && $this->getElement()->getValue()) {
            return false;
        }
        return $defaultValue === false;
    }
    public function checkFieldDisable()
    {
        if ($this->canDisplayUseDefault() && $this->usedDefault()) {
            $this->getElement()->setDisabled(true);
        }
        return $this;
    }
    protected function isScopeGlobal()
    {
        $names = ['store', 'entity_id', 'identifier', 'option_id', 'is_active'];
        if(in_array($this->getElement()->getName(), $names)){
            return true;
        }else{
            return false;
        }
    }
    protected function isScopeStore()
    {
        $names = [
            'description',
            'logo',
            'is_featured',
            'url_key',
            'meta_title',
            'meta_description',
            'meta_keyword'
        ];

        if(in_array($this->getElement()->getName(), $names)){
            return true;
        }else{
            return false;
        }
    }
    public function getScopeLabel()
    {
        $html = '';
        if ($this->isScopeGlobal()) {
            $html .= __('[GLOBAL]');
        } elseif ($this->isScopeStore()) {
            $html .= __('[STORE VIEW]');
        }

        return $html;
    }
    protected function _getDefaultStoreId()
    {
        return \Magento\Store\Model\Store::DEFAULT_STORE_ID;
    }

    public function getAllPins() {
        return $this->pinMarkerFactory->create()->getCollection()->getAllIds();
    }

    public function getPins() {
        $id = $this->request->getParam('id'); 
        if ($id) {
            return $this->_objectManager->create('CleverSoft\CleverPinMarker\Model\PinMarker')->load($id)->getWpaPin();
        }
        return null;
    }

    public function getPinId() {
        $id = $this->request->getParam('id'); 
        if ($id) {
            return $this->_objectManager->create('CleverSoft\CleverPinMarker\Model\PinMarker')->load($id)->getId();
        }
        return null;
    }
}
