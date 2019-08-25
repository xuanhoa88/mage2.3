<?php
/**
 * @category    CleverSoft
 * @package     CleverMegaMenus
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverMegaMenus\Setup;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;

class InstallSchema implements InstallSchemaInterface {

    protected $deployHelper;

    public function __construct(
        \CleverSoft\CleverMegaMenus\Helper\Deploy $deployHelper
    ) {
        $this->deployHelper = $deployHelper;
    }

    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context){
        $installer = $setup;
        $installer->startSetup();
        if($setup->tableExists('clever_megamenu')) {
            //get old key before rename the table
            $oldIdxKey = $setup->getIdxName($setup->getTable('clever_megamenu'), ['title']);
            //rename
            $setup->getConnection()->renameTable('clever_megamenu', 'cleversoft_megamenus');
            //
            $setup->getConnection()->changeColumn(
                $setup->getTable('cleversoft_megamenus'),
                'title',
                'name',
                ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 'length' => 255, 'comment' => 'Menu Name']
            );
            $setup->getConnection()->changeColumn(
                $setup->getTable('cleversoft_megamenus'),
                'type',
                'direction',
                ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT, 'length' => 6, 'comment' => 'Direction']
            );
            $setup->getConnection()->changeColumn(
                $setup->getTable('cleversoft_megamenus'),
                'content',
                'menucontent',
                ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 'length' => '2M', 'comment' => 'Menu Content']
            );
            $setup->getConnection()->changeColumn(
                $setup->getTable('cleversoft_megamenus'),
                'style',
                'menustyles',
                ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 'length' => '128k', 'comment' => 'Menu Styles']
            );
            //remove old key from new table, unuseful.
            $setup->getConnection()->dropIndex($setup->getTable('cleversoft_megamenus'),$oldIdxKey);
            ///add new index key
            $setup->getConnection()->addIndex(
                $setup->getTable('cleversoft_megamenus'),
                $setup->getIdxName(
                    $setup->getTable('cleversoft_megamenus'),
                    ['name'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT
                ),
                ['name'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT);

            $tablesUpdate = ['content' =>'cms_page','xml'=>'layout_update','instance_type'=>'widget_instance','path'=>'core_config_data','menucontent'=>'cleversoft_megamenus'];
            $this->updateShortCodeData($tablesUpdate,$setup);
        } else {
            $table = $installer->getConnection()->newTable($installer->getTable('cleversoft_megamenus'))->addColumn(
                'id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['identity' => true, 'nullable' => false, 'primary' => true, 'unsigned' => true],
                'id'
            )->addColumn(
                'identifier',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Menu Identifier'
            )->addColumn(
                'name',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Menu Name'
            )->addColumn(
                'direction',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['nullable' => false, 'default' => '0'],
                'Menu Direction'
            )->addColumn(
                'menucontent',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '2M',
                [],
                'Menu Content'
            )->addColumn(
                'menustyles',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '128k',
                ['nullable' => true],
                'Menu Styles'
            )->addColumn(
                'is_active',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['nullable' => false, 'default' => '1'],
                'Is Active'
            )->addIndex(
                $installer->getIdxName('cleversoft_megamenus', ['identifier']),
                ['identifier']
            )->addIndex(
                $setup->getIdxName(
                    $installer->getTable('cleversoft_megamenus'),
                    ['name'],
                    AdapterInterface::INDEX_TYPE_FULLTEXT
                ),
                ['name'],
                ['type' => AdapterInterface::INDEX_TYPE_FULLTEXT]
            )->setComment(
                'CleverSoft Mega Menus Table'
            );
            $installer->getConnection()->createTable($table);
        }
        $this->deployPub();
    }

    /*
     * update shortcode data
     */
    public function updateShortCodeData($tablesUpdate,SchemaSetupInterface $setup){
        foreach ($tablesUpdate as $key=>$val) {
            $setup->getConnection()->query('UPDATE '.$setup->getTable($val).' SET '.$key.' = REPLACE('.$key.', "\MegaMenus", "\CleverMegaMenus")');
        }
        $setup->getConnection()->query('UPDATE '.$setup->getTable("core_config_data").' SET path = REPLACE(path, "_MegaMenus", "_CleverMegaMenus")');
        $setup->getConnection()->query('UPDATE '.$setup->getTable("cms_block").' SET content = REPLACE(content, "\MegaMenus", "\CleverMegaMenus")');
    }
    /*
     *
     */
    public function deployPub(){
        $modulePath = __DIR__.'/pub';
        $this->deployHelper->deployFolder($modulePath);
    }
}
