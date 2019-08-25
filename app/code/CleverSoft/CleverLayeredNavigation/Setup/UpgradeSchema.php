<?php
/**
 * @category    CleverSoft
 * @package     CleverLayeredNavigation
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */
 
namespace CleverSoft\CleverLayeredNavigation\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\DB\Adapter\AdapterInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.0.0', '<')) {
            $this->createTableLayered($setup);
        }

        if (version_compare($context->getVersion(), '1.0.1', '<')) {
            $this->addPriceSliderColumnsToFilterSettings($setup);
        }

        if (version_compare($context->getVersion(), '1.2.2.1', '<')) {
            $this->addIndexModeColumnsToFilterSettings($setup);
        }

        if (version_compare($context->getVersion(), '1.3.1', '<')) {
            $this->addHideOneOptionColumnToFilterSettings($setup);
        }

        if (version_compare($context->getVersion(), '1.5.0', '<')) {
            $this->createOptionSettingTable($setup);
        }

        if (version_compare($context->getVersion(), '1.6.1', '<')) {
            $this->addCollapsedColumn($setup);
        }

        if (version_compare($context->getVersion(), '1.6.2', '<')) {
            $this->addDisplayProperties($setup);
        }

        if (version_compare($context->getVersion(), '1.6.3', '<')) {
            $this->addTooltips($setup);
        }

        if (version_compare($context->getVersion(), '1.6.4', '<')) {
            $this->renameCollapsedColumn($setup);
        }

        if (version_compare($context->getVersion(), '1.7.2', '<')) {
            $this->addUseAndLogicField($setup);
        }

        $setup->endSetup();
    }

    protected function createTableLayered(SchemaSetupInterface $setup){
        $installer = $setup;
        $tableName = $installer->getTable('cleversoft_clevershopby_filter_setting');
        $table = $installer->getConnection()
            ->newTable($tableName)
            ->addColumn(
                'setting_id',
                Table::TYPE_SMALLINT,
                null,
                ['identity' => true, 'nullable' => false, 'primary' => true]
            )
            ->addColumn('filter_code', Table::TYPE_TEXT, 100, ['nullable' => false])
            ->addColumn('is_multiselect', Table::TYPE_SMALLINT, null, ['nullable' => false, 'default' => 0])
            ->addColumn('display_mode', Table::TYPE_SMALLINT, null, ['nullable' => false, 'default' => 0])
            ->addColumn('is_seo_significant', Table::TYPE_SMALLINT, null, ['nullable' => false, 'default' => 0]);

        $installer->getConnection()->createTable($table);

        $installer->getConnection()->query('ALTER TABLE `' . $tableName . '` ADD UNIQUE(`filter_code`)');

        $table = $installer->getConnection()->newTable(
            $installer->getTable('cleversoft_clevershopby')
        )->addColumn(
            'page_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['identity' => true, 'nullable' => false, 'primary' => true],
            'Page ID'
        )->addColumn(
            'position',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false, 'default' => 'replace'],
            'Position'
        )->addColumn(
            'url',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => true],
            'Url'
        )->addColumn(
            'title',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => true],
            'Title'
        )->addColumn(
            'description',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '64k',
            ['nullable' => true],
            'description'
        )->addColumn(
            'meta_title',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => true],
            'Meta Title'
        )->addColumn(
            'meta_keywords',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '64k',
            ['nullable' => true],
            'Page Meta Keywords'
        )->addColumn(
            'meta_description',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '64k',
            ['nullable' => true],
            'Page Meta Description'
        )->addColumn(
            'conditions',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '128k',
            [],
            'Conditions'
        )->addColumn(
            'categories',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => true],
            'Categories'
        )->addColumn(
            'top_block_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['nullable' => true],
            'Top Block ID'
        )->addColumn(
            'bottom_block_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['nullable' => true],
            'Bottom Block ID'
        )->addIndex(
            $setup->getIdxName(
                $installer->getTable('cleversoft_clevershopby'),
                ['title', 'description', 'meta_title', 'meta_keywords', 'meta_description'],
                AdapterInterface::INDEX_TYPE_FULLTEXT
            ),
            ['title', 'description', 'meta_title', 'meta_keywords', 'meta_description'],
            ['type' => AdapterInterface::INDEX_TYPE_FULLTEXT]
        )->addForeignKey(
            $installer->getFkName('cleversoft_clevershopby', 'top_block_id', 'cms_block', 'block_id'),
            'top_block_id',
            $installer->getTable('cms_block'),
            'block_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_SET_NULL
        )->addForeignKey(
            $installer->getFkName('cleversoft_clevershopby', 'bottom_block_id', 'cms_block', 'block_id'),
            'bottom_block_id',
            $installer->getTable('cms_block'),
            'block_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_SET_NULL
        )->setComment(
            'CleverSoft ShopBy Page Table'
        );
        $installer->getConnection()->createTable($table);

        $table = $installer->getConnection()->newTable(
            $installer->getTable('cleversoft_clevershopby_store')
        )->addColumn(
            'page_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['nullable' => false, 'primary' => true],
            'Page ID'
        )->addColumn(
            'store_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'primary' => true],
            'Store ID'
        )->addIndex(
            $installer->getIdxName('cleversoft_clevershopby_store', ['store_id']),
            ['store_id']
        )->addForeignKey(
            $installer->getFkName('cleversoft_clevershopby_store', 'page_id', 'cleversoft_clevershopby', 'page_id'),
            'page_id',
            $installer->getTable('cleversoft_clevershopby'),
            'page_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->addForeignKey(
            $installer->getFkName('cleversoft_clevershopby_store', 'store_id', 'store', 'store_id'),
            'store_id',
            $installer->getTable('store'),
            'store_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->setComment(
            'CleverSoft ShopBy Page To Store Linkage Table'
        );
        $installer->getConnection()->createTable($table);
    }

    protected function addPriceSliderColumnsToFilterSettings(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->addColumn(
            $setup->getTable('cleversoft_clevershopby_filter_setting'),
            'slider_step',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                'length' => '12,4',
                'nullable' => false,
                'default' => '1.00',
                'comment' => 'Slider Step'
            ]
        );
        $setup->getConnection()->addColumn(
            $setup->getTable('cleversoft_clevershopby_filter_setting'),
            'units_label_use_currency_symbol',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
                'nullable' => false,
                'default' => true,
                'comment' => 'is Units label used currency symbol'
            ]
        );
        $setup->getConnection()->addColumn(
            $setup->getTable('cleversoft_clevershopby_filter_setting'),
            'units_label',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => '255',
                'nullable' => false,
                'default' => '',
                'comment' => 'Units label'
            ]
        );
    }

    protected function addIndexModeColumnsToFilterSettings(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->addColumn(
            $setup->getTable('cleversoft_clevershopby_filter_setting'),
            'index_mode',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                'nullable' => false,
                'default' => '0',
                'comment' => 'Robots Index Mode'
            ]
        );

        $setup->getConnection()->addColumn(
            $setup->getTable('cleversoft_clevershopby_filter_setting'),
            'follow_mode',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                'nullable' => false,
                'default' => '0',
                'comment' => 'Robots Follow Mode'
            ]
        );
    }

    protected function addHideOneOptionColumnToFilterSettings(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->addColumn(
            $setup->getTable('cleversoft_clevershopby_filter_setting'),
            'hide_one_option',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                'nullable' => false,
                'default' => '0',
                'comment' => 'Hide filter when only one option available'
            ]
        );
    }

    protected function createOptionSettingTable(SchemaSetupInterface $setup)
    {
        $tableName = $setup->getTable('cleversoft_clevershopby_option_setting');
        $table = $setup->getConnection()
            ->newTable($tableName)
            ->addColumn(
                'option_setting_id',
                Table::TYPE_SMALLINT,
                null,
                ['identity' => true, 'nullable' => false, 'primary' => true]
            )
            ->addColumn('filter_code', Table::TYPE_TEXT, 255, ['nullable' => false])
            ->addColumn('value', Table::TYPE_INTEGER, 11, ['nullable' => false])
            ->addColumn('store_id', Table::TYPE_INTEGER, null, ['nullable' => false, 'default'=>0])
            ->addColumn('url_alias', Table::TYPE_TEXT, 255, ['nullable' => false])
            ->addColumn('is_featured', Table::TYPE_SMALLINT, null, ['nullable' => false, 'default'=>0])
            ->addColumn('meta_title', Table::TYPE_TEXT, 1000, ['nullable' => false])
            ->addColumn('meta_description', Table::TYPE_TEXT, 10000)
            ->addColumn('meta_keywords', Table::TYPE_TEXT, 10000)
            ->addColumn('title', Table::TYPE_TEXT, 1000, ['nullable' => false])
            ->addColumn('description', Table::TYPE_TEXT, 10000)
            ->addColumn('image', Table::TYPE_TEXT, 255)
            ->addColumn('top_cms_block_id', Table::TYPE_INTEGER)
            ->addColumn('bottom_cms_block_id', Table::TYPE_INTEGER);

        $setup->getConnection()->createTable($table);
    }

    protected function addCollapsedColumn(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->addColumn(
            $setup->getTable('cleversoft_clevershopby_filter_setting'),
            'is_collapsed',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                'nullable' => false,
                'default' => '1',
                'comment' => 'Is filter collapsed'
            ]
        );
    }

    protected function addDisplayProperties(SchemaSetupInterface $setup)
    {
        $table = $setup->getTable('cleversoft_clevershopby_filter_setting');
        $connection = $setup->getConnection();

        $connection->addColumn(
            $table,
            'show_in_block',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                'nullable' => false,
                'default' => '0',
                'comment' => 'Show in the block'
            ]
        );

        $connection->addColumn(
            $table,
            'cat_tree_deep',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                'nullable' => false,
                'default' => '1',
                'comment' => 'Category Tree Depth'
            ]
        );

        $connection->addColumn(
            $table,
            'subcat_view',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                'nullable' => false,
                'default' => '1',
                'comment' => 'Subcategories View'
            ]
        );

        $connection->addColumn(
            $table,
            'expand_subcat',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                'nullable' => false,
                'default' => '1',
                'comment' => 'Expand Subcategories'
            ]
        );

        $connection->addColumn(
            $table,
            'render_cat_level',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                'nullable' => false,
                'default' => '1',
                'comment' => 'Render Categories Level'
            ]
        );

        $connection->addColumn(
            $table,
            'render_all_cat_tree',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                'nullable' => false,
                'default' => '0',
                'comment' => 'Render All Categories Tree'
            ]
        );








        $connection->addColumn(
            $table,
            'sort_options_by',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                'nullable' => false,
                'default' => '0',
                'comment' => 'Sort Options By'
            ]
        );

        $connection->addColumn(
            $table,
            'show_product_quantities',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                'nullable' => false,
                'default' => '0',
                'comment' => 'Show Product Quantities'
            ]
        );

        $connection->addColumn(
            $table,
            'is_show_search_box',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                'nullable' => false,
                'default' => '0',
                'comment' => 'Show Search Box'
            ]
        );

        $connection->addColumn(
            $table,
            'number_unfolded_options',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                'nullable' => false,
                'default' => '0',
                'comment' => 'Number of unfolded options'
            ]
        );
    }

    protected function addTooltips(SchemaSetupInterface $setup)
    {
        $table = $setup->getTable('cleversoft_clevershopby_filter_setting');
        $connection = $setup->getConnection();

        $connection->addColumn(
            $table,
            'tooltip',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'nullable' => false,
                'default' => '',
                'comment' => 'Tooltip'
            ]
        );
    }

    protected function renameCollapsedColumn(SchemaSetupInterface $setup)
    {
        $table = $setup->getTable('cleversoft_clevershopby_filter_setting');

        $sql = "ALTER TABLE `$table` CHANGE `is_collapsed` `is_expanded` INT(11) NOT NULL DEFAULT '0' COMMENT 'Is filter expanded'";
        $setup->getConnection()->query($sql);

        $sql = "UPDATE `$table` SET `is_expanded` = 1 - `is_expanded`;";
        $setup->getConnection()->query($sql);
    }

    protected function addUseAndLogicField(SchemaSetupInterface $setup)
    {
        $table = $setup->getTable('cleversoft_clevershopby_filter_setting');
        $connection = $setup->getConnection();

        $connection->addColumn(
            $table,
            'is_use_and_logic',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                'nullable' => false,
                'default' => 0,
                'comment' => 'Is Use And Logic'
            ]
        );
    }
}
