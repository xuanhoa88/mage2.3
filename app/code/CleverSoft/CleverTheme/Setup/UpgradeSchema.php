<?php

namespace CleverSoft\CleverTheme\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        
        if (version_compare($context->getVersion(), '0.0.4', '<')) {
            /**
             * Create table 'clever_header_builder'
             */
            $table = $setup->getConnection()
                ->newTable($setup->getTable('clever_header_builder'))
                ->addColumn(
                    'id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Id'
                )
                ->addColumn(
                    'header_desktop_data',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    ['default' => null],
                    'Header Desktop Data'
                )
                ->addColumn(
                    'header_mobile_data',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    ['default' => null],
                    'Header Mobile Data'
                )
                ->addColumn(
                    'header_style',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    ['default' => null],
                    'Header Style'
                )
                ->addColumn(
                    'store_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    null,
                    ['unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Store ID'
                );
            $setup->getConnection()->createTable($table);

            $table = $setup->getConnection()
                ->newTable($setup->getTable('clever_header_builder_template'))
                ->addColumn(
                    'id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Id'
                )
                ->addColumn(
                    'template_name',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    ['default' => null],
                    'Template Name'
                )
                ->addColumn(
                    'header_desktop_data',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    ['default' => null],
                    'Header Desktop Data'
                )
                ->addColumn(
                    'header_mobile_data',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    ['default' => null],
                    'Header Mobile Data'
                )
                ->addColumn(
                    'header_style',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    ['default' => null],
                    'Header Style'
                );
            $setup->getConnection()->createTable($table);

            /**
             * Update tables 'cms_block'
             */
            $setup->getConnection()->addColumn(
                $setup->getTable('cms_block'),
                'use_in_headerbuilder',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    'unsigned' => true,
                    'nullable' => false,
                    'default' => '0',
                    'comment' => 'use_in_headerbuilder'
                ]
            );
        }

        if (version_compare($context->getVersion(), '0.0.5', '<')) {
            /**
             * Create table 'clever_header_builder'
             */
            $table = $setup->getConnection()
                ->newTable($setup->getTable('clever_footer_builder'))
                ->addColumn(
                    'id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Id'
                )
                ->addColumn(
                    'footer_desktop_data',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    ['default' => null],
                    'Footer Desktop Data'
                )
                ->addColumn(
                    'footer_mobile_data',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    ['default' => null],
                    'Footer Mobile Data'
                )
                ->addColumn(
                    'footer_style',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    ['default' => null],
                    'Footer Style'
                )
                ->addColumn(
                    'store_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    null,
                    ['unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Store ID'
                );
            $setup->getConnection()->createTable($table);

            $table = $setup->getConnection()
                ->newTable($setup->getTable('clever_footer_builder_template'))
                ->addColumn(
                    'id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Id'
                )
                ->addColumn(
                    'template_name',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    ['default' => null],
                    'Template Name'
                )
                ->addColumn(
                    'footer_desktop_data',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    ['default' => null],
                    'Footer Desktop Data'
                )
                ->addColumn(
                    'footer_mobile_data',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    ['default' => null],
                    'Footer Mobile Data'
                )
                ->addColumn(
                    'footer_style',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    ['default' => null],
                    'Footer Style'
                );
            $setup->getConnection()->createTable($table);
        }

        $setup->endSetup();
    }
}
