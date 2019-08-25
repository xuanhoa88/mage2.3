<?php
/**
 * @category    CleverSoft
 * @package     CleverBuilder
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */
 
namespace CleverSoft\CleverBuilder\Setup;

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

        if (version_compare($context->getVersion(), '1.1.0', '<')) {
            $this->createTableTemplates($setup);
        }

        $setup->endSetup();
    }

    protected function createTableTemplates(SchemaSetupInterface $setup){
        $installer = $setup;
        $tableName = $installer->getTable('cleversoft_panels_template');
        $table = $installer->getConnection()
            ->newTable($tableName)
        ->addColumn(
            'id',
            Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Id'
        )->addColumn(
            'title',
            Table::TYPE_TEXT,
            null,
            ['nullable' => false],
            'Title'
        )->addColumn(
            'setting',
            Table::TYPE_TEXT,
            '2M',
            ['nullable' => true],
            'Setting'
        );

        $installer->getConnection()->createTable($table);
    }
}
