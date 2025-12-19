<?php
declare(strict_types=1);

namespace OCA\DomainControl\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\DB\Types;
use OCP\Migration\SimpleMigrationStep;
use OCP\Migration\IOutput;

class Version3002Date20241221000000 extends SimpleMigrationStep {

	public function changeSchema(IOutput $output, Closure $schemaClosure, array $options): ?ISchemaWrapper {
		/** @var ISchemaWrapper $schema */
		$schema = $schemaClosure();

		// Add project_type column to dc_projects
		if ($schema->hasTable('dc_projects')) {
			$table = $schema->getTable('dc_projects');
			
			if (!$table->hasColumn('project_type')) {
				$table->addColumn('project_type', Types::STRING, [
					'notnull' => false,
					'length' => 50,
					'default' => '',
				]);
			}
		}

		return $schema;
	}
}

