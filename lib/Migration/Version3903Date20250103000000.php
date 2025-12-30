<?php
namespace OCA\DomainControl\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\DB\Types;
use OCP\Migration\SimpleMigrationStep;
use OCP\Migration\IOutput;

class Version3903Date20250103000000 extends SimpleMigrationStep
{
    public function changeSchema(IOutput $output, Closure $schemaClosure, array $options): ?ISchemaWrapper
    {
        /** @var ISchemaWrapper $schema */
        $schema = $schemaClosure();

        // 1. Orders Table
        if (!$schema->hasTable('dc_orders')) {
            $table = $schema->createTable('dc_orders');
            $table->addColumn('id', 'integer', [
                'autoincrement' => true,
                'notnull' => true,
            ]);
            $table->addColumn('order_number', 'string', [
                'notnull' => true,
                'length' => 50,
            ]);
            $table->addColumn('client_id', 'integer', [
                'notnull' => true,
            ]);
            $table->addColumn('type', 'string', [
                'notnull' => true,
                'length' => 20, // 'sale' or 'rental'
            ]);
            $table->addColumn('order_date', 'date', [
                'notnull' => true,
            ]);
            $table->addColumn('total_amount', 'decimal', [
                'notnull' => true,
                'scale' => 2,
                'precision' => 10,
                'default' => 0,
            ]);
            $table->addColumn('status', 'string', [
                'notnull' => true,
                'length' => 20,
                'default' => 'pending', // pending, completed, cancelled
            ]);
            $table->addColumn('notes', 'text', [
                'notnull' => false,
            ]);
            $table->addColumn('created_at', 'datetime', [
                'notnull' => false,
            ]);
            $table->addColumn('updated_at', 'datetime', [
                'notnull' => false,
            ]);
            $table->setPrimaryKey(['id']);
            $table->addUniqueIndex(['order_number'], 'idx_dc_ord_num');
            $table->addIndex(['client_id'], 'idx_dc_ord_clt');
            $table->addIndex(['order_date'], 'idx_dc_ord_date');
            $table->addIndex(['status'], 'idx_dc_ord_status');
        }

        // 2. Add order_id to dc_inv_movements
        if ($schema->hasTable('dc_inv_movements')) {
            $table = $schema->getTable('dc_inv_movements');
            if (!$table->hasColumn('order_id')) {
                $table->addColumn('order_id', 'integer', [
                    'notnull' => false,
                    'default' => null,
                ]);
                $table->addIndex(['order_id'], 'idx_dc_mv_ord_id');
            }
        }

        return $schema;
    }
}


