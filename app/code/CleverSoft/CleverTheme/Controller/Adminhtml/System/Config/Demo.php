<?php
/**
 * @category    CleverSoft
 * @package     CleverTheme
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverTheme\Controller\Adminhtml\System\Config;

abstract class Demo extends \Magento\Backend\App\Action {
    protected function _import()
    {
        return $this->_objectManager->get('CleverSoft\CleverTheme\Model\Import\Demo')
            ->importDemo($this->getRequest()->getParam('demo_version'),$this->getRequest()->getParam('store_view'));
    }
}
