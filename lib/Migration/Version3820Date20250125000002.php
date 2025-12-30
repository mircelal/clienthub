<?php
declare(strict_types=1);

namespace OCA\DomainControl\Migration;

use OCP\DB\ISchemaWrapper;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

class Version3820Date20250125000002 extends SimpleMigrationStep {
	public function changeSchema(IOutput $output, \Closure $schemaClosure, array $options): ?ISchemaWrapper {
		/** @var ISchemaWrapper $schema */
		$schema = $schemaClosure();

		if (!$schema->hasTable('dc_project_files')) {
			$table = $schema->createTable('dc_project_files');
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
			$table->addColumn('file_path', 'string', [
				'notnull' => true,
				'length' => 500,
			]);
			$table->addColumn('file_name', 'string', [
				'notnull' => true,
				'length' => 255,
			]);
			$table->addColumn('file_size', 'integer', [
				'notnull' => false,
			]);
			$table->addColumn('mime_type', 'string', [
				'notnull' => false,
				'length' => 100,
			]);
			$table->addColumn('category', 'string', [
				'notnull' => false,
				'length' => 50,
			]);
			$table->addColumn('description', 'text', [
				'notnull' => false,
			]);
			$table->addColumn('created_at', 'datetime', [
				'notnull' => true,
			]);
			$table->addColumn('updated_at', 'datetime', [
				'notnull' => true,
			]);
			$table->setPrimaryKey(['id']);
			$table->addIndex(['project_id'], 'dc_proj_file_proj_idx');
			$table->addIndex(['user_id'], 'dc_proj_file_user_idx');
			$table->addIndex(['category'], 'dc_proj_file_cat_idx');
		}

		return $schema;
	}
}

