<?php
/**
 * Copyright © 2017 CleverSoft, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace CleverSoft\CleverPinMarker\Block\Adminhtml\Index\Edit\Tab;
	
use Magento\Backend\Block\Widget\Form;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Theme\Helper\Storage;

class Main extends Generic implements TabInterface
{
    
	public function getTabLabel()
	{
		return __('Pin Information');
	}
    
	public function getTabTitle()
	{
		return __('Pin Information');
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
		$model = $this->_coreRegistry->registry('pinmarker');
		$form = $this->_formFactory->create();
		$form->setHtmlIdPrefix('pinmarker_');
        $scopeConfig = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\App\Config\ScopeConfigInterface');
		$fieldset = $form->addFieldset(
			'base_fieldset',
			['legend' => __('General Information'), 'class' => 'fieldset-wide']
		);
		if ($model->getId()) {
			$fieldset->addField('id', 'hidden', ['name' => 'id']);
		}else{
			$model->addData([
				'is_actived' => 1
			]);
		}

		$fieldset->addField(
			'pinmarker_label',
			'text',
			['name' => 'pinmarker_label', 'required' => true, 'label' => __('Label'), 'title' => __('Label')]
		);

		$fieldset->addField(
            'is_actived',
            'select',
            ['name' => 'is_actived', 'label' => __('Active'), 'title' => __('Active'),
                'required' => true,
                'options' => ['1' => __('Yes'), '0' => __('No')]
            ]
        );

        $field = $fieldset->addField(
            'image',
            'hidden',
            ['name' => 'image', 'required' => false, 'class' => 'input-image', 'onchange' => 'changePreviewImage(this)']
        );

        $renderer = $this->getLayout()->createBlock(
            'CleverSoft\CleverPinMarker\Block\Adminhtml\AbstractHtmlField\Image'
        );

        $field->setRenderer($renderer);

		$form->setDataObject($model);
		$form->setValues($model->getData());
		$this->setForm($form);
		
		return parent::_prepareForm();
	}
    
	public function getWysiwygConfig()
	{
		$config = new \Magento\Framework\DataObject();
		$config->setData([
			'enabled' => true,
			'hidden' => false,
			'popup_css' => $this->_assetRepo->getUrl(
				'mage/adminhtml/wysiwyg/tiny_mce/themes/advanced/skins/default/dialog.css'
			),
			'content_css' => $this->_assetRepo->getUrl(
				'mage/adminhtml/wysiwyg/tiny_mce/themes/advanced/skins/default/content.css'
			),
		]);
		return $config;	
	}
}