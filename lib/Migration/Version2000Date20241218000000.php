<?php
declare(strict_types=1);

namespace OCA\DomainControl\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\Migration\SimpleMigrationStep;
use OCP\Migration\IOutput;

/**
 * Add currency, panelNotes, and renewalHistory columns to domains table
 */
class Version2000Date20241218000000 extends SimpleMigrationStep {

	public function changeSchema(IOutput $output, Closure $schemaClosure, array $options): ?ISchemaWrapper {
		/** @var ISchemaWrapper $schema */
		$schema = $schemaClosure();

		if ($schema->hasTable('domaincontrol_domains')) {
			$table = $schema->getTable('domaincontrol_domains');
			
			// Add currency column
			if (!$table->hasColumn('currency')) {
				$table->addColumn('currency', 'string', [
					'notnull' => false,
					'length' => 10,
					'default' => 'USD',
				]);
			}
			
			// Add panel_notes column
			if (!$table->hasColumn('panel_notes')) {
				$table->addColumn('panel_notes', 'text', [
					'notnull' => false,
					'default' => null,
				]);
			}
			
			// Add renewal_history column (JSON)
			if (!$table->hasColumn('renewal_history')) {
				$table->addColumn('renewal_history', 'text', [
					'notnull' => false,
					'default' => '[]',
				]);
			}
		}

		return $schema;
	}
}

