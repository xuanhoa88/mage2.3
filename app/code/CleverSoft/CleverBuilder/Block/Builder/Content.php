<?php
namespace CleverSoft\CleverBuilder\Block\Builder;

class Content extends \Magento\Framework\View\Element\Template
{
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \CleverSoft\CleverBuilder\Model\ResourceModel\PanelsTemplate\CollectionFactory $templateCollectionFactory,
        array $data = []
    ) {
        $this->_templateCollectionFactory = $templateCollectionFactory;
        parent::__construct($context, $data);
    }

    public function getPrebuiltTemplate() {
        return $this->_templateCollectionFactory->create();
    }
}