<?php
/**
 * @category    CleverSoft
 * @package     CleverBuilder
 * @copyright   Copyright © 2018 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverBuilder\Block\Builder\Content\Render;

use Magento\Catalog\Model\Product\Attribute\Repository;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\Element\Template;

class Content extends \Magento\Catalog\Block\Product\AbstractProduct implements \Magento\Widget\Block\BlockInterface
{
    /*
         * @var \Magento\Catalog\Model\Product\Attribute\Repository
         */
    protected $_productAttributeRepository;
    /*
     * @var ObjectManagerInterface
     */
    protected $_objectManager;
    /**
     * Product collection factory
     *
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $viewedModel;
    /**
     * Resource instance
     *
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $_resource;
    /*
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $_productCollectionFactory;
    /*
     * @var \Magento\Framework\Url\Helper\Data
     */
    protected $_urlHelper;
    /**
     * @param ObjectManagerInterface $objectManagerInterface
     * @param Template\Context $context
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param array $data
     */
    public function __construct(\Magento\Catalog\Block\Product\Context $context, ObjectManagerInterface $objectManagerInterface,\Magento\Framework\App\ResourceConnection $resource, \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory , \Magento\Reports\Model\Product\Index\Viewed $viewedModel , \Magento\Framework\Url\Helper\Data $urlHelper, array $data = [] ) {
        $this->_objectManager = $objectManagerInterface;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_resource = $resource;
        $this->_urlHelper = $urlHelper;
        $this->viewedModel = $viewedModel;
        parent::__construct($context,$data);
    }
    /*
     * set template for generating html
     */
    protected function _construct()
    {
        parent::_construct();
    }
    /*
     * get defautl data
     */
    public function getDefaultData(){return $this->default;}
    /*
     * get html for empty tab
     */
    public function getEmptyDefaultTabs($default) {
        $html = '';
        $html .='<ul class="nav nav-tabs">';
        for ($i=0 ; $i < $default ; $i++) {
            $html .='<li data-id="tab'.$i.'" class="has-own-click-event '.($i == 0 ? 'active' : '' ).'"><a data-toggle="tab" href="#tab'.$i.'">'. __('Tab') .' '.($i+1) . ' ' . __('Title') .'</a></li>';
        }
        $html .='</ul>';

        $html .='<div class="tab-content empty-content has-own-click-event">';
            for ($i=0 ; $i < $default ; $i++) {
                $html .='<div id="tab'.$i.'" class="tab-pane fade in '.($i == 0 ? 'active' : '' ).'  has-own-click-event">';
                $html .='<div class="plus  has-own-click-event"></div></div>';
            }
        $html .='</div>';
        return $html;
    }
}
