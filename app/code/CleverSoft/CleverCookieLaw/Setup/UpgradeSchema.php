<?php
/**
 * Copyright © 2015 CleverSoft. All rights reserved.
 */

namespace CleverSoft\CleverCookieLaw\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;


class UpgradeSchema implements UpgradeSchemaInterface
{
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();
        if (version_compare($context->getVersion(), '1.1.0', '<')) {

            $table  = $installer->getConnection()
                ->newTable($installer->getTable('cleversoft_cookielaw_rectify'))
                ->addColumn(
                    'id',
                    Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Id'
                )
                ->addColumn(
                    'email',
                    Table::TYPE_TEXT,
                    255,
                    ['default' => null],
                    'Email'
                )
                ->addColumn(
                    'created_at',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                    'Created At'
                )
                ->addColumn(
                    'status',
                    Table::TYPE_TEXT,
                    255,
                    ['default' => null],
                    'Status'
                )
                ->addColumn(
                    'information',
                    Table::TYPE_TEXT,
                    null,
                    ['default' => null],
                    'Information'
                );
            $installer->getConnection()->createTable($table);

            $table  = $installer->getConnection()
                ->newTable($installer->getTable('cleversoft_cookielaw_complaint'))
                ->addColumn(
                    'id',
                    Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Id'
                )
                ->addColumn(
                    'email',
                    Table::TYPE_TEXT,
                    255,
                    ['default' => null],
                    'Email'
                )
                ->addColumn(
                    'created_at',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                    'Created At'
                )
                ->addColumn(
                    'status',
                    Table::TYPE_TEXT,
                    255,
                    ['default' => null],
                    'Status'
                )
                ->addColumn(
                    'information',
                    Table::TYPE_TEXT,
                    null,
                    ['default' => null],
                    'Information'
                );
            $installer->getConnection()->createTable($table);

            $table  = $installer->getConnection()
                ->newTable($installer->getTable('cleversoft_cookielaw_delete'))
                ->addColumn(
                    'id',
                    Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Id'
                )
                ->addColumn(
                    'email',
                    Table::TYPE_TEXT,
                    255,
                    ['default' => null],
                    'Email'
                )
                ->addColumn(
                    'created_at',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                    'Created At'
                )
                ->addColumn(
                    'status',
                    Table::TYPE_TEXT,
                    255,
                    ['default' => null],
                    'Status'
                )
                ->addColumn(
                    'information',
                    Table::TYPE_TEXT,
                    null,
                    ['default' => null],
                    'Information'
                );
            $installer->getConnection()->createTable($table);

        }

        $installer->endSetup();
    }
}
