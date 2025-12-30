<?php

namespace OCA\DomainControl\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

class Version3902Date20250102000000 extends SimpleMigrationStep
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

        // Add quantity and minQuantity columns to inventory table
        if ($schema->hasTable('dc_inventory')) {
            $table = $schema->getTable('dc_inventory');
            
            if (!$table->hasColumn('quantity')) {
                $table->addColumn('quantity', 'integer', [
                    'notnull' => true,
                    'default' => 0,
                ]);
            }
            
            if (!$table->hasColumn('min_quantity')) {
                $table->addColumn('min_quantity', 'integer', [
                    'notnull' => true,
                    'default' => 0,
                ]);
            }
        }

        return $schema;
    }
}


