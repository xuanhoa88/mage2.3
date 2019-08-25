<?php
/**
 * Copyright © 2017 CleverSoft, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace CleverSoft\CleverShopByBrand\Controller\Adminhtml\Index;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\TestFramework\ErrorLog\Logger;
use Magento\Framework\Exception\LocalizedException;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Option;

class Save extends \Magento\Backend\App\Action
{
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Option\CollectionFactory $optionCollectionFactory,
        \CleverSoft\CleverShopByBrand\Model\ResourceModel\Brand\CollectionFactory $brandCollectionFactory
    )
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->optionCollectionFactory = $optionCollectionFactory;
        $this->_brandCollectionFactory = $brandCollectionFactory;
    }

	protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('CleverSoft_CleverShopByBrand::shopbybrand');
    }
	public function execute()
    {
        $data = $this->getRequest()->getPostValue();
		$storeId = (int)$this->getRequest()->getParam('store');

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
		
        $resultRedirect = $this->resultRedirectFactory->create();
		
        if ($data) {
			$id = $this->getRequest()->getParam('id');
            $model = $this->_objectManager->create('CleverSoft\CleverShopByBrand\Model\Brand');
			
			$this->_eventManager->dispatch(
				'product_brand_prepare_save',
				['product_brand' => $model, 'request' => $this->getRequest()]
			);
            
			$model->setStoreId($storeId);

			if ($id) {
                $model->load($id);
            } else {
                $lastOption = $this->optionCollectionFactory->create()->getLastItem()->getId();
                $countBrand = count($this->_brandCollectionFactory->create());
                $model->setId($lastOption + $countBrand);
            }
            
            $resource = $this->_objectManager->get('Magento\Framework\App\ResourceConnection');
            $connection = $resource->getConnection();
            $tableName = $resource->getTableName('eav_attribute_option_value');

            $option_id = $id;
            $sql = "select * FROM " . $tableName . " where option_id=".$option_id;
            $result = $connection->fetchAll($sql);

            $data['brand_label'] = $result[0]['value'];
            if(!$data['url_key']) {
                $data['url_key'] = $this->Sluggify($data['brand_label']);
            }
            $model->addData($data);
			
            try {
                $model->save();
                $this->messageManager->addSuccess(__('Brand successfully saved!'));
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
                $this->messageManager->addException($e, __('Something went wrong while saving the brand.'));
            }

            $this->_getSession()->setFormData($data);
            return $resultRedirect->setPath('*/*/edit', ['id' => $model->getId() ]);
        }
        return $resultRedirect->setPath('*/*/');
    }

    function Sluggify($url)
    {
        # Prep string with some basic normalization
        $url = strtolower($url);
        $url = strip_tags($url);
        $url = stripslashes($url);
        $url = html_entity_decode($url);

        # Remove quotes (can't, etc.)
        $url = str_replace('\'', '', $url);

        # Replace non-alpha numeric with hyphens
        $match = '/[^a-z0-9]+/';
        $replace = '-';
        $url = preg_replace($match, $replace, $url);

        $url = trim($url, '-').'.html';

        return $url;
    }
}
