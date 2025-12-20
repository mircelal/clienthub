<?php
declare(strict_types=1);

namespace OCA\DomainControl\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\DB\Types;
use OCP\Migration\SimpleMigrationStep;
use OCP\Migration\IOutput;

class Version3300Date20251222000000 extends SimpleMigrationStep
{
	public function changeSchema(IOutput $output, Closure $schemaClosure, array $options): ?ISchemaWrapper
	{
		/** @var ISchemaWrapper $schema */
		$schema = $schemaClosure();

		// Create dc_project_shares table
		if (!$schema->hasTable('dc_project_shares')) {
			$table = $schema->createTable('dc_project_shares');
			$table->addColumn('id', Types::BIGINT, [
				'autoincrement' => true,
				'notnull' => true,
			]);
			$table->addColumn('project_id', Types::BIGINT, [
				'notnull' => true,
			]);
			$table->addColumn('shared_with_user_id', Types::STRING, [
				'notnull' => true,
				'length' => 255,
			]);
			$table->addColumn('permission_level', Types::STRING, [
				'notnull' => true,
				'length' => 20,
				'default' => 'read',
			]);
			$table->addColumn('shared_by_user_id', Types::STRING, [
				'notnull' => true,
				'length' => 255,
			]);
			$table->addColumn('created_at', Types::DATETIME, [
				'notnull' => true,
			]);

			$table->setPrimaryKey(['id']);
			$table->addIndex(['project_id'], 'dc_proj_share_proj_idx');
			$table->addIndex(['shared_with_user_id'], 'dc_proj_share_user_idx');
			$table->addUniqueIndex(['project_id', 'shared_with_user_id'], 'dc_proj_share_unique');

			$output->info('Created dc_project_shares table');
		}

		return $schema;
	}
}

