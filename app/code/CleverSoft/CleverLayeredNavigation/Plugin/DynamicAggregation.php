<?php
/**
 * @category    CleverSoft
 * @package     CleverLayeredNavigation
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverLayeredNavigation\Plugin;


use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\ScopeResolverInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Search\Request\BucketInterface;

class DynamicAggregation
{
    /**
     * @var Resource
     */
    protected $resource;

    /**
     * @var ScopeResolverInterface
     */
    protected $scopeResolver;

    protected $eavConfig;
    protected $filterSettingHelper;

    /**
     * @param ResourceConnection $resource
     * @param ScopeResolverInterface $scopeResolver
     */
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Framework\App\ScopeResolverInterface $scopeResolver,
        \Magento\Eav\Model\Config $eavConfig,
        \CleverSoft\CleverLayeredNavigation\Helper\FilterSetting $filterSettingHelper
    ) {
        $this->resource = $resource;
        $this->scopeResolver = $scopeResolver;
        $this->eavConfig = $eavConfig;
        $this->filterSettingHelper = $filterSettingHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function aroundBuild(
        \Magento\Framework\Search\Adapter\Mysql\Aggregation\Builder\Dynamic $subject,
        \Closure $closure,
        \Magento\Framework\Search\Adapter\Mysql\Aggregation\DataProviderInterface $dataProvider,
        array $dimensions,
        \Magento\Framework\Search\Request\BucketInterface $bucket,
        \Magento\Framework\DB\Ddl\Table $entityIdsTable
    ) {
        $attribute = $this->eavConfig->getAttribute(\Magento\Catalog\Model\Product::ENTITY, $bucket->getField());

        if($attribute->getBackendType() == 'decimal') {
            $filterSetting = $this->filterSettingHelper->getSettingByAttribute($attribute);
            if($filterSetting->getDisplayMode() == \CleverSoft\CleverLayeredNavigation\Model\Source\DisplayMode::MODE_SLIDER) {
                $currentScope = $dimensions['scope']->getValue();
                $currentScopeId = $this->scopeResolver->getScope($currentScope)->getId();
                /** @var RangeBucket $bucket */
                $select = $this->resource->getConnection()->select();
                $table = $this->resource->getTableName(
                    'catalog_product_index_eav_decimal'
                );
                $select->from(['main_table' => $table], ['value'])
                    ->where('main_table.attribute_id = ?', $attribute->getAttributeId())
                    ->where('main_table.store_id = ? ', $currentScopeId);
                $select->joinInner(
                    ['entities' => $entityIdsTable->getName()],
                    'main_table.entity_id  = entities.entity_id',
                    []
                );
                /** @var Select $fullQuery */
                $fullQuery = $this->resource->getConnection()
                    ->select();

                $fullQuery->from(['main_table' => $select], ['value'=> new \Zend_Db_Expr("'data'")]);
                $fullQuery->columns(
                    ['min' => 'min(main_table.value)',
                     'max' => 'max(main_table.value)']
                );
                return $dataProvider->execute($fullQuery);
            }
        }

        return $closure($dataProvider, $dimensions, $bucket, $entityIdsTable);
    }
}
