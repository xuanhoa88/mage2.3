<?php
namespace CleverSoft\CleverBuilder\Helper\RenderFields;
/**
 *
 * The common base class for text input type fields.
 */
class CategoryIdsField extends \CleverSoft\CleverBuilder\Helper\RenderFields\BaseFieldAbs {

/*
 * return html of rendering the field
 */
    protected function render_field( $value, $instance ) {
        $id = uniqid();
        $text =  __('Category id will be inserted here after you choose any one of them.') ;
        $data = array(
            'helper' => $this,
            'input_field' => '<input type="text" id="'.$id .'" name="'.$this->element_name .'" value="'. ( $value ) .'"  disabled="" placeholder="'.$text.'">',
            'element_name' => $this->element_name,
            'element_id' => $id,
            'value' => explode(",",$value)
        );
        echo $this->_objectManager->get('\Magento\Framework\View\Element\Template')->setData($data)->setTemplate('CleverSoft_CleverBuilder::widget/category-ids.phtml')->toHtml();
    }

    public function getModel($model){
        return $this->_objectManager->create($model);
    }

    public function getRootCategory(){
        $store = $this->_storeManager->getStore();
        $categoryId = $store->getRootCategoryId();
        $category = $this->getModel('Magento\Catalog\Model\Category')->load($categoryId);
        return $category;
    }

    public function getTreeCategory($category, $parent, $ids = array(), $checkedCat){
        $rootCategoryId = $this->getRootCategory()->getId();
        $children = $category->getChildrenCategories();
        $childrenCount = count($children);
        //$checkedCat = explode(',',$checkedIds);
        $htmlLi = '<li lang="'.$category->getId().'">';
        $html[] = $htmlLi;
        //if($this->isCategoryActive($category)){
        $ids[] = $category->getId();
        //$this->_ids = implode(",", $ids);
        //}
        $html[] = '<a id="node'.$category->getId().'">';

        if($category->getId() != $rootCategoryId){
            $html[] = '<input lang="'.$category->getId().'" type="checkbox" id="radio'.$category->getId().'" name="setting[category_id][]" value="'.$category->getId().'" class="checkbox'.$parent.'"';
            if(in_array($category->getId(), $checkedCat)){
                $html[] = ' checked="checked"';
            }
            $html[] = '/>';
        }


        $html[] = '<label for="radio'.$category->getId().'">' . $category->getName() . '</label>';

        $html[] = '</a>';

        $htmlChildren = '';
        if($childrenCount>0){
            foreach ($children as $child) {
                $_child = $this->getModel('Magento\Catalog\Model\Category')->load($child->getId());
                $htmlChildren .= $this->getTreeCategory($_child, $category->getId(), $ids, $checkedCat);
            }
        }
        if (!empty($htmlChildren)) {
            $html[] = '<ul id="container'.$category->getId().'" style="display:none">';
            $html[] = $htmlChildren;
            $html[] = '</ul>';
        }

        $html[] = '</li>';
        $html = implode("\n", $html);
        return $html;
    }

}
