<?php
declare(strict_types=1);

namespace OCA\DomainControl\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\DB\Types;
use OCP\Migration\SimpleMigrationStep;
use OCP\Migration\IOutput;

/**
 * Create domaincontrol_clients table if it doesn't exist
 */
class Version3870Date20250129000000 extends SimpleMigrationStep {

	public function changeSchema(IOutput $output, Closure $schemaClosure, array $options): ?ISchemaWrapper {
		/** @var ISchemaWrapper $schema */
		$schema = $schemaClosure();

		// Create clients table if it doesn't exist
		if (!$schema->hasTable('domaincontrol_clients')) {
			$output->info('Creating domaincontrol_clients table...');
			$table = $schema->createTable('domaincontrol_clients');
			$table->addColumn('id', Types::BIGINT, [
				'autoincrement' => true,
				'notnull' => true,
			]);
			$table->addColumn('name', Types::STRING, [
				'notnull' => true,
				'length' => 255,
			]);
			$table->addColumn('email', Types::STRING, [
				'notnull' => false,
				'length' => 255,
			]);
			$table->addColumn('phone', Types::STRING, [
				'notnull' => false,
				'length' => 50,
			]);
			$table->addColumn('notes', Types::TEXT, [
				'notnull' => false,
			]);
			$table->addColumn('user_id', Types::STRING, [
				'notnull' => true,
				'length' => 64,
			]);
			$table->addColumn('nc_user_id', Types::STRING, [
				'notnull' => false,
				'length' => 64,
				'default' => null,
			]);
			$table->addColumn('created_at', Types::STRING, [
				'notnull' => false,
			]);
			$table->addColumn('updated_at', Types::STRING, [
				'notnull' => false,
			]);
			$table->setPrimaryKey(['id']);
			$table->addIndex(['user_id'], 'dc_cli_user_idx');
			$table->addIndex(['nc_user_id'], 'dc_cli_nc_user_idx');
			$output->info('domaincontrol_clients table created successfully.');
		} else {
			$output->info('domaincontrol_clients table already exists.');
		}

		return $schema;
	}
}


