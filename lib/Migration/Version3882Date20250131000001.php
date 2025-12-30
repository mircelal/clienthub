<?php
declare(strict_types=1);

namespace OCA\DomainControl\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\DB\Types;
use OCP\Migration\SimpleMigrationStep;
use OCP\Migration\IOutput;

/**
 * Add title column to dc_client_notes table
 */
class Version3882Date20250131000001 extends SimpleMigrationStep {

	public function changeSchema(IOutput $output, Closure $schemaClosure, array $options): ?ISchemaWrapper {
		/** @var ISchemaWrapper $schema */
		$schema = $schemaClosure();

		if ($schema->hasTable('dc_client_notes')) {
			$table = $schema->getTable('dc_client_notes');
			
			// Add title column if it doesn't exist
			if (!$table->hasColumn('title')) {
				$output->info('Adding title column to dc_client_notes table...');
				$table->addColumn('title', Types::STRING, [
					'notnull' => false,
					'length' => 255,
					'default' => null,
				]);
				$output->info('title column added successfully.');
			} else {
				$output->info('title column already exists.');
			}
		}

		return $schema;
	}
}

