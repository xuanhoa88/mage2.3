<?php
/**
 * Copyright © 2017 CleverSoft, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace CleverSoft\CleverPinMarker\Controller\Adminhtml\Index;
use Magento\Backend\App\Action;
use Magento\Framework\View\Result\PageFactory;
use Magento\TestFramework\ErrorLog\Logger;
use Magento\Framework\Exception\LocalizedException;

class Save extends \Magento\Backend\App\Action
{
    public function __construct(
        Action\Context $context,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
    )
    {
        parent::__construct($context);
        $this->_cacheTypeList = $cacheTypeList;
    }
	protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('CleverSoft_CleverPinMarker::pinmarker');
    }
	public function execute()
    {
        $data = $this->getRequest()->getPostValue();
		$storeId = (int)$this->getRequest()->getParam('store');

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
		
        $resultRedirect = $this->resultRedirectFactory->create();
		
        if ($data) {
            if(isset($data['wpa_pin'])) {
                $data['wpa_pin'] = json_encode(array_values($data['wpa_pin']));
            }
            
			$id = $this->getRequest()->getParam('id');
            $model = $this->_objectManager->create('CleverSoft\CleverPinMarker\Model\PinMarker');
			
			$this->_eventManager->dispatch(
				'product_pinmarker_prepare_save',
				['product_pinmarker' => $model, 'request' => $this->getRequest()]
			);
            
			$model->setStoreId($storeId);

			if ($id) {
                $model->load($id);
				if ($id != $model->getId()) {
					throw new LocalizedException(__('Wrong pinmarker specified.'));
				}
            }
            
			$model->addData($data);
			
            try {
                $model->save();
                
                $type = 'full_page';
                $this->_cacheTypeList->cleanType($type);
                $this->messageManager->addSuccess(__('PinMarker successfully saved!'));
				$this->_objectManager->get('Magento\Backend\Model\Session')->setPageData(false);
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', [
                        'id' => $model->getId(),
                        '_current' => true,
                        'store' => $storeId
                    ]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
				$this->messageManager->addError($e->getMessage());
                $this->messageManager->addException($e, __('Something went wrong while saving the pinmarker.'));
            }

            $this->_getSession()->setFormData($data);
            return $resultRedirect->setPath('*/*/edit', ['id' => $model->getId() ]);
        }
        return $resultRedirect->setPath('*/*/');
    }
}
