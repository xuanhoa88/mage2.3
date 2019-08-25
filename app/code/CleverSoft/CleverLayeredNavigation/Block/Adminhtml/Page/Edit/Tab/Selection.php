<?php
/**
 * @category    CleverSoft
 * @package     CleverLayeredNavigation
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverLayeredNavigation\Block\Adminhtml\Page\Edit\Tab;

/**
 * Class Selection
 *
 * @author Artem Brunevski
 */

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;
use CleverSoft\CleverLayeredNavigation\Model\Config\Source\Attribute as SourceAttribute;
use CleverSoft\CleverLayeredNavigation\Controller\RegistryConstants;
use Magento\Catalog\Model\Config as CatalogConfig;
use Magento\Catalog\Model\Product;

class Selection extends Generic implements TabInterface
{
    /** @var  SourceAttribute */
    protected $_sourceAttribute;

    /** @var CatalogConfig  */
    protected $_catalogConfig;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param SourceAttribute $sourceAttribute
     * @param CatalogConfig $catalogConfig
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        SourceAttribute $sourceAttribute,
        CatalogConfig $catalogConfig,
        array $data = []
    ) {
        $this->_sourceAttribute = $sourceAttribute;
        $this->_catalogConfig = $catalogConfig;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare label for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Selections');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return $this->getTabLabel();
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Prepare form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        /** @var \CleverSoft\CleverLayeredNavigation\Model\Page $model */
        $model = $this->_coreRegistry->registry(RegistryConstants::PAGE);

        $conditions = $model->getConditionsUnserialized();

        $attributes = $this->_sourceAttribute->toArray();
        $defaultAttributeId = count($attributes) > 0 ? array_keys($attributes)[0] : null;

        if (!$defaultAttributeId){
            return ;
        }

        $attributeIdx = 1;
        foreach($conditions as $condition)
        {
            $this->addSelectionControls($attributeIdx, $condition, $form, $attributes);
            $attributeIdx++;
        }

        $fieldset = $form->addFieldset(
            'add_selection_fieldset',
            ['legend' => __('Add Selection'), 'class' => 'fieldset-wide']
        );

        $fieldset->addField(
            'new_selection_filter',
            'select',
            [
                'values' => ['0' => __('Select attribute')] + $attributes,
                'label' => __('Filter'),
                'title' => __('Filter'),
                'onchange' => 'window.cleversoftLayeredNavigationSelection.add(\'add_selection_fieldset\', this.value); this.value=0; return;'
            ]
        );

        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * @param $attributeIdx
     * @param $condition
     * @param \Magento\Framework\Data\Form $form
     * @param $attributes
     * @return \Magento\Framework\Data\Form\Element\Fieldset
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function addSelectionControls(
        $attributeIdx,
        $condition,
        \Magento\Framework\Data\Form $form,
        $attributes
    ){
        $filter = array_key_exists('filter', $condition) ? $condition['filter'] : null;
        $value = array_key_exists('value', $condition) ? $condition['value'] : null;

        $attribute = $this->_catalogConfig->getAttribute(Product::ENTITY, $filter);

        $attributeValueId = 'attribute_value_' . $attributeIdx;
        $attributeDeleteId = 'attribute_delete_' . $attributeIdx;
        $fieldset = $form->addFieldset(
            $attributeIdx . '_selection_fieldset',
            ['legend' => __('Selection #%1', $attributeIdx), 'class' => 'fieldset-wide']
        );

        $fieldset->addField(
            $attributeIdx,
            'select',
            [
                'name' => 'conditions[' . $attributeIdx . '][filter]',
                'value' => $filter,
                'values' => $attributes,
                'class' => 'required-entry',
                'required' => true,
                'label' => __('Filter'),
                'title' => __('Filter'),
                'onchange' => 'window.cleversoftLayeredNavigationSelection.load(\'' . $attributeValueId . '_data\', ' . $attributeIdx . ', this.value)'
            ]
        );

        $fieldset->addField(
            $attributeValueId,
            'text',
            [
                'name' => $attributeValueId,
                'label' => __('Value'),
                'title' => __('Value')
            ]
        );

        $form->getElement(
            $attributeValueId
        )->setRenderer(
            $this->getLayout()
                ->createBlock('CleverSoft\CleverLayeredNavigation\Block\Adminhtml\Page\Edit\Tab\Selection\Option')
                ->setEavAttribute($attribute)
                ->setEavAttributeValue($value)
                ->setEavAttributeIdx($attributeIdx)
        );

        $fieldset->addField(
            $attributeDeleteId,
            'button',
            [
                'value' => __('Remove'),
                'onclick' => 'window.cleversoftLayeredNavigationSelection.remove(\'' . $attributeIdx . '_selection_fieldset\')'
            ]
        );

        return $fieldset;
    }
}