<?php
/**
 * @category    CleverSoft
 * @package     CleverBuilder
 * @copyright   Copyright © 2018 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverBuilder\Block\Builder\Element\Render;

use Magento\Catalog\Model\Product\Attribute\Repository;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\Element\Template;

class Products extends \Magento\Catalog\Block\Product\AbstractProduct implements \Magento\Widget\Block\BlockInterface
{
    /*
     * @var array - default value.
     */
    protected $default = array(
        'number' => 4,
        'columns'=> 1,
        'order_by'=>'product_name',
        'order'=>'asc'
    );
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
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        ObjectManagerInterface $objectManagerInterface,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Reports\Model\Product\Index\Viewed $viewedModel,
        \Magento\Framework\Url\Helper\Data $urlHelper,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \CleverSoft\CleverBuilder\Helper\Product\Data $helperData,
        array $data = []
    ) {
        $this->_objectManager = $objectManagerInterface;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_resource = $resource;
        $this->_urlHelper = $urlHelper;
        $this->viewedModel = $viewedModel;
        $this->_jsonEncoder = $jsonEncoder;
        $this->_helperData = $helperData;
        parent::__construct($context,$data);
    }
    /*
     * set template for generating html
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('element/product.phtml');
        $this->_productAttributeRepository =  $this->_objectManager->create('Magento\Catalog\Model\Product\Attribute\Repository');
    }
    /*
     * set default value for query if there is no value available
     */
    public function setDefaultData(){
        $data = $this->getData();
        foreach ($this->default as $k=>$df) {
            if (!isset($data[$k])) {
                $this->setData($k, $df);
            }
        }
    }
    /*
     * build product collection
     */
    public function getProductCollection(){
        return $this->_productCollectionFactory->create();;
    }
    /*
     * get table name from resource
     */
    public function getTable($name)
    {
        return $this->_resource->getTableName($name);
    }
    /**
     * Get end of day
     * @return mixed
     */
    public function getEndOfDayDate()
    {
        return $this->_objectManager->create('\Magento\Framework\Stdlib\DateTime\DateTime')->date(null, '23:59:59');
    }
    /**
     * Get start of day
     * @return mixed
     */
    public function getStartOfDayDate()
    {
        return $this->_objectManager->create('\Magento\Framework\Stdlib\DateTime\DateTime')->date(null, '0:0:0');
    }
    /*
     * add order, limit to query
     */
    public function addOrderLimit($collection) {
        if($this->getData('order_by') == 'random') {
            $collection->getConnection()->orderRand($collection->getSelect());
        } else {
            $collection->setOrder($this->getData('order_by'), $this->getData('order'));
        }
        $collection
            ->getSelect()
            ->limit($this->getData('number'));
        return $collection;
    }
    /**
     * Get store id
     * @return mixed
     */
    public function getStoreId()
    {
        return $this->_objectManager->create('\Magento\Catalog\Block\Product\Context')->getStoreManager()->getStore()->getId();
    }
    /**
     * Get post parameters
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     */
    public function getAddToCartPostParams(\Magento\Catalog\Model\Product $product)
    {
        $url = $this->getAddToCartUrl($product);
        return [
            'action' => $url,
            'data' => [
                'product' => $product->getEntityId(),
                \Magento\Framework\App\ActionInterface::PARAM_NAME_URL_ENCODED => $this->_urlHelper->getEncodedUrl($url),
            ]
        ];
    }
}
