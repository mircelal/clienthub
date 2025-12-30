<?php
declare(strict_types=1);

namespace OCA\DomainControl\Migration;

use OCP\DB\ISchemaWrapper;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

class Version3850Date20251224110638 extends SimpleMigrationStep {
	public function changeSchema(IOutput $output, \Closure $schemaClosure, array $options): ?ISchemaWrapper {
		/** @var ISchemaWrapper $schema */
		$schema = $schemaClosure();

		if ($schema->hasTable('domaincontrol_clients')) {
			$table = $schema->getTable('domaincontrol_clients');
			
			if (!$table->hasColumn('nc_user_id')) {
				$table->addColumn('nc_user_id', 'string', [
					'notnull' => false,
					'length' => 64,
					'default' => null,
				]);
			}
		}

		return $schema;
	}
}

