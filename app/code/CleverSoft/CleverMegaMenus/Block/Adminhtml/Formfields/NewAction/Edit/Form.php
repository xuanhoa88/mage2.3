<?php
/**
 * @category    CleverSoft
 * @package     CleverMegaMenus
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */
 
namespace CleverSoft\CleverMegaMenus\Block\Adminhtml\Formfields\NewAction\Edit;
class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * Initialise form fields
     *
     * @return void
     */
	protected function _prepareForm()
    {
		$data = $this->_coreRegistry->registry('megamenu');
		$form = $this->_formFactory->create(
			[
                'data' => [
                    'id' => 'edit_form',
                    'action' => $this->getData('action'),
                    'method' => 'post',
                    'enctype' => 'multipart/form-data']
            ]
		);
		$fieldset = $form->addFieldset('clever_fieldset',['legend' => __('General Settings')]);
		if ($data->getId()) {
			$fieldset->addField('id', 'hidden', ['name' => 'id']);
		}
		$fieldset->addField(
			'name',
			'text',
			['name' => 'name', 'label' => __('Name'), 'title' => __('Name'), 'required' => true]
		);
		$fieldset->addField(
			'identifier',
			'text',
			['name' => 'identifier', 'label' => __('Identifier'), 'title' => __('Identifier'), 'required' => true]
		);

		$fieldset->addField(
			'direction',
			'select',
			[
				'label' => __('Menu Direction'),
				'title' => __('Menu Direction'),
				'name' => 'direction',
				'required' => true,
				'values' => ['0' => __('Horizontal'), '1' => __('Vertical')],
			]
		);
		$fieldset->addField(
			'custom_class',
			'text',
			[
				'label' => __('Wrapper Custom Class'),
				'title' => __('Wrapper Custom Class'),
				'name' => 'css_class',
				'required' => false
			]
		);
		$fieldset->addField(
			'animation',
			'select',
			[
				'label' => __('Animation'),
				'title' => __('Animation'),
				'name' => 'animation',
				'values' => ['show' => 'Normal', 'fade' => __('Fade'),'slide' => __('Slide')],
				'required' => false
			]
		);
		if($style = $data->getData('menustyles')){
			$style = json_decode($style);
            $data->setData('custom_class', isset($style->custom_class) ? $style->custom_class : (isset($style->css_class) ? $style->css_class : ''));
            $data->setData('animation',isset($style->animation) ? $style->animation : (isset($style->dropdown_animation) ? $style->dropdown_animation : '')) ;
		}
		$fieldset->addField(
			'is_active',
			'select',
			[
				'label' => __('Active'),
				'title' => __('Active'),
				'name' => 'is_active',
				'required' => true,
                'values' => ['0' => __('No'), '1' => __('Yes')]
			]
		);
        if(!$data->getId()){
            $data->setData('is_active',1);
        }
        $fields = $fieldset->addField('menu-content', 'hidden', ['name' => 'menucontent', 'label'=>'Menu Content']);
		$fields->setRenderer($this->getLayout()->createBlock('CleverSoft\CleverMegaMenus\Block\Adminhtml\Formfields\NewAction\Fields\Elements'));

		$form->setUseContainer(true);
        $form->setHtmlIdPrefix('clever_');
        $form->setValues($data->getData());
		$this->setForm($form);

		return parent::_prepareForm();
	}
}