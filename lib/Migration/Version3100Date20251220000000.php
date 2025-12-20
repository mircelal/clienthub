<?php
declare(strict_types=1);

namespace OCA\DomainControl\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\DB\Types;
use OCP\Migration\SimpleMigrationStep;
use OCP\Migration\IOutput;

class Version3100Date20251220000000 extends SimpleMigrationStep
{

    public function changeSchema(IOutput $output, Closure $schemaClosure, array $options): ?ISchemaWrapper
    {
        /** @var ISchemaWrapper $schema */
        $schema = $schemaClosure();

        if ($schema->hasTable('dc_tasks')) {
            $table = $schema->getTable('dc_tasks');

            if (!$table->hasColumn('parent_id')) {
                $table->addColumn('parent_id', Types::BIGINT, [
                    'notnull' => false,
                    'default' => null,
                ]);
                $table->addIndex(['parent_id'], 'dc_tsk_parent_idx');
            }

            if (!$table->hasColumn('notes')) {
                $table->addColumn('notes', Types::TEXT, [
                    'notnull' => false,
                ]);
            }

            if (!$table->hasColumn('cancelled_at')) {
                $table->addColumn('cancelled_at', Types::STRING, [
                    'notnull' => false,
                ]);
            }
        }

        return $schema;
    }
}
