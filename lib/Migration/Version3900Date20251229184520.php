<?php

namespace OCA\DomainControl\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

class Version3900Date20251229184520 extends SimpleMigrationStep
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

        // 1. Warehouses Table
        if (!$schema->hasTable('dc_warehouses')) {
            $table = $schema->createTable('dc_warehouses');
            $table->addColumn('id', 'integer', [
                'autoincrement' => true,
                'notnull' => true,
            ]);
            $table->addColumn('name', 'string', [
                'notnull' => true,
                'length' => 255,
            ]);
            $table->addColumn('location', 'string', [
                'notnull' => false,
                'length' => 255,
            ]);
            $table->addColumn('description', 'text', [
                'notnull' => false,
            ]);
            $table->setPrimaryKey(['id']);
        }

        // 2. Categories Table
        if (!$schema->hasTable('dc_inv_categories')) {
            $table = $schema->createTable('dc_inv_categories');
            $table->addColumn('id', 'integer', [
                'autoincrement' => true,
                'notnull' => true,
            ]);
            $table->addColumn('name', 'string', [
                'notnull' => true,
                'length' => 255,
            ]);
            $table->addColumn('parent_id', 'integer', [
                'notnull' => false,
                'default' => 0,
            ]);
            $table->addColumn('description', 'text', [
                'notnull' => false,
            ]);
            $table->setPrimaryKey(['id']);
        }

        // 3. Inventory Items Table
        if (!$schema->hasTable('dc_inventory')) {
            $table = $schema->createTable('dc_inventory');
            $table->addColumn('id', 'integer', [
                'autoincrement' => true,
                'notnull' => true,
            ]);
            $table->addColumn('name', 'string', [
                'notnull' => true,
                'length' => 255,
            ]);
            $table->addColumn('sku', 'string', [
                'notnull' => false,
                'length' => 100,
            ]);
            $table->addColumn('category_id', 'integer', [
                'notnull' => false,
                'default' => 0,
            ]);
            $table->addColumn('warehouse_id', 'integer', [
                'notnull' => false,
                'default' => 0,
            ]);
            // Status: available, rented, sold, maintenance, broken, retired
            $table->addColumn('status', 'string', [
                'notnull' => true,
                'default' => 'available',
                'length' => 50,
            ]);
            $table->addColumn('serial_number', 'string', [
                'notnull' => false,
                'length' => 255,
            ]);
            $table->addColumn('purchase_price', 'decimal', [
                'notnull' => false,
                'scale' => 2,
                'precision' => 10,
                'default' => 0,
            ]);
            $table->addColumn('sale_price', 'decimal', [
                'notnull' => false,
                'scale' => 2,
                'precision' => 10,
                'default' => 0,
            ]);
            $table->addColumn('rental_price', 'decimal', [
                'notnull' => false,
                'scale' => 2,
                'precision' => 10,
                'default' => 0,
            ]);
            $table->addColumn('purchased_at', 'date', [
                'notnull' => false,
            ]);
            $table->addColumn('description', 'text', [
                'notnull' => false,
            ]);
            $table->addColumn('image_path', 'string', [
                'notnull' => false,
                'length' => 500,
            ]);
            // Current assignee (client ID) - for quick lookup
            $table->addColumn('ref_client_id', 'integer', [
                'notnull' => false,
                'default' => 0,
            ]);
            $table->setPrimaryKey(['id']);
            $table->addIndex(['sku'], 'idx_dc_inv_sku');
            $table->addIndex(['status'], 'idx_dc_inv_status');
        }

        // 4. Inventory Movements (History/Tracking)
        if (!$schema->hasTable('dc_inv_movements')) {
            $table = $schema->createTable('dc_inv_movements');
            $table->addColumn('id', 'integer', [
                'autoincrement' => true,
                'notnull' => true,
            ]);
            $table->addColumn('inventory_id', 'integer', [
                'notnull' => true,
            ]);
            $table->addColumn('client_id', 'integer', [
                'notnull' => false,
                'default' => 0,
            ]);
            $table->addColumn('project_id', 'integer', [
                'notnull' => false,
                'default' => 0,
            ]);
            // Type: sale, rent, repair, transfer, return
            $table->addColumn('type', 'string', [
                'notnull' => true,
                'length' => 50,
            ]);
            $table->addColumn('date_out', 'date', [
                'notnull' => true,
            ]);
            // Due date for return (e.g. end of rental)
            $table->addColumn('date_due', 'date', [
                'notnull' => false,
            ]);
            // Actual return date
            $table->addColumn('date_returned', 'date', [
                'notnull' => false,
            ]);
            $table->addColumn('price', 'decimal', [
                'notnull' => false,
                'scale' => 2,
                'precision' => 10,
                'default' => 0,
            ]);
            $table->addColumn('notes', 'text', [
                'notnull' => false,
            ]);
            $table->setPrimaryKey(['id']);
            $table->addIndex(['inventory_id'], 'idx_dc_mv_inv_id');
            $table->addIndex(['client_id'], 'idx_dc_mv_clt_id');
        }

        return $schema;
    }
}
