<?php
declare(strict_types=1);

namespace OCA\DomainControl\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\DB\Types;
use OCP\Migration\SimpleMigrationStep;
use OCP\Migration\IOutput;

class Version3301Date20251222000001 extends SimpleMigrationStep
{
	public function changeSchema(IOutput $output, Closure $schemaClosure, array $options): ?ISchemaWrapper
	{
		/** @var ISchemaWrapper $schema */
		$schema = $schemaClosure();

		if ($schema->hasTable('dc_tasks')) {
			$table = $schema->getTable('dc_tasks');

			// Add assigned_to_user_id column if it doesn't exist
			if (!$table->hasColumn('assigned_to_user_id')) {
				$table->addColumn('assigned_to_user_id', Types::STRING, [
					'notnull' => false,
					'length' => 255,
					'default' => null,
				]);
				$table->addIndex(['assigned_to_user_id'], 'dc_task_assigned_idx');
				$output->info('Added assigned_to_user_id column to dc_tasks');
			}

			// Add completed_by_user_id column if it doesn't exist
			if (!$table->hasColumn('completed_by_user_id')) {
				$table->addColumn('completed_by_user_id', Types::STRING, [
					'notnull' => false,
					'length' => 255,
					'default' => null,
				]);
				$table->addIndex(['completed_by_user_id'], 'dc_task_completed_idx');
				$output->info('Added completed_by_user_id column to dc_tasks');
			}
		}

		return $schema;
	}
}

