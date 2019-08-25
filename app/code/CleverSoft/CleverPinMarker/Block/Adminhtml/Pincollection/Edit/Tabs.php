<?php
namespace CleverSoft\CleverPinMarker\Block\Adminhtml\Pincollection\Edit;
class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('pincollection_edit_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Collection Manager'));
    }
}
