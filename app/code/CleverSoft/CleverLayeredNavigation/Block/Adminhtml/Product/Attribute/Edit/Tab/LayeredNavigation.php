<?php
/**
 * @category    CleverSoft
 * @package     CleverLayeredNavigation
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverLayeredNavigation\Block\Adminhtml\Product\Attribute\Edit\Tab;

use CleverSoft\CleverLayeredNavigation\Model\FilterSetting;
use CleverSoft\CleverLayeredNavigation\Model\FilterSettingFactory;
use CleverSoft\CleverLayeredNavigation\Model\Source\DisplayMode;
use CleverSoft\CleverLayeredNavigation\Model\Source\MeasureUnit;
use CleverSoft\CleverLayeredNavigation\Model\Source\MultipleValuesLogic;
use CleverSoft\CleverLayeredNavigation\Model\Source\Showinblock;
use CleverSoft\CleverLayeredNavigation\Model\Source\Subcatview;
use CleverSoft\CleverLayeredNavigation\Model\Source\ExpandSubCat;
use CleverSoft\CleverLayeredNavigation\Model\Source\RenderCatLevel;
use CleverSoft\CleverLayeredNavigation\Model\Source\ShowProductQuantities;
use CleverSoft\CleverLayeredNavigation\Model\Source\SortOptionsBy;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form;
use Magento\Config\Model\Config\Source\Yesno;
use Magento\Catalog\Model\Entity\Attribute;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;

class LayeredNavigation extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * @var Yesno
     */
    protected $yesNo;

    /** @var  DisplayMode */
    protected $displayMode;

    /** @var  MeasureUnit */
    protected $measureUnitSource;

    /** @var  MultipleValuesLogic */
    protected $multipleValuesLogic;

    /** @var  FilterSetting */
    protected $setting;

    /** @var Attribute $attributeObject */
    protected $attributeObject;

    protected $showInBlock;

    protected $subCatview;
    protected $expandSubCat;
    protected $renderCatLevel;
    /**
     * @var SortOptionsBy
     */
    protected $sortOptionsBy;

    /**
     * @var ShowProductQuantities
     */
    protected $showProductQuantities;

    /**
     * @var \Magento\Config\Model\Config\Structure\Element\Dependency\FieldFactory
     */
    protected $dependencyFieldFactory;

    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        Yesno $yesNo,
        Subcatview $subcatview,
        Showinblock $showinblock,
        ExpandSubCat $expandsubcat,
        RenderCatLevel $rendercatlevel,
        DisplayMode $displayMode,
        MeasureUnit $measureUnitSource,
        FilterSettingFactory $settingFactory,
        SortOptionsBy $sortOptionsBy,
        ShowProductQuantities $showProductQuantities,
        \Magento\Config\Model\Config\Structure\Element\Dependency\FieldFactory $dependencyFieldFactory,
        MultipleValuesLogic $multipleValuesLogic,
        array $data = []
    ) {
        $this->yesNo = $yesNo;
        $this->displayMode = $displayMode;
        $this->subCatview = $subcatview;
        $this->showInBlock = $showinblock;
        $this->expandSubCat = $expandsubcat;
        $this->renderCatLevel = $rendercatlevel;
        $this->measureUnitSource = $measureUnitSource;
        $this->setting = $settingFactory->create();
        $this->attributeObject = $registry->registry('entity_attribute');
        $this->displayMode->setAttributeType($this->attributeObject->getBackendType());
        $this->sortOptionsBy = $sortOptionsBy;
        $this->showProductQuantities = $showProductQuantities;
        $this->dependencyFieldFactory = $dependencyFieldFactory;
        $this->multipleValuesLogic = $multipleValuesLogic;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * {@inheritdoc}
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareForm()
    {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            ['data' => ['id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post']]
        );

        $this->prepareFilterSetting();
        $form->setDataObject($this->setting);

        $yesnoSource = $this->yesNo->toOptionArray();
        /** @var  $dependence \Magento\SalesRule\Block\Widget\Form\Element\Dependence */
        $dependence = $this->getLayout()->createBlock(
            'Magento\SalesRule\Block\Widget\Form\Element\Dependence'
        );

        $fieldsetDisplayProperties = $form->addFieldset(
            'shopby_fieldset_display_properties',
            ['legend' => __('Display Properties'), 'collapsable' => $this->getRequest()->has('popup')]
        );

        $displayModeField = $fieldsetDisplayProperties->addField(
            'display_mode',
            'select',
            [
                'name'     => 'display_mode',
                'label'    => __('Display Mode'),
                'title'    => __('Display Mode'),
                'values'   => $this->displayMode->toOptionArray(),
            ]
        );
        $dependence->addFieldMap(
            $displayModeField->getHtmlId(),
            $displayModeField->getName()
        );

        $fieldDisplayModeSliderDependencyNegative = $this->dependencyFieldFactory->create(
            ['fieldData' => ['value' => (string)DisplayMode::MODE_SLIDER, 'negative'=>true], 'fieldPrefix' => '']
        );



//        $fieldsetDisplayProperties->addField(
//            'show_in_block',
//            'select',
//            [
//                'name'     => 'show_in_block',
//                'label'    => __('Show in the Block'),
//                'title'    => __('Show in the Block'),
//                'values'   => $this->showInBlock->toOptionArray(),
//            ]
//        );


        //If attribute is CATEGORY attribute
        $id = $this->getRequest()->getParam('attribute_id');
        $_objectManager = \Magento\Framework\App\ObjectManager::getInstance(); //instance of\Magento\Framework\App\ObjectManager
        $model = $_objectManager->get('Magento\Catalog\Model\ResourceModel\Eav\Attribute');
        $model->load($id);
        $attributeCode = $model->getAttributeCode();
        if($attributeCode == 'category_ids'){
//            $fieldsetDisplayProperties->addField(
//                'cat_tree_deep',
//                'text',
//                [
//                    'name'     => 'cat_tree_deep',
//                    'label'    => __('Category Tree Depth'),
//                    'title'    => __('Category Tree Depth'),
//                    'value' => '1',
//                    'note' => __('Specify the max level number for category tree. Keep 1 to hide the subcategories'),
//                ]
//            );

//            $fieldsetDisplayProperties->addField(
//                'subcat_view',
//                'select',
//                [
//                    'name'     => 'subcat_view',
//                    'label'    => __('Subcategories View'),
//                    'title'    => __('Subcategories View'),
//                    'values'   => $this->subCatview->toOptionArray(),
//                ]
//            );
//
//            $fieldsetDisplayProperties->addField(
//                'expand_subcat',
//                'select',
//                [
//                    'name'     => 'expand_subcat',
//                    'label'    => __('Expand Subcategories'),
//                    'title'    => __('Expand Subcategories'),
//                    'values'   => $this->expandSubCat->toOptionArray(),
//                ]
//            );
//
//            $fieldsetDisplayProperties->addField(
//                'render_all_cat_tree',
//                'select',
//                [
//                    'name'     => 'render_all_cat_tree',
//                    'label'    => __('Render All Categories Tree'),
//                    'title'    => __('Render All Categories Tree'),
//                    'values'   => $this->yesNo->toOptionArray(),
//                    'note' => __('Yes (Render Full Categories Tree) or No (Only For Current Category Path)'),
//                ]
//            );
//
//            $fieldsetDisplayProperties->addField(
//                'render_cat_level',
//                'select',
//                [
//                    'name'     => 'render_cat_level',
//                    'label'    => __('Render Categories Level'),
//                    'title'    => __('Render Categories Level'),
//                    'values'   => $this->renderCatLevel->toOptionArray(),
//                ]
//            );

        }
        //End If attribute is CATEGORY attribute

        $sortOptionsByField = $fieldsetDisplayProperties->addField(
            'sort_options_by',
            'select',
            [
                'name'     => 'sort_options_by',
                'label'    => __('Sort Options By'),
                'title'    => __('Sort Options By'),
                'values'   => $this->sortOptionsBy->toOptionArray(),
            ]
        );

        $dependence->addFieldMap(
            $sortOptionsByField->getHtmlId(),
            $sortOptionsByField->getName()
        );

        $dependence->addFieldDependence(
            $sortOptionsByField->getName(),
            $displayModeField->getName(),
            $fieldDisplayModeSliderDependencyNegative
        );

        $showProductQuantitiesField = $fieldsetDisplayProperties->addField(
            'show_product_quantities',
            'select',
            [
                'name'     => 'show_product_quantities',
                'label'    => __('Show Product Quantities'),
                'title'    => __('Show Product Quantities'),
                'values'   => $this->showProductQuantities->toOptionArray(),
            ]
        );

        $dependence->addFieldMap(
            $showProductQuantitiesField->getHtmlId(),
            $showProductQuantitiesField->getName()
        );



        $dependence->addFieldDependence(
            $showProductQuantitiesField->getName(),
            $displayModeField->getName(),
            $fieldDisplayModeSliderDependencyNegative
        );

        $showSearchBoxField = $fieldsetDisplayProperties->addField(
            'is_show_search_box',
            'select',
            [
                'name'     => 'is_show_search_box',
                'label'    => __('Show Search Box'),
                'title'    => __('Show Search Box'),
                'values'   => $this->yesNo->toOptionArray(),
            ]
        );

        $dependence->addFieldMap(
            $showSearchBoxField->getHtmlId(),
            $showSearchBoxField->getName()
        );

        $dependence->addFieldDependence(
            $showSearchBoxField->getName(),
            $displayModeField->getName(),
            DisplayMode::MODE_DEFAULT
        );

        $numberUnfoldedOptions = $fieldsetDisplayProperties->addField(
            'number_unfolded_options',
            'text',
            [
                'name'     => 'number_unfolded_options',
                'label'    => __('Number of unfolded options'),
                'title'    => __('Number of unfolded options'),
                'note' => __('Other options will be shown after a customer clicks the "More" button.'),
            ]
        );

        $dependence->addFieldMap(
            $numberUnfoldedOptions->getHtmlId(),
            $numberUnfoldedOptions->getName()
        );

        $dependence->addFieldDependence(
            $numberUnfoldedOptions->getName(),
            $displayModeField->getName(),
            DisplayMode::MODE_DEFAULT
        );



        $fieldsetDisplayProperties->addField(
            'is_expanded',
            'select',
            [
                'name'     => 'is_expanded',
                'label'    => __('Expand'),
                'title'    => __('Expand'),
                'values'   =>  $this->yesNo->toOptionArray(),
            ]
        );


        $fieldsetDisplayProperties->addField(
            'tooltip',
            'textarea',
            [
                'name'     => 'tooltip',
                'label'    => __('Tooltip'),
                'title'    => __('Tooltip'),
            ]
        );



        $fieldsetFiltering = $form->addFieldset(
            'shopby_fieldset_filtering',
            ['legend' => __('Filtering'), 'collapsable' => $this->getRequest()->has('popup')]
        );

        $fieldsetFiltering->addField(
            'filter_code',
            'hidden',
            [
                'name'     => 'filter_code',
                'value'   => $this->setting->getFilterCode(),
            ]
        );



        if($this->attributeObject->getBackendType() != 'decimal') {
            if($attributeCode != 'category_ids') {
                $multiselectField = $fieldsetFiltering->addField(
                    'is_multiselect',
                    'select',
                    [
                        'name' => 'is_multiselect',
                        'label' => __('Allow Multiselect'),
                        'title' => __('Allow Multiselect'),
                        'values' => $yesnoSource,
                    ]
                );

                $useAndLogicField = $fieldsetFiltering->addField(
                    'is_use_and_logic',
                    'select',
                    [
                        'name'     => 'is_use_and_logic',
                        'label'    => __('Multiple Values Logic'),
                        'title'    => __('Multiple Values Logic'),
                        'values'   => $this->multipleValuesLogic->toOptionArray(),
                    ]
                );
            }else{
                $fieldsetFiltering->addField(
                    'is_multiselect',
                    'select',
                    [
                        'name' => 'is_multiselect',
                        'label' => __('Allow Multiselect'),
                        'title' => __('Allow Multiselect'),
                        'note' => __('When multiselect option is disabled it follows the category page'),
                        'values' => $yesnoSource,
                    ]
                );
            }
            /*$dependence->addFieldMap(
                $multiselectField->getHtmlId(),
                $multiselectField->getName()
            )->addFieldDependence(
                $multiselectField->getName(),
                $displayModeField->getName(),
                DisplayMode::MODE_DEFAULT
            );*/

            /*$dependence->addFieldMap(
                $useAndLogicField->getHtmlId(),
                $useAndLogicField->getName()
            )->addFieldDependence(
                $useAndLogicField->getName(),
                $multiselectField->getName(),
                1
            )->addFieldDependence(
                $useAndLogicField->getName(),
                $displayModeField->getName(),
                DisplayMode::MODE_DEFAULT
            );*/

            $fieldsetDisplayProperties->addField(
                'hide_one_option',
                'select',
                [
                    'name'     => 'hide_one_option',
                    'label'    => __('Hide filter when only one option available'),
                    'title'    => __('Hide filter when only one option available'),
                    'values'   => $yesnoSource,
                ]
            );
        } else {
            $useCurrencySymbolField = $fieldsetDisplayProperties->addField(
                'units_label_use_currency_symbol',
                'select',
                [
                    'name'     => 'units_label_use_currency_symbol',
                    'label'    => __('Measure Units'),
                    'title'    => __('Measure Units'),
                    'values'   => $this->measureUnitSource->toOptionArray(),
                ]
            );
            $dependence->addFieldMap(
                $useCurrencySymbolField->getHtmlId(),
                $useCurrencySymbolField->getName()
            );

            $unitsLabelField = $fieldsetDisplayProperties->addField(
                'units_label',
                'text',
                [
                    'name'     => 'units_label',
                    'label'    => __('Unit label'),
                    'title'    => __('Unit label'),
                ]
            );

            $dependence->addFieldMap(
                $unitsLabelField->getHtmlId(),
                $unitsLabelField->getName()
            );

            $dependence->addFieldDependence(
                $unitsLabelField->getName(),
                $useCurrencySymbolField->getName(),
                MeasureUnit::CUSTOM
            );

            $sliderStepField = $fieldsetDisplayProperties->addField(
                'slider_step',
                'text',
                [
                    'name'     => 'slider_step',
                    'label'    => __('Slider Step'),
                    'title'    => __('Slider Step'),
                ]
            );

            $dependence->addFieldMap(
                $sliderStepField->getHtmlId(),
                $sliderStepField->getName()
            )->addFieldDependence(
                $sliderStepField->getName(),
                $displayModeField->getName(),
                DisplayMode::MODE_SLIDER
            );
        }

        $this->setChild(
            'form_after',
            $dependence
        );


        $this->_eventManager->dispatch('clevershopby_attribute_form_tab_build_after', ['form' => $form, 'setting' => $this->setting]);

        $this->setForm($form);
        $data = $this->setting->getData();
        if(isset($data['slider_step'])) {
            $data['slider_step'] = round($data['slider_step'], 4);
        }
        $form->setValues($data);
        return parent::_prepareForm();
    }

    protected function prepareFilterSetting()
    {
        if ($this->attributeObject->getId()) {
            $filterCode = 'attr_' . $this->attributeObject->getAttributeCode();
            $this->setting->load($filterCode, 'filter_code');
            $this->setting->setFilterCode($filterCode);
        }
    }
}
