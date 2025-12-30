<?php
declare(strict_types=1);

namespace OCA\DomainControl\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\DB\Types;
use OCP\Migration\SimpleMigrationStep;
use OCP\Migration\IOutput;

/**
 * Migration: Add Invoice, Payment, Project, Task, Service tables
 * Table names shortened to avoid index name length issues
 */
class Version3000Date20241219000000 extends SimpleMigrationStep {

	public function changeSchema(IOutput $output, Closure $schemaClosure, array $options): ?ISchemaWrapper {
		/** @var ISchemaWrapper $schema */
		$schema = $schemaClosure();

		// 1. Service Types Table
		if (!$schema->hasTable('dc_service_types')) {
			$table = $schema->createTable('dc_service_types');
			$table->addColumn('id', Types::BIGINT, [
				'autoincrement' => true,
				'notnull' => true,
			]);
			$table->addColumn('name', Types::STRING, [
				'notnull' => true,
				'length' => 255,
			]);
			$table->addColumn('description', Types::TEXT, [
				'notnull' => false,
			]);
			$table->addColumn('default_price', Types::DECIMAL, [
				'notnull' => false,
				'precision' => 10,
				'scale' => 2,
				'default' => 0,
			]);
			$table->addColumn('default_currency', Types::STRING, [
				'notnull' => true,
				'length' => 10,
				'default' => 'USD',
			]);
			$table->addColumn('renewal_interval', Types::STRING, [
				'notnull' => true,
				'length' => 20,
				'default' => 'monthly',
			]);
			$table->addColumn('is_predefined', Types::SMALLINT, [
				'notnull' => true,
				'default' => 0,
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
			$table->addIndex(['user_id'], 'dc_st_user_idx');
		}

		// 2. Services Table
		if (!$schema->hasTable('dc_services')) {
			$table = $schema->createTable('dc_services');
			$table->addColumn('id', Types::BIGINT, [
				'autoincrement' => true,
				'notnull' => true,
			]);
			$table->addColumn('client_id', Types::BIGINT, [
				'notnull' => true,
			]);
			$table->addColumn('service_type_id', Types::BIGINT, [
				'notnull' => false,
			]);
			$table->addColumn('name', Types::STRING, [
				'notnull' => true,
				'length' => 255,
			]);
			$table->addColumn('price', Types::DECIMAL, [
				'notnull' => false,
				'precision' => 10,
				'scale' => 2,
				'default' => 0,
			]);
			$table->addColumn('currency', Types::STRING, [
				'notnull' => true,
				'length' => 10,
				'default' => 'USD',
			]);
			$table->addColumn('start_date', Types::STRING, [
				'notnull' => false,
			]);
			$table->addColumn('expiration_date', Types::STRING, [
				'notnull' => false,
			]);
			$table->addColumn('renewal_interval', Types::STRING, [
				'notnull' => true,
				'length' => 20,
				'default' => 'monthly',
			]);
			$table->addColumn('status', Types::STRING, [
				'notnull' => true,
				'length' => 20,
				'default' => 'active',
			]);
			$table->addColumn('notes', Types::TEXT, [
				'notnull' => false,
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
			$table->addIndex(['client_id'], 'dc_svc_cli_idx');
			$table->addIndex(['user_id'], 'dc_svc_usr_idx');
		}

		// 3. Invoices Table
		if (!$schema->hasTable('dc_invoices')) {
			$table = $schema->createTable('dc_invoices');
			$table->addColumn('id', Types::BIGINT, [
				'autoincrement' => true,
				'notnull' => true,
			]);
			$table->addColumn('client_id', Types::BIGINT, [
				'notnull' => true,
			]);
			$table->addColumn('invoice_number', Types::STRING, [
				'notnull' => true,
				'length' => 50,
			]);
			$table->addColumn('issue_date', Types::STRING, [
				'notnull' => false,
			]);
			$table->addColumn('due_date', Types::STRING, [
				'notnull' => false,
			]);
			$table->addColumn('total_amount', Types::DECIMAL, [
				'notnull' => false,
				'precision' => 10,
				'scale' => 2,
				'default' => 0,
			]);
			$table->addColumn('paid_amount', Types::DECIMAL, [
				'notnull' => false,
				'precision' => 10,
				'scale' => 2,
				'default' => 0,
			]);
			$table->addColumn('currency', Types::STRING, [
				'notnull' => true,
				'length' => 10,
				'default' => 'USD',
			]);
			$table->addColumn('status', Types::STRING, [
				'notnull' => true,
				'length' => 20,
				'default' => 'draft',
			]);
			$table->addColumn('notes', Types::TEXT, [
				'notnull' => false,
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
			$table->addIndex(['client_id'], 'dc_inv_cli_idx');
			$table->addIndex(['user_id'], 'dc_inv_usr_idx');
			$table->addIndex(['status'], 'dc_inv_stat_idx');
			$table->addUniqueIndex(['invoice_number', 'user_id'], 'dc_inv_num_unq');
		}

		// 4. Invoice Items Table
		if (!$schema->hasTable('dc_invoice_items')) {
			$table = $schema->createTable('dc_invoice_items');
			$table->addColumn('id', Types::BIGINT, [
				'autoincrement' => true,
				'notnull' => true,
			]);
			$table->addColumn('invoice_id', Types::BIGINT, [
				'notnull' => true,
			]);
			$table->addColumn('item_type', Types::STRING, [
				'notnull' => true,
				'length' => 20,
			]);
			$table->addColumn('item_id', Types::BIGINT, [
				'notnull' => false,
			]);
			$table->addColumn('description', Types::STRING, [
				'notnull' => true,
				'length' => 500,
			]);
			$table->addColumn('quantity', Types::INTEGER, [
				'notnull' => true,
				'default' => 1,
			]);
			$table->addColumn('unit_price', Types::DECIMAL, [
				'notnull' => false,
				'precision' => 10,
				'scale' => 2,
				'default' => 0,
			]);
			$table->addColumn('total_price', Types::DECIMAL, [
				'notnull' => false,
				'precision' => 10,
				'scale' => 2,
				'default' => 0,
			]);
			$table->addColumn('period_start', Types::STRING, [
				'notnull' => false,
			]);
			$table->addColumn('period_end', Types::STRING, [
				'notnull' => false,
			]);
			$table->addColumn('created_at', Types::STRING, [
				'notnull' => false,
			]);
			$table->setPrimaryKey(['id']);
			$table->addIndex(['invoice_id'], 'dc_ii_inv_idx');
		}

		// 5. Payments Table
		if (!$schema->hasTable('dc_payments')) {
			$table = $schema->createTable('dc_payments');
			$table->addColumn('id', Types::BIGINT, [
				'autoincrement' => true,
				'notnull' => true,
			]);
			$table->addColumn('invoice_id', Types::BIGINT, [
				'notnull' => false,
			]);
			$table->addColumn('client_id', Types::BIGINT, [
				'notnull' => true,
			]);
			$table->addColumn('amount', Types::DECIMAL, [
				'notnull' => true,
				'precision' => 10,
				'scale' => 2,
			]);
			$table->addColumn('currency', Types::STRING, [
				'notnull' => true,
				'length' => 10,
				'default' => 'USD',
			]);
			$table->addColumn('payment_date', Types::STRING, [
				'notnull' => false,
			]);
			$table->addColumn('payment_method', Types::STRING, [
				'notnull' => false,
				'length' => 50,
			]);
			$table->addColumn('reference', Types::STRING, [
				'notnull' => false,
				'length' => 255,
			]);
			$table->addColumn('notes', Types::TEXT, [
				'notnull' => false,
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
			$table->addIndex(['invoice_id'], 'dc_pay_inv_idx');
			$table->addIndex(['client_id'], 'dc_pay_cli_idx');
			$table->addIndex(['user_id'], 'dc_pay_usr_idx');
		}

		// 6. Projects Table
		if (!$schema->hasTable('dc_projects')) {
			$table = $schema->createTable('dc_projects');
			$table->addColumn('id', Types::BIGINT, [
				'autoincrement' => true,
				'notnull' => true,
			]);
			$table->addColumn('client_id', Types::BIGINT, [
				'notnull' => true,
			]);
			$table->addColumn('name', Types::STRING, [
				'notnull' => true,
				'length' => 255,
			]);
			$table->addColumn('description', Types::TEXT, [
				'notnull' => false,
			]);
			$table->addColumn('status', Types::STRING, [
				'notnull' => true,
				'length' => 20,
				'default' => 'active',
			]);
			$table->addColumn('start_date', Types::STRING, [
				'notnull' => false,
			]);
			$table->addColumn('deadline', Types::STRING, [
				'notnull' => false,
			]);
			$table->addColumn('budget', Types::DECIMAL, [
				'notnull' => false,
				'precision' => 10,
				'scale' => 2,
				'default' => 0,
			]);
			$table->addColumn('currency', Types::STRING, [
				'notnull' => true,
				'length' => 10,
				'default' => 'USD',
			]);
			$table->addColumn('notes', Types::TEXT, [
				'notnull' => false,
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
			$table->addIndex(['client_id'], 'dc_prj_cli_idx');
			$table->addIndex(['user_id'], 'dc_prj_usr_idx');
			$table->addIndex(['status'], 'dc_prj_stat_idx');
		}

		// 7. Project Items Table
		if (!$schema->hasTable('dc_project_items')) {
			$table = $schema->createTable('dc_project_items');
			$table->addColumn('id', Types::BIGINT, [
				'autoincrement' => true,
				'notnull' => true,
			]);
			$table->addColumn('project_id', Types::BIGINT, [
				'notnull' => true,
			]);
			$table->addColumn('item_type', Types::STRING, [
				'notnull' => true,
				'length' => 20,
			]);
			$table->addColumn('item_id', Types::BIGINT, [
				'notnull' => true,
			]);
			$table->addColumn('created_at', Types::STRING, [
				'notnull' => false,
			]);
			$table->setPrimaryKey(['id']);
			$table->addIndex(['project_id'], 'dc_pi_prj_idx');
			$table->addUniqueIndex(['project_id', 'item_type', 'item_id'], 'dc_pi_unq');
		}

		// 8. Tasks Table
		if (!$schema->hasTable('dc_tasks')) {
			$table = $schema->createTable('dc_tasks');
			$table->addColumn('id', Types::BIGINT, [
				'autoincrement' => true,
				'notnull' => true,
			]);
			$table->addColumn('project_id', Types::BIGINT, [
				'notnull' => false,
			]);
			$table->addColumn('client_id', Types::BIGINT, [
				'notnull' => false,
			]);
			$table->addColumn('title', Types::STRING, [
				'notnull' => true,
				'length' => 255,
			]);
			$table->addColumn('description', Types::TEXT, [
				'notnull' => false,
			]);
			$table->addColumn('status', Types::STRING, [
				'notnull' => true,
				'length' => 20,
				'default' => 'todo',
			]);
			$table->addColumn('priority', Types::STRING, [
				'notnull' => true,
				'length' => 10,
				'default' => 'medium',
			]);
			$table->addColumn('due_date', Types::STRING, [
				'notnull' => false,
			]);
			$table->addColumn('completed_at', Types::STRING, [
				'notnull' => false,
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
			$table->addIndex(['project_id'], 'dc_tsk_prj_idx');
			$table->addIndex(['client_id'], 'dc_tsk_cli_idx');
			$table->addIndex(['user_id'], 'dc_tsk_usr_idx');
			$table->addIndex(['status'], 'dc_tsk_stat_idx');
		}

		return $schema;
	}
}
