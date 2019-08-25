<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CleverSoft\CleverBuilder\Controller\Index;

use CleverSoft\CleverBuilder\Helper\Data as DataHelper;

use CleverSoft\CleverBuilder\Helper\Ajax\AjaxStylesForm as AjaxStylesForm;
use CleverSoft\CleverBuilder\Helper\Ajax\AjaxBuilderContent as AjaxBuilderContent;
use CleverSoft\CleverBuilder\Helper\Ajax\AjaxWidgetForm as AjaxWidgetForm;
use CleverSoft\CleverBuilder\Helper\Ajax\AjaxUpdateDbPanelsData;

/** @noinspection PhpUnnecessaryFullyQualifiedNameInspection */
class Ajax extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\Controller\Result\ForwardFactory
     */
    protected $resultForwardFactory;
    /**
     * @var \CleverSoft\CleverBuilder\Helper\Data
     */
    protected $_dataHelper;
    /**
     * @var \CleverSoft\CleverBuilder\Helper\Ajax\AjaxStylesForm
     */
    protected $_ajaxStylesHelper;
    /**
     * @var \CleverSoft\CleverBuilder\Helper\Ajax\AjaxBuilderContent
     */
    protected $_ajaxBuilderContent;
    /**
     * @var \CleverSoft\CleverBuilder\Helper\Ajax\AjaxWidgetForm
     */
    protected $_ajaxWidgetForm;
    /**
     * @var \CleverSoft\CleverBuilder\Helper\Ajax\AjaxUpdateDbPanelsData
     */
    protected $_ajaxUpdateDbPanelsData;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\ForwardFactory $resultForwardFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        DataHelper $dataHelper,
        AjaxStylesForm $ajaxStylesForm,
        AjaxBuilderContent $ajaxBuilderContent,
        AjaxWidgetForm $ajaxWidgetForm,
        AjaxUpdateDbPanelsData $ajaxUpdateDbPanelsData,
        \Magento\Framework\Controller\Result\ForwardFactory $resultForwardFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \CleverSoft\CleverBuilder\Model\Cssgen\Generator $generator
    ) {
        $this->resultForwardFactory = $resultForwardFactory;
        $this->_dataHelper = $dataHelper;
        $this->_ajaxStylesHelper = $ajaxStylesForm;
        $this->_ajaxWidgetForm = $ajaxWidgetForm;
        $this->_ajaxBuilderContent = $ajaxBuilderContent;
        $this->_ajaxUpdateDbPanelsData = $ajaxUpdateDbPanelsData;
        $this->_cssGenerator = $generator;
        $this->_storeManager = $storeManager;
        parent::__construct($context);
    }
    /**
     * process all ajax request from builder
     *
     */
    public function execute($coreRoute = null)
    {
        $post = $this->getRequest()->getParams();
        $storeId = $this->_storeManager->getStore()->getId();

        if (isset($post['action'])) {
            switch (trim($post['action'])) {
                case 'so_panels_style_form' :
                    echo $this->_ajaxStylesHelper->getHtml();
                    break;
                case 'so_panels_widget_form' :
                    echo $this->_ajaxWidgetForm->getHtml();
                    break;
                case 'so_panels_save_database':
                    if (isset($post['panels_data'])) {
                        $this->_cssGenerator->generateCss($storeId, $post['panels_data'], $post['page_id']);
                    }
                    echo $this->_ajaxUpdateDbPanelsData->savePanelsData($post);
                    break;
                case 'so_panels_update_database':
                    if (isset($post['panels_data'])) {
                        $this->_cssGenerator->generateCss($storeId, $post['panels_data'], $post['page_id']);
                    }
                    echo $this->_ajaxUpdateDbPanelsData->updatePanelsData($post);
                    break;
                case 'so_panels_prebuilt_database':
                    echo $this->_ajaxUpdateDbPanelsData->prebuiltPanelsData($storeId,$post);
                    break;
                case 'so_panels_save_template':
                    echo $this->_ajaxUpdateDbPanelsData->saveTemplate($post);
                    break;
                case 'so_panels_remove_template':
                    echo $this->_ajaxUpdateDbPanelsData->removeTemplate($post);
                    break;
                case 'so_panels_logout':
                    try{
                        $this->_dataHelper->setCustomerStatusPanel(0);
                        echo '';
                        die();
                    }catch (\Exception $e) {
                        $this->_logger->critical($e);
                    }
                    break;
                default:
                    break;
            }
        }

        exit;
    }
}
