<?php
/**
 * Copyright © 2015 CleverSoft. All rights reserved.
 */

namespace CleverSoft\CleverShopByBrand\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\DB\Adapter\AdapterInterface;


class InstallSchema implements InstallSchemaInterface
{
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();
        $table  = $installer->getConnection()
            ->newTable($installer->getTable('cleversoft_shopbybrand'))
            ->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Id'
            )
            ->addColumn(
                'brand_label',
                Table::TYPE_TEXT,
                255,
                ['default' => null],
                'Brand Label'
            )
            ->addColumn(
                'description',
                Table::TYPE_TEXT,
                null,
                ['default' => null],
                'Description'
            )
            ->addColumn(
                'logo',
                Table::TYPE_TEXT,
                null,
                ['default' => null],
                'Logo'
            )
            ->addColumn(
                'is_featured',
                Table::TYPE_SMALLINT,
                null,
                [],
                'Is Featured'
            )
            ->addColumn(
                'is_actived',
                Table::TYPE_SMALLINT,
                null,
                [],
                'Is Actived'
            )
            ->addColumn(
                'store_id',
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Store ID'
            )
            ->addColumn(
                'url_key',
                Table::TYPE_TEXT,
                null,
                ['default' => null],
                'Url Key'
            )
            ->addColumn(
                'meta_title',
                Table::TYPE_TEXT,
                null,
                ['default' => null],
                'Meta Title'
            )
            ->addColumn(
                'meta_description',
                Table::TYPE_TEXT,
                null,
                ['default' => null],
                'Meta Description'
            )
            ->addColumn(
                'meta_keyword',
                Table::TYPE_TEXT,
                null,
                ['default' => null],
                'Meta Keyword'
            )
            ->setComment(
                'Shop By Brand Table'
            )
            ;
        $installer->getConnection()->createTable($table);
        $installer->endSetup();
    }
}
