<?php
/**
 * @category    CleverSoft
 * @package     CleverMegaMenus
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverMegaMenus\Block\Adminhtml\Formfields;
class NewAction extends \Magento\Backend\Block\Widget\Form\Container{
    /*
     * */
	protected $_coreRegistry = null;
    protected $_objectId = 'id';
    protected $_blockGroup = 'CleverSoft_CleverMegaMenus';
    protected $_controller = 'adminhtml_formfields_newAction';
    /*
     *
     * */
	public function __construct(
		\Magento\Backend\Block\Widget\Context $context,
		\Magento\Framework\Registry $registry,
		array $data = []
	){
		$this->_coreRegistry = $registry;
		parent::__construct($context, $data);
	}
    /*
     * add more control buttons here
     * follow @class Magento\Backend\Block\Widget\Form\Container
     * */
	protected function _construct()
	{
		parent::_construct();
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
            ]
        );
		if ($this->_coreRegistry->registry('megamenu')->getId()) {
			$this->buttonList->add(
				'duplicate',
				[
					'label' => __('Duplicate'),
					'data_attribute' => [
						'mage-init' => [
							'button' => ['event' => 'saveAndContinueEdit', 'target' => '#edit_form', 'event_data' => ['type' => 'duplicate']],
						],
					]
				]
			);
		}
        /*
         * change lable of some buttons to uppercase
         */
        $this->buttonList->update('save', 'label', __('Save'));
		$this->buttonList->remove('reset');
		$this->buttonList->update('delete', 'label', __('Delete'));
	}
    /*
     * get back to menu manager (listing)
     * */
	public function getBackUrl() {
		return $this->getUrl('megamenu/*/manager');
	}

    /*
     * define we are creating new menu or edit an existing one.
     */
	public function getHeaderText()
	{
		if ($this->_coreRegistry->registry('megamenu')->getId()) {
			return __("Edit Menu '%1'", $this->escapeHtml($this->_coreRegistry->registry('megamenu')->getName()));
		} else {
			return __('New Menu');
		}
	}
	
}