<?php
declare(strict_types=1);

namespace OCA\DomainControl\Migration;

use OCP\DB\ISchemaWrapper;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

class Version3800Date20250125000000 extends SimpleMigrationStep {
	public function changeSchema(IOutput $output, \Closure $schemaClosure, array $options): ?ISchemaWrapper {
		/** @var ISchemaWrapper $schema */
		$schema = $schemaClosure();

		if (!$schema->hasTable('dc_project_notes')) {
			$table = $schema->createTable('dc_project_notes');
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
			$table->addColumn('category', 'string', [
				'notnull' => true,
				'length' => 50,
				'default' => 'general',
			]);
			$table->addColumn('title', 'string', [
				'notnull' => true,
				'length' => 255,
			]);
			$table->addColumn('content', 'text', [
				'notnull' => false,
			]);
			$table->addColumn('status', 'string', [
				'notnull' => false,
				'length' => 50,
			]);
			$table->addColumn('created_at', 'datetime', [
				'notnull' => true,
			]);
			$table->addColumn('updated_at', 'datetime', [
				'notnull' => true,
			]);
			$table->setPrimaryKey(['id']);
			$table->addIndex(['project_id'], 'dc_proj_note_proj_idx');
			$table->addIndex(['user_id'], 'dc_proj_note_user_idx');
			$table->addIndex(['category'], 'dc_proj_note_cat_idx');
		}

		return $schema;
	}
}

