<?php
/**
 * @category    CleverSoft
 * @package     CleverInstagram
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverInstagram\Block\Widget\Adminhtml;

use Magento\Backend\Block\Template;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;

/**
 * Class Conditions
 */
class Accesstoken extends Template implements RendererInterface
{

    /**
     * @var \Magento\Framework\Data\Form\Element\Factory
     */
    protected $elementFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var AbstractElement
     */
    protected $element;

    /**
     * @var \Magento\Framework\Data\Form\Element\Text
     */
    protected $input;

    /**
     * @var string
     */
    protected $_template = 'instagram/widget/accesstoken.phtml';

    protected $_productAttributeRepository;
    /**
     * @param \Magento\Framework\Data\Form\Element\Factory $elementFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Backend\Block\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Data\Form\Element\Factory $elementFactory,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->elementFactory = $elementFactory;
        $this->registry = $registry;
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->_productAttributeRepository =  $objectManager->create('Magento\Catalog\Model\Product\Attribute\Repository');
        parent::__construct($context, $data);
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $widget = $this->registry->registry('current_widget_instance');
        if ($widget) {
            $widgetParameters = $widget->getWidgetParameters();
            if (isset($widgetParameters['conditions'])) {
                $this->rule->loadPost($widgetParameters);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function render(AbstractElement $element)
    {
        $this->element = $element;
        return $this->toHtml();
    }

    /**
     * @return AbstractElement
     */
    public function getElement()
    {
        return $this->element;
    }

    /**
     * @return string
     */
    public function getHtmlId()
    {
        return $this->getElement()->getContainer()->getHtmlId();
    }

    /**
     * @return string
     */
    public function getInputHtml()
    {
        $widget = $this->registry->registry('current_widget_instance');
        if ($widget) {
            return $widget->getWidgetParameters();
        }
        return '';
    }

    public function getTokenAndIdInStoreConfig(){
        $_objectManager = \Magento\Framework\App\ObjectManager::getInstance(); //instance of\Magento\Framework\App\ObjectManager
        $configManager = $_objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface');
        $storeConfig = array();
        $storeConfig['accessTokenConfig'] = $configManager->getValue('instagram/general/accessToken');
        $storeConfig['UserIdConfig'] = $configManager->getValue('instagram/general/userid');
        $storeConfig['HashTagConfig'] = $configManager->getValue('instagram/general/hash_tag');
        $storeConfig['ModeImgConfig'] = $configManager->getValue('instagram/general/modetakeimg');
        $storeConfig['LatitudeConfig'] = $configManager->getValue('instagram/general/latitude');
        $storeConfig['LongtitudeConfig'] = $configManager->getValue('instagram/general/longitude');
        return $storeConfig;
    }
}
