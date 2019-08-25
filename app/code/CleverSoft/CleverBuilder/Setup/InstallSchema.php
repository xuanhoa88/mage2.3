<?php
/**
 * Copyright � 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace CleverSoft\CleverBuilder\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
		/**
         * Create table 'mgs_theme_home_store'
         */
        $home_store = $installer->getConnection()->newTable(
            $installer->getTable('cleversoft_panels_data')
        )->addColumn(
            'id',
            Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Id'
        )->addColumn(
            'page_id',
            Table::TYPE_INTEGER,
            null,
            ['nullable' => false],
            'Page Id'
        )->addColumn(
            'setting',
            Table::TYPE_TEXT,
            '2M',
            ['nullable' => true],
            'Block Setting'
        );
        $installer->getConnection()->createTable($home_store);
        $installer->endSetup();

    }
}
