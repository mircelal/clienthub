<?php
declare(strict_types=1);

namespace OCA\DomainControl\Migration;

use OCP\DB\ISchemaWrapper;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

class Version3840Date20250126000000 extends SimpleMigrationStep {
	public function changeSchema(IOutput $output, \Closure $schemaClosure, array $options): ?ISchemaWrapper {
		/** @var ISchemaWrapper $schema */
		$schema = $schemaClosure();

		if (!$schema->hasTable('dc_client_notes')) {
			$table = $schema->createTable('dc_client_notes');
			$table->addColumn('id', 'integer', [
				'autoincrement' => true,
				'notnull' => true,
			]);
			$table->addColumn('client_id', 'integer', [
				'notnull' => true,
			]);
			$table->addColumn('user_id', 'string', [
				'notnull' => true,
				'length' => 64,
			]);
			$table->addColumn('content', 'text', [
				'notnull' => false,
			]);
			$table->addColumn('created_at', 'datetime', [
				'notnull' => true,
			]);
			$table->addColumn('updated_at', 'datetime', [
				'notnull' => true,
			]);
			$table->setPrimaryKey(['id']);
			$table->addIndex(['client_id'], 'dc_client_note_client_idx');
			$table->addIndex(['user_id'], 'dc_client_note_user_idx');
		}

		return $schema;
	}
}

