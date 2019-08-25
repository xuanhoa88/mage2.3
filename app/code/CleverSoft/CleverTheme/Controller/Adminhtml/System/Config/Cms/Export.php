<?php
/**
 * @category    CleverSoft
 * @package     CleverTheme
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverTheme\Controller\Adminhtml\System\Config\Cms;
use Magento\Framework\Controller\Result\JsonFactory;
class Export extends \Magento\Backend\App\Action {

    protected $resultJsonFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param JsonFactory $resultJsonFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        JsonFactory $resultJsonFactory
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
    }

    /**
     * Check whether vat is valid
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $result = $this->_export();
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData([
            'valid' => (int)$result->getIsValid(),
            'export_path' => $result->getImportPath(),
            'message' => $result->getRequestMessage(),
        ]);
    }

    protected function _export()
    {
        return $this->_objectManager->get('CleverSoft\CleverTheme\Model\Export\Export')
            ->export($this->getRequest()->getParam('export_type'),$this->getRequest()->getParam('store_view'));
    }
}
