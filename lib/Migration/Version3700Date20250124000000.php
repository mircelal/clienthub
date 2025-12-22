<?php
declare(strict_types=1);

namespace OCA\DomainControl\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\DB\Types;
use OCP\Migration\SimpleMigrationStep;
use OCP\Migration\IOutput;

class Version3700Date20250124000000 extends SimpleMigrationStep
{
	public function changeSchema(IOutput $output, Closure $schemaClosure, array $options): ?ISchemaWrapper
	{
		/** @var ISchemaWrapper $schema */
		$schema = $schemaClosure();

		// Create settings table
		if (!$schema->hasTable('domaincontrol_settings')) {
			$table = $schema->createTable('domaincontrol_settings');
			$table->addColumn('id', Types::BIGINT, [
				'autoincrement' => true,
				'notnull' => true,
			]);
			$table->addColumn('user_id', Types::STRING, [
				'notnull' => true,
				'length' => 64,
			]);
			$table->addColumn('setting_key', Types::STRING, [
				'notnull' => true,
				'length' => 255,
			]);
			$table->addColumn('setting_value', Types::TEXT, [
				'notnull' => false,
				'default' => null,
			]);
			$table->addColumn('created_at', Types::DATETIME, [
				'notnull' => false,
			]);
			$table->addColumn('updated_at', Types::DATETIME, [
				'notnull' => false,
			]);

			$table->setPrimaryKey(['id']);
			$table->addUniqueIndex(['user_id', 'setting_key'], 'dc_settings_user_key');
			$table->addIndex(['user_id'], 'dc_settings_user_id');
		}

		return $schema;
	}
}

