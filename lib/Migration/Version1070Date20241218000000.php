<?php
declare(strict_types=1);

namespace OCA\DomainControl\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

class Version1070Date20241218000000 extends SimpleMigrationStep {

	public function changeSchema(IOutput $output, Closure $schemaClosure, array $options): ?ISchemaWrapper {
		/** @var ISchemaWrapper $schema */
		$schema = $schemaClosure();

		// Add notes column to domains table if it doesn't exist
		if ($schema->hasTable('domaincontrol_domains')) {
			$table = $schema->getTable('domaincontrol_domains');
			
			if (!$table->hasColumn('notes')) {
				$table->addColumn('notes', 'text', [
					'notnull' => false,
				]);
			}
		}

		return $schema;
	}
}

