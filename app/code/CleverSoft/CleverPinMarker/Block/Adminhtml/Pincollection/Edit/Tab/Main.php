<?php
/**
 * Copyright © 2017 CleverSoft, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace CleverSoft\CleverPinMarker\Block\Adminhtml\Pincollection\Edit\Tab;
	
use Magento\Backend\Block\Widget\Form;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Theme\Helper\Storage;

class Main extends Generic implements TabInterface
{
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \CleverSoft\CleverPinMarker\Model\Config\Source\DisplayType $displayType,
        \CleverSoft\CleverPinMarker\Model\Config\Source\Column $column,
        \Magento\Config\Model\Config\Structure\Element\Dependency\FieldFactory $fieldFactory,
        array $data = []
    ) {
        $this->_displayType = $displayType;
        $this->_column = $column;
        $this->_fieldFactory = $fieldFactory;
        parent::__construct($context, $registry, $formFactory, $data);
    }

	public function getTabLabel()
	{
		return __('Collection Information');
	}
    
	public function getTabTitle()
	{
		return __('Collection Information');
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
		$model = $this->_coreRegistry->registry('pincollection');
		$form = $this->_formFactory->create();
		$form->setHtmlIdPrefix('pincollection_');
        $scopeConfig = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\App\Config\ScopeConfigInterface');
		$fieldset = $form->addFieldset(
			'base_fieldset',
			['legend' => __('General Information'), 'class' => 'fieldset-wide']
		);
		if ($model->getId()) {
			$fieldset->addField('id', 'hidden', ['name' => 'id']);
		}else{
			$model->addData([
				'is_actived' => 1,
				'gutter_width' => 10
			]);
		}

		$fieldset->addField(
			'collection_name',
			'text',
			['name' => 'collection_name', 'required' => true, 'label' => __('Name'), 'title' => __('Name')]
		);

		$fieldset->addField(
			'display_type',
			'select',
			['name' => 'display_type', 'required' => false, 'label' => __('Display Type'), 'title' => __('Display Type'), 'values' => $this->_displayType->toOptionArray()]
		);

		$fieldset->addField(
            'auto_play',
            'select',
            ['name' => 'auto_play', 'label' => __('Auto Play'), 'title' => __('Auto Play'),
                'required' => false,
                'options' => ['1' => __('Yes'), '0' => __('No')]
            ]
        );

        $fieldset->addField(
            'arrow',
            'select',
            ['name' => 'arrow', 'label' => __('Enable Arrow'), 'title' => __('Arrow'),
                'required' => false,
                'options' => ['1' => __('Yes'), '0' => __('No')]
            ]
        );

        $fieldset->addField(
            'dot',
            'select',
            ['name' => 'dot', 'label' => __('Enable Dot'), 'title' => __('Dot'),
                'required' => false,
                'options' => ['1' => __('Yes'), '0' => __('No')]
            ]
        );

		$fieldset->addField(
			'column',
			'select',
			['name' => 'column', 'required' => false, 'label' => __('Column'), 'title' => __('Column'), 'values' => $this->_column->toOptionArray()]
		);

		$fieldset->addField(
			'gutter_width',
			'text',
			['name' => 'gutter_width', 'required' => false, 'label' => __('Gutter Width (Type Number Only)'), 'title' => __('Gutter Width (Type Number Only)')]
		);

		$form->setDataObject($model);
		$form->setValues($model->getData());
		$this->setForm($form);
		
		return parent::_prepareForm();
	}
}