<?php
/**
 * @category    CleverSoft
 * @package     CleverTheme
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverTheme\Block;


class Categories extends \Magento\Framework\View\Element\Template
{

    protected $_objectManager;

    protected $_registry;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Registry $registry,
        array $data = []

    ) {

        parent::__construct($context, $data);
        $this->_objectManager = $objectManager;
        $this->_registry = $registry;
    }

    public function categoryListHtml(){
        $category = $this->_objectManager->create('Magento\Catalog\Model\Category');
        if(is_object($this->_registry->registry('current_category'))){
            $current_category_path = $this->_registry->registry('current_category')->getPathIds();
        }else{
            $current_category_path = array();
        }


        $category->load($this->_storeManager->getStore()->getRootCategoryId());
        $children_string = $category->getChildren();
        $children = explode(',',$children_string);
        $extra_options='';
        foreach($children as $c){
            $selected = (in_array($c, $current_category_path)) ? 'selected="true"' : '';
            $extra_options.= '<li value="' . $c . '" ' . $selected . '><span>' . $category->load($c)->getName() . '</span></li>' . "\n";
        }

        return $extra_options;
    }
}