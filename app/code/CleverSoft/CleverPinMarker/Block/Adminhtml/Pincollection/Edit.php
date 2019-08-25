<?php

namespace CleverSoft\CleverPinMarker\Block\Adminhtml\Pincollection;

class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
	protected $_coreRegistry = null;
	public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }
	
	protected function _construct()
    {
        $this->_objectId = 'id';
        $this->_blockGroup = 'CleverSoft_CleverPinMarker';
        $this->_controller = 'adminhtml_pincollection';
		
        parent::_construct();

        if ($this->_isAllowedAction('CleverSoft_CleverPinMarker::save')) {
            $this->buttonList->update('save', 'label', __('Save'));
            $this->buttonList->add(
                'saveandcontinue',
                [
                    'label' => __('Save and Continue Edit'),
                    'class' => 'save',
                    'data_attribute' => [
                        'mage-init' => [
                            'button' => ['event' => 'saveAndContinueEdit', 'target' => '#edit_form'],
                        ],
                    ]
                ],
                -100
            );
        } else {
            $this->buttonList->remove('save');
        }
		
		$this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('block_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'block_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'block_content');
                }
            }
        ";
        $this->buttonList->remove('delete');
    }
	public function getHeaderText()
    {
        if ($this->_coreRegistry->registry('pincollection')->getId()) {
            return __("Edit '%1'", $this->escapeHtml($this->_coreRegistry->registry('pincollection')->getTitle()));
        } else {
            return __('Edit Collection');
        }
    }
	protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }
	protected function _getSaveAndContinueUrl()
    {
        return $this->getUrl('CleverPinMarker/*/save', ['_current' => true, 'back' => 'edit', 'active_tab' => '']);
    }
}