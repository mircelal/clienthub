<?php
declare(strict_types=1);

namespace OCA\DomainControl\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\DB\Types;
use OCP\Migration\SimpleMigrationStep;
use OCP\Migration\IOutput;

class Version3500Date20250122000000 extends SimpleMigrationStep
{
	public function changeSchema(IOutput $output, Closure $schemaClosure, array $options): ?ISchemaWrapper
	{
		/** @var ISchemaWrapper $schema */
		$schema = $schemaClosure();

		// Hosting Packages (Templates) Table
		if (!$schema->hasTable('dc_hosting_packages')) {
			$table = $schema->createTable('dc_hosting_packages');
			$table->addColumn('id', Types::BIGINT, [
				'autoincrement' => true,
				'notnull' => true,
			]);
			$table->addColumn('name', Types::STRING, [
				'notnull' => true,
				'length' => 100,
			]);
			$table->addColumn('provider', Types::STRING, [
				'notnull' => true,
				'length' => 100,
			]);
			$table->addColumn('price_monthly', Types::DECIMAL, [
				'notnull' => false,
				'precision' => 10,
				'scale' => 2,
				'default' => 0,
			]);
			$table->addColumn('price_yearly', Types::DECIMAL, [
				'notnull' => false,
				'precision' => 10,
				'scale' => 2,
				'default' => 0,
			]);
			$table->addColumn('currency', Types::STRING, [
				'notnull' => false,
				'length' => 10,
				'default' => 'USD',
			]);
			$table->addColumn('disk_space_gb', Types::INTEGER, [
				'notnull' => false,
				'default' => 0,
			]);
			$table->addColumn('traffic_gb', Types::INTEGER, [
				'notnull' => false,
				'default' => 0,
			]);
			$table->addColumn('bandwidth_unlimited', Types::BOOLEAN, [
				'notnull' => false,
				'default' => false,
			]);
			$table->addColumn('domains_allowed', Types::INTEGER, [
				'notnull' => false,
				'default' => 1,
			]);
			$table->addColumn('databases_allowed', Types::INTEGER, [
				'notnull' => false,
				'default' => 0,
			]);
			$table->addColumn('email_accounts', Types::INTEGER, [
				'notnull' => false,
				'default' => 0,
			]);
			$table->addColumn('ftp_accounts', Types::INTEGER, [
				'notnull' => false,
				'default' => 0,
			]);
			$table->addColumn('ssl_included', Types::BOOLEAN, [
				'notnull' => false,
				'default' => false,
			]);
			$table->addColumn('backup_included', Types::BOOLEAN, [
				'notnull' => false,
				'default' => false,
			]);
			$table->addColumn('description', Types::TEXT, [
				'notnull' => false,
			]);
			$table->addColumn('is_active', Types::BOOLEAN, [
				'notnull' => false,
				'default' => true,
			]);
			$table->addColumn('user_id', Types::STRING, [
				'notnull' => false,
				'length' => 64,
			]);
			$table->addColumn('created_at', Types::DATETIME, [
				'notnull' => false,
			]);
			$table->addColumn('updated_at', Types::DATETIME, [
				'notnull' => false,
			]);
			
			$table->setPrimaryKey(['id']);
			$table->addIndex(['user_id'], 'dc_hpkg_user_id');
			$table->addIndex(['is_active'], 'dc_hpkg_is_active');
		}

		// Add package_id to hosting accounts table
		if ($schema->hasTable('domaincontrol_hostings')) {
			$table = $schema->getTable('domaincontrol_hostings');
			if (!$table->hasColumn('package_id')) {
				$table->addColumn('package_id', Types::BIGINT, [
					'notnull' => false,
					'default' => null,
				]);
				$table->addIndex(['package_id'], 'dc_hosting_package_id');
			}
		}

		return $schema;
	}
}

