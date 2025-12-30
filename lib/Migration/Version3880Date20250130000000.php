<?php
declare(strict_types=1);

namespace OCA\DomainControl\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\DB\Types;
use OCP\Migration\SimpleMigrationStep;
use OCP\Migration\IOutput;

/**
 * Create domaincontrol_domains and domaincontrol_hostings tables if they don't exist
 */
class Version3880Date20250130000000 extends SimpleMigrationStep {

	public function changeSchema(IOutput $output, Closure $schemaClosure, array $options): ?ISchemaWrapper {
		/** @var ISchemaWrapper $schema */
		$schema = $schemaClosure();

		// Create domains table if it doesn't exist
		if (!$schema->hasTable('domaincontrol_domains')) {
			$output->info('Creating domaincontrol_domains table...');
			$table = $schema->createTable('domaincontrol_domains');
			$table->addColumn('id', Types::BIGINT, [
				'autoincrement' => true,
				'notnull' => true,
			]);
			$table->addColumn('client_id', Types::BIGINT, [
				'notnull' => true,
			]);
			$table->addColumn('hosting_id', Types::BIGINT, [
				'notnull' => false,
				'default' => null,
			]);
			$table->addColumn('domain_name', Types::STRING, [
				'notnull' => true,
				'length' => 255,
			]);
			$table->addColumn('registrar', Types::STRING, [
				'notnull' => false,
				'length' => 255,
			]);
			$table->addColumn('registration_date', Types::STRING, [
				'notnull' => false,
				'length' => 20,
			]);
			$table->addColumn('expiration_date', Types::STRING, [
				'notnull' => false,
				'length' => 20,
			]);
			$table->addColumn('price', Types::DECIMAL, [
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
			$table->addColumn('renewal_interval', Types::STRING, [
				'notnull' => false,
				'length' => 20,
				'default' => '1',
			]);
			$table->addColumn('renewal_reminder', Types::BOOLEAN, [
				'notnull' => false,
				'default' => false,
			]);
			$table->addColumn('reminder_days', Types::INTEGER, [
				'notnull' => false,
				'default' => 30,
			]);
			$table->addColumn('notes', Types::TEXT, [
				'notnull' => false,
			]);
			$table->addColumn('panel_notes', Types::TEXT, [
				'notnull' => false,
			]);
			$table->addColumn('renewal_history', Types::TEXT, [
				'notnull' => false,
				'default' => '[]',
			]);
			$table->addColumn('user_id', Types::STRING, [
				'notnull' => true,
				'length' => 64,
			]);
			$table->addColumn('created_at', Types::STRING, [
				'notnull' => false,
			]);
			$table->addColumn('updated_at', Types::STRING, [
				'notnull' => false,
			]);
			$table->setPrimaryKey(['id']);
			$table->addIndex(['client_id'], 'dc_domain_client_id');
			$table->addIndex(['hosting_id'], 'dc_domain_hosting_id');
			$table->addIndex(['user_id'], 'dc_domain_user_id');
			$table->addIndex(['expiration_date'], 'dc_domain_expiry');
			$output->info('domaincontrol_domains table created successfully.');
		} else {
			$output->info('domaincontrol_domains table already exists.');
		}

		// Create hostings table if it doesn't exist
		if (!$schema->hasTable('domaincontrol_hostings')) {
			$output->info('Creating domaincontrol_hostings table...');
			$table = $schema->createTable('domaincontrol_hostings');
			$table->addColumn('id', Types::BIGINT, [
				'autoincrement' => true,
				'notnull' => true,
			]);
			$table->addColumn('client_id', Types::BIGINT, [
				'notnull' => true,
			]);
			$table->addColumn('package_id', Types::BIGINT, [
				'notnull' => false,
				'default' => null,
			]);
			$table->addColumn('provider', Types::STRING, [
				'notnull' => true,
				'length' => 255,
			]);
			$table->addColumn('plan', Types::STRING, [
				'notnull' => false,
				'length' => 255,
			]);
			$table->addColumn('server_type', Types::STRING, [
				'notnull' => false,
				'length' => 20,
				'default' => 'external',
			]);
			$table->addColumn('server_ip', Types::STRING, [
				'notnull' => false,
				'length' => 50,
			]);
			$table->addColumn('installation_date', Types::STRING, [
				'notnull' => false,
				'length' => 20,
			]);
			$table->addColumn('start_date', Types::STRING, [
				'notnull' => false,
				'length' => 20,
			]);
			$table->addColumn('expiration_date', Types::STRING, [
				'notnull' => false,
				'length' => 20,
			]);
			$table->addColumn('last_payment_date', Types::STRING, [
				'notnull' => false,
				'length' => 20,
			]);
			$table->addColumn('price', Types::DECIMAL, [
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
			$table->addColumn('renewal_interval', Types::STRING, [
				'notnull' => false,
				'length' => 20,
				'default' => 'monthly',
			]);
			$table->addColumn('renewal_reminder', Types::BOOLEAN, [
				'notnull' => false,
				'default' => false,
			]);
			$table->addColumn('reminder_days', Types::INTEGER, [
				'notnull' => false,
				'default' => 30,
			]);
			$table->addColumn('notes', Types::TEXT, [
				'notnull' => false,
			]);
			$table->addColumn('panel_url', Types::STRING, [
				'notnull' => false,
				'length' => 500,
			]);
			$table->addColumn('panel_notes', Types::TEXT, [
				'notnull' => false,
			]);
			$table->addColumn('payment_history', Types::TEXT, [
				'notnull' => false,
				'default' => '[]',
			]);
			$table->addColumn('user_id', Types::STRING, [
				'notnull' => true,
				'length' => 64,
			]);
			$table->addColumn('created_at', Types::STRING, [
				'notnull' => false,
			]);
			$table->addColumn('updated_at', Types::STRING, [
				'notnull' => false,
			]);
			$table->setPrimaryKey(['id']);
			$table->addIndex(['client_id'], 'dc_hosting_client_id');
			$table->addIndex(['package_id'], 'dc_hosting_package_id');
			$table->addIndex(['user_id'], 'dc_hosting_user_id');
			$table->addIndex(['expiration_date'], 'dc_hosting_expiry');
			$output->info('domaincontrol_hostings table created successfully.');
		} else {
			$output->info('domaincontrol_hostings table already exists.');
		}

		return $schema;
	}
}

