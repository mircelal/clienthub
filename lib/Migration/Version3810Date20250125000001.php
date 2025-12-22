<?php
declare(strict_types=1);

namespace OCA\DomainControl\Migration;

use OCP\DB\ISchemaWrapper;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

class Version3810Date20250125000001 extends SimpleMigrationStep {
	public function changeSchema(IOutput $output, \Closure $schemaClosure, array $options): ?ISchemaWrapper {
		/** @var ISchemaWrapper $schema */
		$schema = $schemaClosure();

		if (!$schema->hasTable('dc_project_activities')) {
			$table = $schema->createTable('dc_project_activities');
			$table->addColumn('id', 'integer', [
				'autoincrement' => true,
				'notnull' => true,
			]);
			$table->addColumn('project_id', 'integer', [
				'notnull' => true,
			]);
			$table->addColumn('user_id', 'string', [
				'notnull' => true,
				'length' => 64,
			]);
			$table->addColumn('activity_type', 'string', [
				'notnull' => true,
				'length' => 50,
			]);
			$table->addColumn('description', 'text', [
				'notnull' => false,
			]);
			$table->addColumn('metadata', 'text', [
				'notnull' => false,
			]);
			$table->addColumn('created_at', 'datetime', [
				'notnull' => true,
			]);
			$table->setPrimaryKey(['id']);
			$table->addIndex(['project_id'], 'dc_proj_act_proj_idx');
			$table->addIndex(['user_id'], 'dc_proj_act_user_idx');
			$table->addIndex(['activity_type'], 'dc_proj_act_type_idx');
			$table->addIndex(['created_at'], 'dc_proj_act_date_idx');
		}

		return $schema;
	}
}

