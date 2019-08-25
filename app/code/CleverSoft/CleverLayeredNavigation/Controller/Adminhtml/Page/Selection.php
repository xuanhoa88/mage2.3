<?php
/**
 * @category    CleverSoft
 * @package     CleverLayeredNavigation
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverLayeredNavigation\Controller\Adminhtml\Page;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Catalog\Model\Config as CatalogConfig;
use Magento\Catalog\Model\Product;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Registry as CoreRegistry;
use CleverSoft\CleverLayeredNavigation\Controller\RegistryConstants;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Selection
 *
 * @author Artem Brunevski
 */

class Selection extends Action
{
    /**
     * Core registry
     *
     * @var CoreRegistry
     */
    protected $_coreRegistry = null;

    /** @var CatalogConfig  */
    protected $_catalogConfig;

    /**
     * @var PageFactory
     */
    protected $_resultPageFactory;

    /** @var JsonFactory  */
    protected $_resultJsonFactory;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param JsonFactory $resultJsonFactory
     * @param CatalogConfig $catalogConfig
     * @param CoreRegistry $registry
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        JsonFactory $resultJsonFactory,
        CatalogConfig $catalogConfig,
        CoreRegistry $registry
    ){
        $this->_resultPageFactory = $resultPageFactory;
        $this->_resultJsonFactory = $resultJsonFactory;
        $this->_catalogConfig = $catalogConfig;
        $this->_coreRegistry = $registry;
        return parent::__construct($context);
    }
    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
{
    return $this->_authorization->isAllowed('CleverSoft_CleverLayeredNavigation::page');
}

    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        try {
            $attributeId = $this->getRequest()->getParam('id');
            $attributeIdx = $this->getRequest()->getParam('idx');

            $attribute = $this->_catalogConfig->getAttribute(Product::ENTITY, $attributeId);

            if (!$attribute->getId()){
                throw new LocalizedException(__('Attribute doesn\'t exists'));
            }
            $this->_coreRegistry->register(RegistryConstants::ATTRIBUTE, $attribute);
            $this->_coreRegistry->register(RegistryConstants::ATTRIBUTE_IDX, $attributeIdx);

            return $this->_resultPageFactory->create();
        } catch (LocalizedException $e) {
            $response = ['error' => true, 'message' => $e->getMessage()];
        } catch (\Exception $e) {
            $response = ['error' => true, 'message' => $e->getMessage() . __('We can\'t fetch attribute options.')];
        }

        $resultJson = $this->_resultJsonFactory->create();
        $resultJson->setData($response);
        return $resultJson;
    }

}