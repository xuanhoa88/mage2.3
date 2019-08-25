<?php
/**
 * Copyright © 2017 CleverSoft, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace CleverSoft\CleverPinMarker\Controller\Adminhtml\Index;

use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Framework\Controller\ResultFactory;
use CleverSoft\CleverPinMarker\Controller\Adminhtml\AbstractMassAction;
use CleverSoft\CleverPinMarker\Model\ResourceModel\PinMarker\CollectionFactory;
use CleverSoft\CleverPinMarker\Model\PinMarkerFactory;

/**
 * Class MassDelete
 */
class MassStatus extends AbstractMassAction
{
    const ADMIN_RESOURCE = 'CleverSoft_CleverPinMarker::pinmarker';

    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        PinMarkerFactory $manageFactory,
        \Magento\Framework\Url $urlBuilder
    ) {
        parent::__construct($context, $filter);
        $this->collectionFactory = $collectionFactory;
        $this->model = $manageFactory;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    protected function massAction($collection)
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $status = (int) $this->getRequest()->getParam('is_actived');
        $itemsSelected = 0;
        foreach ($collection->getAllIds() as $itemId) {
            $model = $this->model->create()->load($itemId);
            $model->setData('is_actived', $status);
            $model->save();
            $itemsSelected++;
        }

        if ($itemsSelected) {
            $this->messageManager->addSuccess(__('A total of %1 pin(s) were updated.', $itemsSelected));
        } else {
            $this->messageManager->addErrorMessage('Something went wrong while update pin');
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath($this->getComponentRefererUrl());

        return $resultRedirect;
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed(self::ADMIN_RESOURCE);
    }
}