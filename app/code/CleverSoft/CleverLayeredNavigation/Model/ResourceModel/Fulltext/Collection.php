<?php
/**
 * @category    CleverSoft
 * @package     CleverLayeredNavigation
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverLayeredNavigation\Model\ResourceModel\Fulltext;

use Magento\Framework\DB\Select;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Phrase;
use Magento\Framework\Search\Adapter\Mysql\TemporaryStorage;
use Magento\Framework\Search\Response\QueryResponse;
use Magento\Framework\Api\Search\SearchResultFactory;

/**
 * Fulltext Collection
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Collection extends \Magento\Catalog\Model\ResourceModel\Product\Collection
{
    /** @var  QueryResponse */
    protected $queryResponse;
    private $temporaryStorageFactory;

    /**
     * Catalog search data
     *
     * @var \Magento\Search\Model\QueryFactory
     */
    protected $queryFactory = null;
    private $searchResult;
    private $searchResultFactory;

    /**
     * Catalog search fulltext
     *
     * @var \Magento\CatalogSearch\Model\Fulltext
     */
    protected $_catalogSearchFulltext;

    /**
     * @var \CleverSoft\CleverLayeredNavigation\Model\Request\Builder
     */
    protected $requestBuilder;

    /**
     * @var \Magento\Search\Model\SearchEngine
     */
    protected $searchEngine;

    /** @var string */
    protected $queryText;

    /** @var string|null */
    protected $order = null;

    /**
     * @var \CleverSoft\CleverLayeredNavigation\Model\Request\Builder
     */
    public $_memRequestBuilder;

    public $request;

    protected $category_ids = [];

    /**
     * @param \Magento\Framework\Data\Collection\EntityFactory $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\Eav\Model\EntityFactory $eavEntityFactory
     * @param \Magento\Catalog\Model\ResourceModel\Helper $resourceHelper
     * @param \Magento\Framework\Validator\UniversalFactory $universalFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param \Magento\Catalog\Model\Indexer\Product\Flat\State $catalogProductFlatState
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Catalog\Model\Product\OptionFactory $productOptionFactory
     * @param \Magento\Catalog\Model\ResourceModel\Url $catalogUrl
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param \Magento\Customer\Api\GroupManagementInterface $groupManagement
     * @param \Magento\Search\Model\QueryFactory $catalogSearchData
     * @param \Magento\CatalogSearch\Model\Fulltext $catalogSearchFulltext
     * @param \CleverSoft\CleverLayeredNavigation\Model\Request\Builder $requestBuilder
     * @param \Magento\Search\Model\SearchEngine $searchEngine
     * @param \Zend_Db_Adapter_Abstract $connection
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactory $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Eav\Model\EntityFactory $eavEntityFactory,
        \Magento\Catalog\Model\ResourceModel\Helper $resourceHelper,
        \Magento\Framework\Validator\UniversalFactory $universalFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Catalog\Model\Indexer\Product\Flat\State $catalogProductFlatState,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Catalog\Model\Product\OptionFactory $productOptionFactory,
        \Magento\Catalog\Model\ResourceModel\Url $catalogUrl,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        \Magento\Customer\Api\GroupManagementInterface $groupManagement,
        \Magento\Search\Model\QueryFactory $catalogSearchData,
        \Magento\CatalogSearch\Model\Fulltext $catalogSearchFulltext,
        \CleverSoft\CleverLayeredNavigation\Model\Request\Builder $requestBuilder,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Search\Model\SearchEngine $searchEngine,
        \Magento\Framework\Search\Adapter\Mysql\TemporaryStorageFactory $temporaryStorageFactory,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        SearchResultFactory $searchResultFactory = null
    ) {
        $this->_catalogSearchFulltext = $catalogSearchFulltext;
        $this->queryFactory = $catalogSearchData;
        if ($searchResultFactory === null) {
            $this->searchResultFactory = \Magento\Framework\App\ObjectManager::getInstance()
                ->get('Magento\Framework\Api\Search\SearchResultFactory');
        }
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $eavConfig,
            $resource,
            $eavEntityFactory,
            $resourceHelper,
            $universalFactory,
            $storeManager,
            $moduleManager,
            $catalogProductFlatState,
            $scopeConfig,
            $productOptionFactory,
            $catalogUrl,
            $localeDate,
            $customerSession,
            $dateTime,
            $groupManagement,
            $connection
        );
        $this->requestBuilder = $requestBuilder;
        $this->request = $request;
        $this->temporaryStorageFactory = $temporaryStorageFactory;
        $this->searchEngine = $searchEngine;
    }

    /**
     * Apply attribute filter to facet collection
     *
     * @param string $field
     * @param null $condition
     * @return $this
     */
    public function addFieldToFilter($field, $condition = null)
    {
        if ($this->queryResponse !== null) {
            throw new \RuntimeException('Illegal state');
        }
        if (!is_array($condition) || (!in_array(key($condition), ['from', 'to'], true) && $field != 'visibility')) {
            $this->requestBuilder->bind($field, $condition);
        } else {
            if (!empty($condition['from'])) {
                $this->requestBuilder->bind("{$field}.from", $condition['from']);
            }
            if (!empty($condition['to'])) {
                $this->requestBuilder->bind("{$field}.to", $condition['to']);
            }
        }
        return $this;
    }

    /**
     * Add search query filter
     *
     * @param string $query
     * @return $this
     */
    public function addSearchFilter($query)
    {
        $this->queryText = trim($this->queryText . ' ' . $query);
        return $this;
    }

    /**
     * @inheritdoc
     */

    public function setRequestData($builder){
        $this->_select->reset();
        $this->requestBuilder = $builder;
        $this->queryResponse = null;
        $this->_isFiltersRendered = false;

    }
    protected function _renderFiltersBefore()
    {
        $this->requestBuilder->bindDimension('scope', $this->getStoreId());
        if ($this->queryText) {
            $this->requestBuilder->bind('search_term', $this->queryText);
        }

        $priceRangeCalculation = $this->_scopeConfig->getValue(
            \Magento\Catalog\Model\Layer\Filter\Dynamic\AlgorithmFactory::XML_PATH_RANGE_CALCULATION,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        if ($priceRangeCalculation) {
            $this->requestBuilder->bind('price_dynamic_algorithm', $priceRangeCalculation);
        }

        if($this->getFlag('category_ids')) {
            $this->requestBuilder->bind('category_ids',$this->getFlag('category_ids'));
        }

        $this->requestBuilder->autoSetRequestName();
        $this->_memRequestBuilder = clone $this->requestBuilder;
        $queryRequest = $this->requestBuilder->create();
        $this->queryResponse = $this->searchEngine->search($queryRequest);
        $ids = array();
        /** @var \Magento\Framework\Search\Document $document */
        foreach ($this->queryResponse as $document) {
            $ids[] = $document->getId();
        }
        parent::addFieldToFilter('entity_id', ['in' => $ids]);
        // $this->_totalRecords = count($ids);

        if ($this->order && $this->order['field'] == 'relevance') {
            $this->getSelect()->order(
                new \Zend_Db_Expr(
                    $this->_conn->quoteInto(
                        'FIELD(e.entity_id, ?) ' . $this->order['dir'],
                        $ids
                    )
                )
            );
        }
    }

    public function getQueryResponse() {
        return $this->queryResponse;
    }

    public function addItem(\Magento\Framework\DataObject $item){
        $itemId = $this->_getItemId($item);

        if ($itemId !== null) {
            if (isset($this->_items[$itemId])) {
                ///
            } else $this->_items[$itemId] = $item;

        } else {
            $this->_addItem($item);
        }
        return $this;
    }

    /**
     * Specify category filter for product collection
     *
     * @param \Magento\Catalog\Model\Category $category
     * @return $this
     */
    public function addCategoryFilter(\Magento\Catalog\Model\Category $category)
    {
        // code for multiselect categoru filter
//
        $categoryId = $this->request->getParam('cat') ? $this->request->getParam('cat') : 0 ;
        if ($categoryId) {
            $this->category_ids = explode(',',$categoryId);
        } else $this->category_ids[] = $category->getId();
        $this->addFieldToFilter('category_ids', $category->getId());
        return parent::addCategoryFilter($category);
    }


    /**
     * Specify category filter for product collection
     *
     * @param \Magento\Catalog\Model\Category $category
     * @return $this
     */
    protected function _applyProductLimitations()
    {
        $this->_prepareProductLimitationFilters();
        $this->_productLimitationJoinWebsite();
        $this->_productLimitationJoinPrice();
        $filters = $this->_productLimitationFilters;

        if (!isset($filters['category_id']) && !isset($filters['visibility'])) {
            return $this;
        }

        $conditions = [
            'cat_index.product_id=e.entity_id',
            $this->getConnection()->quoteInto('cat_index.store_id=?', $filters['store_id']),
        ];
        if (isset($filters['visibility']) && !isset($filters['store_table'])) {
            $conditions[] = $this->getConnection()->quoteInto('cat_index.visibility IN(?)', $filters['visibility']);
        }
        $categories_filter = !empty($this->category_ids) ? $this->category_ids : (is_array($filters['category_id']) ? $filters['category_id'] : array($filters['category_id']));

        $conditions[] = $this->getConnection()->quoteInto('cat_index.category_id IN(?)', $categories_filter);
        if (isset($filters['category_is_anchor'])) {
            $conditions[] = $this->getConnection()->quoteInto('cat_index.is_parent=?', $filters['category_is_anchor']);
        }

        $joinCond = join(' AND ', $conditions);
        $fromPart = $this->getSelect()->getPart(\Magento\Framework\DB\Select::FROM);
        if (isset($fromPart['cat_index'])) {
            $fromPart['cat_index']['joinCondition'] = $joinCond;
            $this->getSelect()->setPart(\Magento\Framework\DB\Select::FROM, $fromPart);
        } else {
            $this->getSelect()->join(
                ['cat_index' => $this->getTable('catalog_category_product_index')],
                $joinCond,
                ['cat_index_position' => 'position']
            );
        }

        $this->_productLimitationJoinStore();
        $this->_eventManager->dispatch(
            'catalog_product_collection_apply_limitations_after',
            ['collection' => $this]
        );
        return $this;
    }

    /**
     * Executes query and return raw response
     *
     * @param Table $table
     * @return array
     * @throws \Zend_Db_Exception
     */
    private function getDocuments(Table $table)
    {
        $connection = $this->getConnection();
        $select = $connection->select();
        $select->from($table->getName(), ['entity_id', 'score']);
        return $connection->fetchAssoc($select);
    }

    /**
     * @return $this
     */
    protected function _renderFilters()
    {
        $this->_filters = [];
        return parent::_renderFilters();
    }

    /**
     * Set Order field
     *
     * @param string $attribute
     * @param string $dir
     * @return $this
     */
    public function setOrder($attribute, $dir = Select::SQL_DESC)
    {
        $this->order = ['field' => $attribute, 'dir' => $dir];
        if ($attribute != 'relevance') {
            parent::setOrder($attribute, $dir);
        }
        return $this;
    }

    /**
     * Stub method for compatibility with other search engines
     *
     * @return $this
     */
    public function setGeneralDefaultQuery()
    {
        return $this;
    }

    /**
     * Return field faceted data from faceted search result
     *
     * @param string $field
     * @return array
     * @throws StateException
     */
    public function getFacetedData($field)
    {
        $this->_renderFilters();
        $aggregations = $this->queryResponse->getAggregations();
        $bucket = $aggregations->getBucket($field . '_bucket');
        if (!$bucket) {
            throw new StateException(new Phrase('Bucket does not exist'));
        }

        $result = [];
        foreach ($bucket->getValues() as $value) {
            $metrics = $value->getMetrics();
            $result[$metrics['value']] = $metrics;
        }
        return $result;
    }

    /**
     * Set product visibility filter for enabled products
     *
     * @param array $visibility
     * @return $this
     */
    public function setVisibility($visibility)
    {
        $this->addFieldToFilter('visibility', $visibility);
        return parent::setVisibility($visibility);
    }

    protected function _prepareStatisticsData()
    {
        if ($this->_memRequestBuilder) {
            $requestBuilder = clone $this->_memRequestBuilder;
            $requestBuilder->removePlaceholder("price.from");
            $requestBuilder->removePlaceholder("price.to");
            $this->requestBuilder = $requestBuilder;
        }
        $this->queryResponse = null;
        $this->_isFiltersRendered = false;
        $where = $this->getSelect()->getPart(\Zend_Db_Select::WHERE);
        foreach($where as $whereId=>$wherePart) {
            if(strpos($wherePart, 'entity_id')!== false) {
                if($whereId == 0) {
                    $where[$whereId] = '1';
                } else {
                    unset($where[$whereId]);
                }

            }
        }
        $this->getSelect()->setPart(\Zend_Db_Select::WHERE, $where);
        $this->_renderFilters();
        parent::_prepareStatisticsData();
    }

}
