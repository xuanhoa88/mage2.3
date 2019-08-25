<?php
namespace CleverSoft\CleverPinMarker\Block\Adminhtml\Index\Edit;
class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('pinmarker_edit_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Pin Manager'));
    }
}
