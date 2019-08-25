<?php
/**
 * @category    CleverSoft
 * @package     Base
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author      ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */
 
namespace CleverSoft\CleverTheme\Plugin;

class FooterDesign
{
    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \CleverSoft\CleverTheme\Model\Cssgen\FooterGenerator $generator,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory,
        \CleverSoft\CleverTheme\Model\ResourceModel\FooterBlock\CollectionFactory $collectionFactory
    )
    {
        $this->request = $request;
        $this->_messageManager = $messageManager;
        $this->_cssGenerator = $generator;
        $this->_objectManager = $objectManager;
        $this->_mediaDirectory = $filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
        $this->_fileUploaderFactory = $fileUploaderFactory;
        $this->_collectionFactory = $collectionFactory;
    }


    public function beforeExecute(
        \Magento\Config\Controller\Adminhtml\System\Config\Save $subject
    )
    {
        $section = $this->request->getParam('section');
        $storeId = $this->request->getParam('store');

        $dataDesktop = $this->request->getParam('footerbuilder-desktop-data');
        $dataMobile = $this->request->getParam('footerbuilder-mobile-data');

        $model = $this->_objectManager->create('CleverSoft\CleverTheme\Model\FooterBlock');
        $templateModel = $this->_objectManager->create('CleverSoft\CleverTheme\Model\FooterTemplate');
        $id = $this->_collectionFactory->create()->addFieldToFilter('store_id',$storeId)->getFirstItem()->getId();
        if ($id) {
            $model->load($id);
        }

        $settings = $this->request->getParam('settings');
        $presetSettings = '';
        if($settings) {
            if (isset($settings['footer']['_customize-radio-footer_template_saved']) && strpos($settings['footer']['_customize-radio-footer_template_saved'], 'preset_') !== false) {
                $key = str_replace("preset_","", $settings['footer']['_customize-radio-footer_template_saved']);
                $templateData = $templateModel->load($key);
                $dataDesktop = $templateData->getData('footer_desktop_data');
                $dataMobile = $templateData->getData('footer_mobile_data');
                $presetSettings = $templateData->getFooterStyle();
            }
            
            $templates = array();
            if (isset($settings['footer']['_customize-radio-footer_template_deleted']) && $settings['footer']['_customize-radio-footer_template_deleted'] != '') {
                $templateModel->load($settings['footer']['_customize-radio-footer_template_deleted']);
                $templateModel->delete();
            }
            if (isset($settings['footer']['_customize-input-footer_template_name']) && $settings['footer']['_customize-input-footer_template_name'] !== '') {
                $templates[] = array("name" => $settings['footer']['_customize-input-footer_template_name'], "value"=>array("desktop_data" => $dataDesktop, "mobile_data" => $dataMobile));
                $templateModel->setData('template_name', $settings['footer']['_customize-input-footer_template_name']);
                $templateModel->setData('footer_desktop_data', $dataDesktop);
                $templateModel->setData('footer_mobile_data', $dataMobile);
                $templateModel->setData('footer_style', $settings = json_encode($settings));
                try {
                    $templateModel->save();
                } catch (\Exception $e) {
                    $this->_messageManager->addException($e);
                }
            }
            $settings = json_encode($settings);

            if ($settings) {
                $model->setFooterStyle($settings);
            }

            if($presetSettings) {
                $model->setFooterStyle($presetSettings);
            }
        }

        if ($dataDesktop || $dataMobile) {
            $model->setData('store_id',$storeId);
            $model->setData('footer_desktop_data', $dataDesktop);
            $model->setData('footer_mobile_data', $dataMobile);
        }

        try {
            $model->save();
        } catch (\Exception $e) {
            $this->_messageManager->addException($e);
        }
        
        if ($section == 'cleversofttheme_design')
        {
            $this->_cssGenerator->generateCss($storeId);
        }
    }
}
