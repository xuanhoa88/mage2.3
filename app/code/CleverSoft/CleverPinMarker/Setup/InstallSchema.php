<?php
/**
 * Copyright © 2015 CleverSoft. All rights reserved.
 */

namespace CleverSoft\CleverPinMarker\Setup;

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
            ->newTable($installer->getTable('cleversoft_pinmarker'))
            ->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Id'
            )
            ->addColumn(
                'pinmarker_label',
                Table::TYPE_TEXT,
                null,
                ['default' => null],
                'Label'
            )
            ->addColumn(
                'image',
                Table::TYPE_TEXT,
                null,
                ['default' => null],
                'Image'
            )
            ->addColumn(
                'wpa_pin',
                Table::TYPE_TEXT,
                null,
                ['default' => null],
                'Pin Content'
            )
            ->addColumn(
                'is_actived',
                Table::TYPE_SMALLINT,
                null,
                [],
                'Is Actived'
            )
			->setComment(
				'PinMarker Table'
            );
        
        $installer->getConnection()->createTable($table);

        $table  = $installer->getConnection()
            ->newTable($installer->getTable('cleversoft_pinmarker_collection'))
            ->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Id'
            )
            ->addColumn(
                'collection_name',
                Table::TYPE_TEXT,
                null,
                ['default' => null],
                'Collection Name'
            )
            ->addColumn(
                'display_type',
                Table::TYPE_TEXT,
                null,
                ['default' => null],
                'Display Type'
            )
            ->addColumn(
                'auto_play',
                Table::TYPE_SMALLINT,
                null,
                [],
                'Auto Play Slider'
            )
            ->addColumn(
                'arrow',
                Table::TYPE_SMALLINT,
                null,
                [],
                'Arrow Slider'
            )
            ->addColumn(
                'dot',
                Table::TYPE_SMALLINT,
                null,
                [],
                'Dot Slider'
            )
            ->addColumn(
                'column',
                Table::TYPE_TEXT,
                null,
                ['default' => null],
                'Collection Name'
            )
            ->addColumn(
                'gutter_width',
                Table::TYPE_TEXT,
                null,
                ['default' => null],
                'Gutter Width'
            )
            ->addColumn(
                'pin_ids',
                Table::TYPE_TEXT,
                null,
                ['default' => null],
                'Pin Ids'
            )
			->setComment(
				'PinMarker Table'
            );
        
        $installer->getConnection()->createTable($table);

        $installer->endSetup();
    }
}
