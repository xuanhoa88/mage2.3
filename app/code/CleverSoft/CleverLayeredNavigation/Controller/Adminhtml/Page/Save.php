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

/**
 * Class Save
 *
 * @author Artem Brunevski
 */

class Save extends Action
{
    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('CleverSoft_CleverLayeredNavigation::page');
    }

    /**
     * @param $data
     * @param $key
     * @param string $delimiter
     */
    protected function _implodeMultipleData(&$data, $key, $delimiter = ',')
    {
        if (array_key_exists($key, $data)){
            $data[$key] = implode($delimiter, $data[$key]);
        } else {
            $data[$key] = null;
        }
    }

    /**
     * @param $data
     * @param $key
     */
    protected function _serializeMultipleData(&$data, $key)
    {
        if (array_key_exists($key, $data)) {
            $data[$key] = serialize($data[$key]);
        } else {
            $data[$key] = null;
        }
    }

    /**
     * @param $data
     */
    protected function _normalizeData(&$data)
    {
        if (array_key_exists('top_block_id', $data) && $data['top_block_id'] === ''){
            $data['top_block_id'] = null;
        }
        if (array_key_exists('bottom_block_id', $data) && $data['bottom_block_id'] === ''){
            $data['bottom_block_id'] = null;
        }
        $this->_implodeMultipleData($data, 'categories');
        $this->_serializeMultipleData($data, 'conditions');
    }

    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            /** @var \CleverSoft\CleverLayeredNavigation\Model\Page $model */
            $model = $this->_objectManager->create('CleverSoft\CleverLayeredNavigation\Model\Page');

            $id = $this->getRequest()->getParam('page_id');
            if ($id) {
                $model->load($id);
            }

            $this->_normalizeData($data);

            $model->setData($data);

            try {
                $model->save()
                    ->saveStores();

                $this->messageManager->addSuccess(__('You saved this page.'));
                $this->_objectManager->get('Magento\Backend\Model\Session')->setFormData(false);
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['id' => $model->getId(), '_current' => true]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving the page.'));
            }

            $this->_getSession()->setFormData($data);
            return $resultRedirect->setPath('*/*/edit', ['id' => $this->getRequest()->getParam('page_id')]);
        }
        return $resultRedirect->setPath('*/*/');
    }
}