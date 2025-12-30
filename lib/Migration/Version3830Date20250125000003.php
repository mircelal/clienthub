<?php
declare(strict_types=1);

namespace OCA\DomainControl\Migration;

use OCP\DB\ISchemaWrapper;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

class Version3830Date20250125000003 extends SimpleMigrationStep {
	public function changeSchema(IOutput $output, \Closure $schemaClosure, array $options): ?ISchemaWrapper {
		/** @var ISchemaWrapper $schema */
		$schema = $schemaClosure();

		if ($schema->hasTable('dc_invoices')) {
			$table = $schema->getTable('dc_invoices');
			
			// Add title column if it doesn't exist
			if (!$table->hasColumn('title')) {
				$table->addColumn('title', 'string', [
					'notnull' => false,
					'length' => 255,
					'default' => null,
				]);
			}
		}

		return $schema;
	}
}

