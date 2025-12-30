<?php
declare(strict_types=1);

namespace OCA\DomainControl\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\DB\Types;
use OCP\Migration\SimpleMigrationStep;
use OCP\Migration\IOutput;

class Version3201Date20251221000001 extends SimpleMigrationStep
{
	public function changeSchema(IOutput $output, Closure $schemaClosure, array $options): ?ISchemaWrapper
	{
		/** @var ISchemaWrapper $schema */
		$schema = $schemaClosure();

		if ($schema->hasTable('dc_time_entries')) {
			$table = $schema->getTable('dc_time_entries');

			// Add duration column if it doesn't exist
			if (!$table->hasColumn('duration')) {
				$table->addColumn('duration', Types::INTEGER, [
					'notnull' => true,
					'default' => 0,
					'comment' => 'Duration in seconds',
				]);
				$output->info('Added duration column to dc_time_entries');
			}

			// Add is_running column if it doesn't exist
			if (!$table->hasColumn('is_running')) {
				$table->addColumn('is_running', Types::BOOLEAN, [
					'notnull' => false,
					'default' => 0,
				]);
				$output->info('Added is_running column to dc_time_entries');
			}

			// Add task_id column if it doesn't exist
			if (!$table->hasColumn('task_id')) {
				$table->addColumn('task_id', Types::BIGINT, [
					'notnull' => false,
					'default' => null,
				]);
				$table->addIndex(['task_id'], 'dc_time_task_idx');
				$output->info('Added task_id column to dc_time_entries');
			}

			// Add description column if it doesn't exist
			if (!$table->hasColumn('description')) {
				$table->addColumn('description', Types::TEXT, [
					'notnull' => false,
				]);
				$output->info('Added description column to dc_time_entries');
			}

			// Add end_time column if it doesn't exist
			if (!$table->hasColumn('end_time')) {
				$table->addColumn('end_time', Types::DATETIME, [
					'notnull' => false,
					'default' => null,
				]);
				$output->info('Added end_time column to dc_time_entries');
			}
		}

		return $schema;
	}
}

