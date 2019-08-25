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


class AggregationDataProvider
{
    /**
     * @var Resource
     */
    protected $resource;

    /**
     * @var ScopeResolverInterface
     */
    protected $scopeResolver;

    /**
     * @param ResourceConnection $resource
     * @param ScopeResolverInterface $scopeResolver
     */
    public function __construct(
        ResourceConnection $resource,
        ScopeResolverInterface $scopeResolver
    ) {
        $this->resource = $resource;
        $this->scopeResolver = $scopeResolver;
    }

    public function aroundGetDataSet(
        \Magento\CatalogSearch\Model\Adapter\Mysql\Aggregation\DataProvider $subject,
        \Closure $proceed,
        BucketInterface $bucket,
        array $dimensions,
        Table $entityIdsTable
    ) {
        if ($bucket->getField() == 'stock_status') {
            $currentScope = $dimensions['scope']->getValue();
            $currentScopeId = $this->scopeResolver->getScope($currentScope)->getId();

            $derivedTable = $this->resource->getConnection()->select();
            $derivedTable->from(
                ['main_table' => $this->resource->getTableName('cataloginventory_stock_status')],
                [
                    'entity_id' => 'product_id',
                    'value' => 'stock_status',
                ]
            )->where('main_table.stock_id = ?', $currentScopeId);
            $derivedTable->joinInner(
                ['entities' => $entityIdsTable->getName()],
                'main_table.product_id  = entities.entity_id',
                []
            );

            $select = $this->resource->getConnection()->select();
            $select->from(['main_table' => $derivedTable]);
            return $select;
        }

        if ($bucket->getField() == 'rating_summary') {
            $currentScope = $dimensions['scope']->getValue();
            $currentScopeId = $this->scopeResolver->getScope($currentScope)->getId();
            $derivedTable = $this->resource->getConnection()->select();
            $derivedTable->from(
                ['entities' => $entityIdsTable->getName()],
                []
            );

            $columnRating = new \Zend_Db_Expr("
                IF(main_table.rating_summary >=100,
                    5,
                    IF(
                        main_table.rating_summary >=80,
                        4,
                        IF(main_table.rating_summary >=60,
                            3,
                            IF(main_table.rating_summary >=40,
                                2,
                                IF(main_table.rating_summary >=20,
                                    1,
                                    0
                                )
                            )
                        )
                    )
                )
            ");
            $derivedTable->joinLeft(
                ['main_table' => $this->resource->getTableName('review_entity_summary')],
                sprintf('`main_table`.`entity_pk_value`=`entities`.entity_id
                AND `main_table`.entity_type = 1
                AND `main_table`.store_id  =  %d',
                    $currentScopeId),
                [
                    'entity_id' => 'entity_pk_value',
                    'value' => $columnRating,
                ]
            );
            $select = $this->resource->getConnection()->select();
            $select->from(['main_table' => $derivedTable]);
            return $select;

        }

        return $proceed($bucket, $dimensions, $entityIdsTable);
    }

}
