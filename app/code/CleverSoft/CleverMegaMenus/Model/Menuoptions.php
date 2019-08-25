<?php
/**
 * @category    CleverSoft
 * @package     CleverMegaMenus
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverMegaMenus\Model;

class Menuoptions implements \Magento\Framework\Option\ArrayInterface {

	protected $_menucollection;

	public function __construct(
		\CleverSoft\CleverMegaMenus\Model\ResourceModel\Megamenu\Collection $menucollection
	){
		$this->_menucollection = $menucollection;
	}


	public function toOptionArray(){
		$menu = array();
		foreach($this->_menucollection as $item) {
            $menu[] = ['label' => $item->getName(),'value' => $item->getIdentifier()];
        }	
		return $menu;
    }
}
