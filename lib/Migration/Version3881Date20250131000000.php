<?php
declare(strict_types=1);

namespace OCA\DomainControl\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\DB\Types;
use OCP\Migration\SimpleMigrationStep;
use OCP\Migration\IOutput;

/**
 * Add company and address columns to domaincontrol_clients table
 */
class Version3881Date20250131000000 extends SimpleMigrationStep {

	public function changeSchema(IOutput $output, Closure $schemaClosure, array $options): ?ISchemaWrapper {
		/** @var ISchemaWrapper $schema */
		$schema = $schemaClosure();

		if ($schema->hasTable('domaincontrol_clients')) {
			$table = $schema->getTable('domaincontrol_clients');
			
			// Add company column if it doesn't exist
			if (!$table->hasColumn('company')) {
				$output->info('Adding company column to domaincontrol_clients table...');
				$table->addColumn('company', Types::STRING, [
					'notnull' => false,
					'length' => 255,
					'default' => null,
				]);
				$output->info('company column added successfully.');
			} else {
				$output->info('company column already exists.');
			}

			// Add address column if it doesn't exist
			if (!$table->hasColumn('address')) {
				$output->info('Adding address column to domaincontrol_clients table...');
				$table->addColumn('address', Types::TEXT, [
					'notnull' => false,
					'default' => null,
				]);
				$output->info('address column added successfully.');
			} else {
				$output->info('address column already exists.');
			}
		}

		return $schema;
	}
}

