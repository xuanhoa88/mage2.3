<?php
/**
 * @category    CleverSoft
 * @package     CleverTheme
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */
 
namespace CleverSoft\CleverTheme\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Catalog Compare Item Model
 *
 */
class PreNextObserver implements ObserverInterface
{

    protected $_session;

    public function __construct(
        \Magento\Framework\Session\SessionManager $session
    )
    {
        $this->_session = $session;
    }


    public function execute(\Magento\Framework\Event\Observer $observer){
        return;
        if ($observer->getEvent()->getRequest()->getControllerName() == 'view') {

            $products = $observer->getEvent()->getBlock('\Magento\Catalog\Block\Product\List')
                ->getLoadedProductCollection()
                ->getColumnValues('entity_id');
            $this->_session->setPrevNextProductCollection($products);
            unset($products);
        }
        return $this;
    }

}
