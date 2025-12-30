<?php
declare(strict_types=1);

namespace OCA\DomainControl\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\DB\Types;
use OCP\Migration\SimpleMigrationStep;
use OCP\Migration\IOutput;

class Version3600Date20250123000000 extends SimpleMigrationStep
{
	public function changeSchema(IOutput $output, Closure $schemaClosure, array $options): ?ISchemaWrapper
	{
		/** @var ISchemaWrapper $schema */
		$schema = $schemaClosure();

		// Add hosting_id to domains table
		if ($schema->hasTable('domaincontrol_domains')) {
			$table = $schema->getTable('domaincontrol_domains');
			if (!$table->hasColumn('hosting_id')) {
				$table->addColumn('hosting_id', Types::BIGINT, [
					'notnull' => false,
					'default' => null,
				]);
				$table->addIndex(['hosting_id'], 'dc_domain_hosting_id');
			}
		}

		return $schema;
	}
}

