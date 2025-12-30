<?php
declare(strict_types=1);

namespace OCA\DomainControl\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\Migration\SimpleMigrationStep;
use OCP\Migration\IOutput;

/**
 * Add new columns to hostings table for payment tracking
 */
class Version2100Date20241218000000 extends SimpleMigrationStep {

	public function changeSchema(IOutput $output, Closure $schemaClosure, array $options): ?ISchemaWrapper {
		/** @var ISchemaWrapper $schema */
		$schema = $schemaClosure();

		if ($schema->hasTable('domaincontrol_hostings')) {
			$table = $schema->getTable('domaincontrol_hostings');
			
			// Add server_type column
			if (!$table->hasColumn('server_type')) {
				$table->addColumn('server_type', 'string', [
					'notnull' => false,
					'length' => 20,
					'default' => 'external',
				]);
			}
			
			// Add start_date column
			if (!$table->hasColumn('start_date')) {
				$table->addColumn('start_date', 'string', [
					'notnull' => false,
					'length' => 20,
				]);
			}
			
			// Add expiration_date column
			if (!$table->hasColumn('expiration_date')) {
				$table->addColumn('expiration_date', 'string', [
					'notnull' => false,
					'length' => 20,
				]);
			}
			
			// Add last_payment_date column
			if (!$table->hasColumn('last_payment_date')) {
				$table->addColumn('last_payment_date', 'string', [
					'notnull' => false,
					'length' => 20,
				]);
			}
			
			// Add currency column
			if (!$table->hasColumn('currency')) {
				$table->addColumn('currency', 'string', [
					'notnull' => false,
					'length' => 10,
					'default' => 'USD',
				]);
			}
			
			// Add notes column
			if (!$table->hasColumn('notes')) {
				$table->addColumn('notes', 'text', [
					'notnull' => false,
				]);
			}
			
			// Add panel_url column
			if (!$table->hasColumn('panel_url')) {
				$table->addColumn('panel_url', 'string', [
					'notnull' => false,
					'length' => 500,
				]);
			}
			
			// Add panel_notes column
			if (!$table->hasColumn('panel_notes')) {
				$table->addColumn('panel_notes', 'text', [
					'notnull' => false,
				]);
			}
			
			// Add payment_history column (JSON)
			if (!$table->hasColumn('payment_history')) {
				$table->addColumn('payment_history', 'text', [
					'notnull' => false,
					'default' => '[]',
				]);
			}
		}

		return $schema;
	}
}

