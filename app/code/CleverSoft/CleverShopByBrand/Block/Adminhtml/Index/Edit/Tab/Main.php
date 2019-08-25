<?php
/**
 * Copyright © 2017 CleverSoft, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace CleverSoft\CleverShopByBrand\Block\Adminhtml\Index\Edit\Tab;
	
use Magento\Backend\Block\Widget\Form;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Theme\Helper\Storage;

class Main extends Generic implements TabInterface
{
    
	public function getTabLabel()
	{
		return __('Brand Information');
	}
    
	public function getTabTitle()
	{
		return __('Brand Information');
	}
    
	public function canShowTab()
	{
		return true;
	}
    
	public function isHidden()
	{
		return false;
	}
    
	protected function _prepareForm()
	{
		$model = $this->_coreRegistry->registry('brand');
		$form = $this->_formFactory->create();
		$form->setHtmlIdPrefix('brand_');
        $scopeConfig = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\App\Config\ScopeConfigInterface');
		$fieldset = $form->addFieldset(
			'base_fieldset',
			['legend' => __('General Information'), 'class' => 'fieldset-wide']
		);
		if ($this->getRequest()->getParam('id')) {
			$fieldset->addField('id', 'hidden', ['name' => 'id']);
			$model->addData([
				'id' => $this->getRequest()->getParam('id')
			]);

			$model->addData([
				'is_actived' => 1,
				'is_featured' => 1
			]);
		}

        $field = $fieldset->addField(
            'logo',
            'hidden',
            ['name' => 'logo', 'label' => __('Logo'), 'title' => __('Logo'), 'required' => false, 'class' => 'input-image', 'onchange' => 'changePreviewImage(this)']
        );

        $renderer = $this->getLayout()->createBlock(
            'CleverSoft\CleverShopByBrand\Block\Adminhtml\CleverShopByBrand\AbstractHtmlField\Image'
        );

        $field->setRenderer($renderer);

		$fieldset->addField(
			'description',
			'editor',
			['name' => 'description', 'config' => \Magento\Framework\App\ObjectManager::getInstance()->create('Magento\Cms\Model\Wysiwyg\Config')->getConfig(), 'label' => __('Description'), 'title' => __('Description'), 'required' => false]
		);

		$fieldset->addField(
			'is_featured',
			'select',
			['name' => 'is_featured', 'label' => __('Is Featured'), 'title' => __('Is Featured'),
				'required' => true,
				'options' => ['1' => __('Yes'), '0' => __('No')]
			]
		);

        $fieldset->addField(
            'is_actived',
            'select',
            ['name' => 'is_actived', 'label' => __('Active'), 'title' => __('Active'),
                'required' => true,
                'options' => ['1' => __('Yes'), '0' => __('No')]
            ]
        );

		$form->setDataObject($model);
		$form->setValues($model->getData());
		$this->setForm($form);
		
		return parent::_prepareForm();
	}
}