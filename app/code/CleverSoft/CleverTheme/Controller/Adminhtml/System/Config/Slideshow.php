<?php
/**
 * @category    CleverSoft
 * @package     CleverTheme
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverTheme\Controller\Adminhtml\System\Config;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use CleverSoft\CleverTheme\Helper\RenderUiComponent;

class Slideshow extends \Magento\Config\Block\System\Config\Form\Field implements ObserverInterface {
    /**
     * Get the button and scripts contents
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    protected $generatorContext;
    protected $observer;
    public function __construct(
        RenderUiComponent $renderUiComponent,
        EventObserver $observer
    ){
        $this->renderUiComponent = $renderUiComponent;
        $this->observer = $observer;
    }

    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element){
        // Remove scope label
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        return $this->execute($this->observer);
    }
    public function execute(EventObserver $observer) {
        return $this->renderUiComponent->renderUiComponent('slideshow_listing');
    }
}
