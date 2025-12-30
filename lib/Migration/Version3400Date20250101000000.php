<?php
declare(strict_types=1);

namespace OCA\DomainControl\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\DB\Types;
use OCP\Migration\SimpleMigrationStep;
use OCP\Migration\IOutput;

class Version3400Date20250101000000 extends SimpleMigrationStep
{
	public function changeSchema(IOutput $output, Closure $schemaClosure, array $options): ?ISchemaWrapper
	{
		/** @var ISchemaWrapper $schema */
		$schema = $schemaClosure();

		// 1. Transaction Categories Table
		if (!$schema->hasTable('dc_tran_cats')) {
			$table = $schema->createTable('dc_tran_cats');
			$table->addColumn('id', Types::BIGINT, [
				'autoincrement' => true,
				'notnull' => true,
			]);
			$table->addColumn('name', Types::STRING, [
				'notnull' => true,
				'length' => 100,
			]);
			$table->addColumn('type', Types::STRING, [
				'notnull' => true,
				'length' => 20, // 'income' or 'expense'
			]);
			$table->addColumn('icon', Types::STRING, [
				'notnull' => false,
				'length' => 50,
			]);
			$table->addColumn('color', Types::STRING, [
				'notnull' => false,
				'length' => 7,
			]);
			$table->addColumn('user_id', Types::STRING, [
				'notnull' => false,
				'length' => 64,
			]);
			$table->addColumn('is_predefined', Types::BOOLEAN, [
				'notnull' => false,
				'default' => false,
			]);
			$table->addColumn('created_at', Types::DATETIME, [
				'notnull' => false,
			]);
			$table->addColumn('updated_at', Types::DATETIME, [
				'notnull' => false,
			]);

			$table->setPrimaryKey(['id']);
			$table->addIndex(['user_id'], 'dc_tran_cat_usr');
			$table->addIndex(['type'], 'dc_tran_cat_typ');
		}

		// 2. Transactions Table
		if (!$schema->hasTable('dc_transactions')) {
			$table = $schema->createTable('dc_transactions');
			$table->addColumn('id', Types::BIGINT, [
				'autoincrement' => true,
				'notnull' => true,
			]);
			$table->addColumn('type', Types::STRING, [
				'notnull' => true,
				'length' => 20, // 'income' or 'expense'
			]);
			$table->addColumn('category_id', Types::BIGINT, [
				'notnull' => false,
			]);
			$table->addColumn('project_id', Types::BIGINT, [
				'notnull' => false,
			]);
			$table->addColumn('client_id', Types::BIGINT, [
				'notnull' => false,
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
			$table->addColumn('transaction_date', Types::DATE, [
				'notnull' => false,
			]);
			$table->addColumn('description', Types::TEXT, [
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
			$table->addColumn('created_at', Types::DATETIME, [
				'notnull' => false,
			]);
			$table->addColumn('updated_at', Types::DATETIME, [
				'notnull' => false,
			]);

			$table->setPrimaryKey(['id']);
			$table->addIndex(['user_id'], 'dc_tran_usr');
			$table->addIndex(['type'], 'dc_tran_typ');
			$table->addIndex(['category_id'], 'dc_tran_cat');
			$table->addIndex(['project_id'], 'dc_tran_proj');
			$table->addIndex(['client_id'], 'dc_tran_cli');
			$table->addIndex(['transaction_date'], 'dc_tran_date');
		}

		// 3. Debts Table
		if (!$schema->hasTable('dc_debts')) {
			$table = $schema->createTable('dc_debts');
			$table->addColumn('id', Types::BIGINT, [
				'autoincrement' => true,
				'notnull' => true,
			]);
			$table->addColumn('type', Types::STRING, [
				'notnull' => true,
				'length' => 20, // 'debt' or 'credit'
			]);
			$table->addColumn('debt_type', Types::STRING, [
				'notnull' => true,
				'length' => 20, // 'credit_card', 'loan', 'physical', 'other'
			]);
			$table->addColumn('creditor_debtor_name', Types::STRING, [
				'notnull' => false,
				'length' => 255,
			]);
			$table->addColumn('client_id', Types::BIGINT, [
				'notnull' => false,
			]);
			$table->addColumn('total_amount', Types::DECIMAL, [
				'notnull' => true,
				'precision' => 10,
				'scale' => 2,
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
			$table->addColumn('interest_rate', Types::DECIMAL, [
				'notnull' => false,
				'precision' => 5,
				'scale' => 2,
			]);
			$table->addColumn('start_date', Types::DATE, [
				'notnull' => false,
			]);
			$table->addColumn('due_date', Types::DATE, [
				'notnull' => false,
			]);
			$table->addColumn('next_payment_date', Types::DATE, [
				'notnull' => false,
			]);
			$table->addColumn('payment_frequency', Types::STRING, [
				'notnull' => false,
				'length' => 20, // 'one_time', 'monthly', 'weekly', 'daily'
			]);
			$table->addColumn('payment_amount', Types::DECIMAL, [
				'notnull' => false,
				'precision' => 10,
				'scale' => 2,
			]);
			$table->addColumn('description', Types::TEXT, [
				'notnull' => false,
			]);
			$table->addColumn('status', Types::STRING, [
				'notnull' => true,
				'length' => 20,
				'default' => 'active', // 'active', 'paid', 'overdue', 'cancelled'
			]);
			$table->addColumn('notes', Types::TEXT, [
				'notnull' => false,
			]);
			$table->addColumn('user_id', Types::STRING, [
				'notnull' => true,
				'length' => 64,
			]);
			$table->addColumn('created_at', Types::DATETIME, [
				'notnull' => false,
			]);
			$table->addColumn('updated_at', Types::DATETIME, [
				'notnull' => false,
			]);

			$table->setPrimaryKey(['id']);
			$table->addIndex(['user_id'], 'dc_debt_usr');
			$table->addIndex(['type'], 'dc_debt_typ');
			$table->addIndex(['status'], 'dc_debt_stat');
			$table->addIndex(['client_id'], 'dc_debt_cli');
			$table->addIndex(['next_payment_date'], 'dc_debt_next');
		}

		// 4. Debt Payments Table
		if (!$schema->hasTable('dc_debt_pays')) {
			$table = $schema->createTable('dc_debt_pays');
			$table->addColumn('id', Types::BIGINT, [
				'autoincrement' => true,
				'notnull' => true,
			]);
			$table->addColumn('debt_id', Types::BIGINT, [
				'notnull' => true,
			]);
			$table->addColumn('amount', Types::DECIMAL, [
				'notnull' => true,
				'precision' => 10,
				'scale' => 2,
			]);
			$table->addColumn('payment_date', Types::DATE, [
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
			$table->addColumn('created_at', Types::DATETIME, [
				'notnull' => false,
			]);
			$table->addColumn('updated_at', Types::DATETIME, [
				'notnull' => false,
			]);

			$table->setPrimaryKey(['id']);
			$table->addIndex(['debt_id'], 'dc_debt_pay_debt');
			$table->addIndex(['user_id'], 'dc_debt_pay_usr');
			$table->addIndex(['payment_date'], 'dc_debt_pay_date');
		}

		return $schema;
	}

	public function postSchemaChange(IOutput $output, Closure $schemaClosure, array $options): void
	{
		/** @var \OCP\IDBConnection $connection */
		$connection = \OC::$server->get(\OCP\IDBConnection::class);

		// Insert predefined categories
		$predefinedCategories = [
			// Expense categories
			['name' => 'İnternet', 'type' => 'expense', 'icon' => '🌐', 'color' => '#3b82f6'],
			['name' => 'Elektrik', 'type' => 'expense', 'icon' => '⚡', 'color' => '#f59e0b'],
			['name' => 'Su', 'type' => 'expense', 'icon' => '💧', 'color' => '#06b6d4'],
			['name' => 'Doğalgaz', 'type' => 'expense', 'icon' => '🔥', 'color' => '#ef4444'],
			['name' => 'Telefon', 'type' => 'expense', 'icon' => '📱', 'color' => '#8b5cf6'],
			['name' => 'Ofis Kiralama', 'type' => 'expense', 'icon' => '🏢', 'color' => '#6366f1'],
			['name' => 'Yazılım Lisansları', 'type' => 'expense', 'icon' => '💻', 'color' => '#10b981'],
			['name' => 'Pazarlama', 'type' => 'expense', 'icon' => '📢', 'color' => '#f97316'],
			['name' => 'Ulaşım', 'type' => 'expense', 'icon' => '🚗', 'color' => '#64748b'],
			['name' => 'Diğer Giderler', 'type' => 'expense', 'icon' => '📦', 'color' => '#6b7280'],
			
			// Income categories
			['name' => 'Site Reklam Geliri', 'type' => 'income', 'icon' => '📺', 'color' => '#10b981'],
			['name' => 'Ek İşler', 'type' => 'income', 'icon' => '💼', 'color' => '#3b82f6'],
			['name' => 'Danışmanlık', 'type' => 'income', 'icon' => '🎯', 'color' => '#8b5cf6'],
			['name' => 'Yatırım Geliri', 'type' => 'income', 'icon' => '📈', 'color' => '#f59e0b'],
			['name' => 'Diğer Gelirler', 'type' => 'income', 'icon' => '💰', 'color' => '#6366f1'],
		];

		$qb = $connection->getQueryBuilder();
		$qb->insert('dc_tran_cats')
			->values([
				'name' => $qb->createParameter('name'),
				'type' => $qb->createParameter('type'),
				'icon' => $qb->createParameter('icon'),
				'color' => $qb->createParameter('color'),
				'is_predefined' => $qb->createParameter('is_predefined'),
				'created_at' => $qb->createParameter('created_at'),
				'updated_at' => $qb->createParameter('updated_at'),
			]);

		$now = date('Y-m-d H:i:s');
		foreach ($predefinedCategories as $category) {
			$qb->setParameter('name', $category['name'])
				->setParameter('type', $category['type'])
				->setParameter('icon', $category['icon'])
				->setParameter('color', $category['color'])
				->setParameter('is_predefined', true)
				->setParameter('created_at', $now)
				->setParameter('updated_at', $now);

			try {
				$qb->executeStatement();
			} catch (\Exception $e) {
				// Category might already exist, skip
				$output->info('Category ' . $category['name'] . ' might already exist, skipping...');
			}
		}

		$output->info('Inserted predefined transaction categories');
	}
}

