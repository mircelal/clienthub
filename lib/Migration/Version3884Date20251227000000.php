<?php
declare(strict_types=1);

namespace OCA\DomainControl\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\DB\Types;
use OCP\Migration\SimpleMigrationStep;
use OCP\Migration\IOutput;

class Version3884Date20251227000000 extends SimpleMigrationStep
{

    public function changeSchema(IOutput $output, Closure $schemaClosure, array $options): ?ISchemaWrapper
    {
        /** @var ISchemaWrapper $schema */
        $schema = $schemaClosure();

        if ($schema->hasTable('dc_invoices')) {
            $table = $schema->getTable('dc_invoices');
            if (!$table->hasColumn('project_id')) {
                $table->addColumn('project_id', Types::BIGINT, [
                    'notnull' => false,
                    'default' => null,
                ]);
                $table->addIndex(['project_id'], 'dc_inv_prj_idx');
            }
        }

        return $schema;
    }
}
