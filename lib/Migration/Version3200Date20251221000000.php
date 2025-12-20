<?php
declare(strict_types=1);

namespace OCA\DomainControl\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\DB\Types;
use OCP\Migration\SimpleMigrationStep;
use OCP\Migration\IOutput;

class Version3200Date20251221000000 extends SimpleMigrationStep
{
	public function changeSchema(IOutput $output, Closure $schemaClosure, array $options): ?ISchemaWrapper
	{
		/** @var ISchemaWrapper $schema */
		$schema = $schemaClosure();

		if (!$schema->hasTable('dc_time_entries')) {
			$table = $schema->createTable('dc_time_entries');
			$table->addColumn('id', Types::BIGINT, [
				'autoincrement' => true,
				'notnull' => true,
			]);
			$table->addColumn('user_id', Types::STRING, [
				'notnull' => true,
				'length' => 64,
			]);
			$table->addColumn('project_id', Types::BIGINT, [
				'notnull' => true,
			]);
			$table->addColumn('task_id', Types::BIGINT, [
				'notnull' => false,
				'default' => null,
			]);
			$table->addColumn('description', Types::TEXT, [
				'notnull' => false,
			]);
			$table->addColumn('start_time', Types::DATETIME, [
				'notnull' => true,
			]);
			$table->addColumn('end_time', Types::DATETIME, [
				'notnull' => false,
				'default' => null,
			]);
			$table->addColumn('duration', Types::INTEGER, [
				'notnull' => true,
				'default' => 0,
				'comment' => 'Duration in seconds',
			]);
			$table->addColumn('is_running', Types::BOOLEAN, [
				'notnull' => false,
				'default' => 0,
			]);
			$table->addColumn('created_at', Types::DATETIME, [
				'notnull' => true,
			]);
			$table->addColumn('updated_at', Types::DATETIME, [
				'notnull' => true,
			]);

			$table->setPrimaryKey(['id']);
			$table->addIndex(['user_id'], 'dc_time_user_idx');
			$table->addIndex(['project_id'], 'dc_time_project_idx');
			$table->addIndex(['task_id'], 'dc_time_task_idx');
			$table->addIndex(['start_time'], 'dc_time_start_idx');
		}

		return $schema;
	}
}

