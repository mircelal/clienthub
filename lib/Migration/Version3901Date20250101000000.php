<?php

namespace OCA\DomainControl\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

class Version3901Date20250101000000 extends SimpleMigrationStep
{

    /**
     * @param IOutput $output
     * @param Closure $schemaClosure The `\Closure` returns a `ISchemaWrapper`
     * @param array $options
     * @return null|ISchemaWrapper
     */
    public function changeSchema(IOutput $output, Closure $schemaClosure, array $options)
    {
        /** @var ISchemaWrapper $schema */
        $schema = $schemaClosure();

        // Inventory Images Table
        if (!$schema->hasTable('dc_inventory_images')) {
            $table = $schema->createTable('dc_inventory_images');
            $table->addColumn('id', 'integer', [
                'autoincrement' => true,
                'notnull' => true,
            ]);
            $table->addColumn('inventory_id', 'integer', [
                'notnull' => true,
            ]);
            $table->addColumn('file_path', 'string', [
                'notnull' => true,
                'length' => 500,
            ]);
            $table->addColumn('file_id', 'integer', [
                'notnull' => false,
                'default' => 0,
            ]);
            $table->addColumn('is_primary', 'integer', [
                'notnull' => true,
                'default' => 0,
            ]);
            $table->addColumn('sort_order', 'integer', [
                'notnull' => true,
                'default' => 0,
            ]);
            $table->addColumn('created_at', 'datetime', [
                'notnull' => true,
            ]);
            $table->setPrimaryKey(['id']);
            $table->addIndex(['inventory_id'], 'idx_dc_inv_img_inv_id');
            $table->addIndex(['is_primary'], 'idx_dc_inv_img_primary');
        }

        return $schema;
    }
}


