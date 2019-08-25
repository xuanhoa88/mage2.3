<?php
/**
 * Copyright © 2017 CleverSoft, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace CleverSoft\CleverPinMarker\Controller\Adminhtml\Collection;
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
        return $this->_authorization->isAllowed('CleverSoft_CleverPinMarker::pincollection');
    }
	public function execute()
    {
        $data = $this->getRequest()->getPostValue();

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
		
        $resultRedirect = $this->resultRedirectFactory->create();
		
        if ($data) {
            if(isset($data['pin_ids'])) {
                
                $data['pin_ids'] = implode(",",$data['pin_ids']);
            }
			$id = $this->getRequest()->getParam('id');
            $model = $this->_objectManager->create('CleverSoft\CleverPinMarker\Model\PinCollection');
			
			$this->_eventManager->dispatch(
				'product_pincollection_prepare_save',
				['product_pincollection' => $model, 'request' => $this->getRequest()]
			);
            

			if ($id) {
                $model->load($id);
				if ($id != $model->getId()) {
					throw new LocalizedException(__('Wrong collection specified.'));
				}
            }
            
			$model->addData($data);
			
            try {
                $model->save();
                
                $type = 'full_page';
                $this->_cacheTypeList->cleanType($type);
                $this->messageManager->addSuccess(__('Collection successfully saved!'));
				$this->_objectManager->get('Magento\Backend\Model\Session')->setPageData(false);
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', [
                        'id' => $model->getId(),
                        '_current' => true
                    ]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
				$this->messageManager->addError($e->getMessage());
                $this->messageManager->addException($e, __('Something went wrong while saving the collection.'));
            }

            $this->_getSession()->setFormData($data);
            return $resultRedirect->setPath('*/*/edit', ['id' => $model->getId() ]);
        }
        return $resultRedirect->setPath('*/*/');
    }
}
