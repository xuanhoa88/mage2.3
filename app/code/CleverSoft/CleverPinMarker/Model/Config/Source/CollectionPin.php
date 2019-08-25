<?php
/**
 * @category    CleverSoft
 * @package     CleverPinMarker
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverPinMarker\Model\Config\Source;
class CollectionPin implements \Magento\Framework\Option\ArrayInterface
{

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager){
        $this->_objectManager = $objectManager;
    }
    public function toOptionArray(){
        $pins = array();
        $collection = $this->_objectManager->create('CleverSoft\CleverPinMarker\Model\ResourceModel\PinCollection\Collection');
        foreach ($collection as $pin){
            $pins[] = array(
                'value'=>$pin->getId(),
                'label'=>$pin->getCollectionName());
        }
        return $pins;
    }
}