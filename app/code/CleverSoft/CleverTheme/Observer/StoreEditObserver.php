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
class StoreEditObserver implements ObserverInterface
{

    protected $_cssGenerator;

    public function __construct(
        \CleverSoft\CleverTheme\Model\Cssgen\Generator $generator
    )
    {
        $this->_cssGenerator = $generator;
    }

    /**
     * After store view is saved
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $store = $observer->getEvent()->getStore();
        $storeCode = $store->getCode();
        $websiteCode = $store->getWebsite()->getCode();

        $this->_cssGenerator->generateCss('design', $websiteCode, $storeCode);
        $this->_cssGenerator->generateCss('layout', $websiteCode, $storeCode);
    }

}
