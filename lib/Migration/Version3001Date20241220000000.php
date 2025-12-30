<?php
declare(strict_types=1);

namespace OCA\DomainControl\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\DB\Types;
use OCP\Migration\SimpleMigrationStep;
use OCP\Migration\IOutput;

/**
 * Migration: Add currency, discount, discount_type columns to dc_invoice_items
 */
class Version3001Date20241220000000 extends SimpleMigrationStep {

	public function changeSchema(IOutput $output, Closure $schemaClosure, array $options): ?ISchemaWrapper {
		/** @var ISchemaWrapper $schema */
		$schema = $schemaClosure();

		// Add new columns to dc_invoice_items
		if ($schema->hasTable('dc_invoice_items')) {
			$table = $schema->getTable('dc_invoice_items');
			
			// Add currency column
			if (!$table->hasColumn('currency')) {
				$table->addColumn('currency', Types::STRING, [
					'notnull' => false,
					'length' => 10,
					'default' => 'USD',
				]);
			}
			
			// Add discount column
			if (!$table->hasColumn('discount')) {
				$table->addColumn('discount', Types::DECIMAL, [
					'notnull' => false,
					'precision' => 10,
					'scale' => 2,
					'default' => 0,
				]);
			}
			
			// Add discount_type column
			if (!$table->hasColumn('discount_type')) {
				$table->addColumn('discount_type', Types::STRING, [
					'notnull' => false,
					'length' => 20,
					'default' => 'fixed',
				]);
			}
		}

		return $schema;
	}
}


