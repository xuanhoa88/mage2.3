<?php
/**
 * @category    CleverSoft
 * @package     CleverLayeredNavigation
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverLayeredNavigation\Controller\Adminhtml\Option;

class Save extends \CleverSoft\CleverLayeredNavigation\Controller\Adminhtml\Option
{
    public function execute()
    {
        $filterCode = $this->getRequest()->getParam('filter_code');
        $optionId = $this->getRequest()->getParam('option_id');
        $storeId = $this->getRequest()->getParam('store', 0);
        /** @var \CleverSoft\CleverLayeredNavigation\Model\OptionSetting $model */
        if ($data = $this->getRequest()->getPostValue()) {
            try {

                /** @var \CleverSoft\CleverLayeredNavigation\Model\OptionSetting $model */
                $model = $this->_objectManager->create('CleverSoft\CleverLayeredNavigation\Model\OptionSetting');
                $inputFilter = new \Zend_Filter_Input(
                    [],
                    [],
                    $data
                );
                $data = $inputFilter->getUnescaped();

                $model = $model->getByParams($filterCode, $optionId, $storeId);

                if(!$model->getId()) {
                    $model
                        ->setValue($optionId)
                        ->setFilterCode($filterCode)
                        ->setStoreId($storeId);
                } elseif($model->getStoreId() != $storeId) {
                    $model->setId(null);
                    $model->isObjectNew(true);
                    $model->setStoreId($storeId);
                }
                $useDefaultImage = false;
                if($storeId > 0 && isset($data['use_default']) && count($data['use_default']) > 0) {
                    $defaultModel = $model->getByParams($filterCode, $optionId, 0);
                    foreach($data['use_default'] as $field) {
                        $data[$field] = $defaultModel->getData($field);
                        if($field == 'image') {
                            $useDefaultImage = true;
                        }
                    }
                }

                if($useDefaultImage || isset($data['image_delete'])) {
                    $model->removeImage();
                    $data['image'] = '';
                }



                if(!$useDefaultImage) {
                    try {
                        $imageName = $model->uploadImage('image');
                        $data['image'] = $imageName;
                    } catch(\Exception $e) {
                        if($e->getCode() != \Magento\Framework\File\Uploader::TMP_NAME_EMPTY && $e->getMessage() != '$_FILES array is empty') {
                            throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()), $e);
                        }
                    }
                }

                if($storeId == 0) {
                    $listDependencyModels = $model->getDependencyModels($filterCode, $optionId);
                    $defaultData = $model->getData();
                    unset($defaultData['option_setting_id'], $defaultData['store_id'], $defaultData['value'], $defaultData['filter_code']);
                    foreach($listDependencyModels as $dependencyModel) {
                        /** @var  \CleverSoft\CleverLayeredNavigation\Model\OptionSetting $dependencyModel */
                        foreach($defaultData as $key=>$value) {
                            if(isset($data[$key]) && $dependencyModel->getData($key) != $data[$key] && $dependencyModel->getData($key) == $value) {
                                $dependencyModel->setData($key, $data[$key]);
                            }
                        }
                        $dependencyModel->save();
                    }
                }
                $model->addData($data);
                $session = $this->_objectManager->get('Magento\Backend\Model\Session');
                $session->setPageData($model->getData());

                $model->save();
                $this->messageManager->addSuccess(__('You saved the item.'));
                $session->setPageData(false);
                //$this->_redirect('*/*/settings', ['option_id'=>(int)$optionId, 'filter_code'=>$filterCode]);
                $this->_forward('settings');
                return;

            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
                $this->_forward('settings');
                return;
                //return $this->_redirect('*/*/settings', ['option_id'=>(int)$optionId, 'filter_code'=>$filterCode]);
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                $this->messageManager->addError(
                    __('Something went wrong while saving the item data. Please review the error log.')
                );
                $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
                $this->_objectManager->get('Magento\Backend\Model\Session')->setPageData($data);
                $this->_forward('settings');
                return;
                //return $this->_redirect('*/*/settings', ['option_id'=>(int)$optionId, 'filter_code'=>$filterCode]);
            }
        }
        $this->_forward('settings');
        return;
        //return $this->_redirect('*/*/settings', ['option_id'=>(int)$optionId, 'filter_code'=>$filterCode]);
    }

}
