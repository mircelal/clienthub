<?php
declare(strict_types=1);

namespace OCA\DomainControl\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\Migration\SimpleMigrationStep;
use OCP\Migration\IOutput;

/**
 * Add new columns to websites table
 */
class Version2200Date20241218000000 extends SimpleMigrationStep {

	public function changeSchema(IOutput $output, Closure $schemaClosure, array $options): ?ISchemaWrapper {
		/** @var ISchemaWrapper $schema */
		$schema = $schemaClosure();

		if ($schema->hasTable('domaincontrol_websites')) {
			$table = $schema->getTable('domaincontrol_websites');
			
			// Add name column
			if (!$table->hasColumn('name')) {
				$table->addColumn('name', 'string', [
					'notnull' => false,
					'length' => 255,
					'default' => '',
				]);
			}
			
			// Add version column
			if (!$table->hasColumn('version')) {
				$table->addColumn('version', 'string', [
					'notnull' => false,
					'length' => 50,
				]);
			}
			
			// Add status column
			if (!$table->hasColumn('status')) {
				$table->addColumn('status', 'string', [
					'notnull' => false,
					'length' => 20,
					'default' => 'active',
				]);
			}
			
			// Add url column
			if (!$table->hasColumn('url')) {
				$table->addColumn('url', 'string', [
					'notnull' => false,
					'length' => 500,
				]);
			}
			
			// Add admin_url column
			if (!$table->hasColumn('admin_url')) {
				$table->addColumn('admin_url', 'string', [
					'notnull' => false,
					'length' => 500,
				]);
			}
			
			// Add admin_notes column
			if (!$table->hasColumn('admin_notes')) {
				$table->addColumn('admin_notes', 'text', [
					'notnull' => false,
				]);
			}
		}

		return $schema;
	}
}


