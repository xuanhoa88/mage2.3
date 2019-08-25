<?php 
/**
 * @category    CleverSoft
 * @package     CleverMegaMenus
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverMegaMenus\Block\Adminhtml\Formfields\NewAction\Fields;
class Elements extends \Magento\Backend\Block\Widget\Form\Renderer\Fieldset\Element implements \Magento\Framework\Data\Form\Element\Renderer\RendererInterface {
    protected $_template = 'newaction/fields/element.phtml';
	public function __construct(\Magento\Backend\Block\Template\Context $context,array $data = []){
        parent::__construct($context, $data);	
    }
}
?>