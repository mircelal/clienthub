<?php
declare(strict_types=1);

namespace OCA\DomainControl\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\DB\Types;
use OCP\Migration\SimpleMigrationStep;
use OCP\Migration\IOutput;

/**
 * Add invoice_id column to dc_transactions table and migrate existing payments
 */
class Version3883Date20250131000002 extends SimpleMigrationStep {

	public function changeSchema(IOutput $output, Closure $schemaClosure, array $options): ?ISchemaWrapper {
		/** @var ISchemaWrapper $schema */
		$schema = $schemaClosure();

		if ($schema->hasTable('dc_transactions')) {
			$table = $schema->getTable('dc_transactions');
			
			// Add invoice_id column if it doesn't exist
			if (!$table->hasColumn('invoice_id')) {
				$output->info('Adding invoice_id column to dc_transactions table...');
				$table->addColumn('invoice_id', Types::BIGINT, [
					'notnull' => false,
					'default' => null,
				]);
				$table->addIndex(['invoice_id'], 'dc_tran_inv_idx');
				$output->info('invoice_id column added successfully.');
			} else {
				$output->info('invoice_id column already exists.');
			}
		}

		return $schema;
	}

	public function postSchemaChange(IOutput $output, Closure $schemaClosure, array $options): void {
		/** @var \OCP\IDBConnection $connection */
		$connection = \OC::$server->get(\OCP\IDBConnection::class);
		
		$qb = $connection->getQueryBuilder();
		
		// Check if there are any payments to migrate
		// Try to query the table - if it doesn't exist, catch the exception
		try {
			$qb->select('COUNT(*)')
				->from('dc_payments');
			$paymentCount = $qb->executeQuery()->fetchOne();
		} catch (\Exception $e) {
			// Table doesn't exist, skip migration
			$output->info('dc_payments table does not exist, skipping migration.');
			return;
		}
		
		if ($paymentCount == 0) {
			$output->info('No payments to migrate.');
			return;
		}

		$output->info("Migrating {$paymentCount} payments to transactions...");

		// Get all payments
		$qb = $connection->getQueryBuilder();
		$qb->select('*')
			->from('dc_payments')
			->orderBy('id', 'ASC');
		$payments = $qb->executeQuery()->fetchAllAssociative();

		$migrated = 0;
		$skipped = 0;

		foreach ($payments as $payment) {
			// Check if this payment already exists as a transaction (by checking notes for [INVOICE_ID:])
			$invoiceId = $payment['invoice_id'] ?? null;
			
			// Check if transaction already exists for this payment
			$checkQb = $connection->getQueryBuilder();
			$checkQb->select('COUNT(*)')
				->from('dc_transactions')
				->where($checkQb->expr()->eq('client_id', $checkQb->createNamedParameter($payment['client_id'])))
				->andWhere($checkQb->expr()->eq('amount', $checkQb->createNamedParameter($payment['amount'])))
				->andWhere($checkQb->expr()->eq('transaction_date', $checkQb->createNamedParameter($payment['payment_date'] ?? $payment['created_at'])));
			
			if ($invoiceId) {
				$checkQb->andWhere($checkQb->expr()->like('notes', $checkQb->createNamedParameter('%[INVOICE_ID:' . $invoiceId . ']%')));
			}
			
			$exists = $checkQb->executeQuery()->fetchOne() > 0;
			
			if ($exists) {
				$skipped++;
				continue;
			}

			// Create transaction from payment
			$insertQb = $connection->getQueryBuilder();
			$insertQb->insert('dc_transactions')
				->setValue('type', $insertQb->createNamedParameter('income'))
				->setValue('client_id', $insertQb->createNamedParameter($payment['client_id']))
				->setValue('amount', $insertQb->createNamedParameter($payment['amount']))
				->setValue('currency', $insertQb->createNamedParameter($payment['currency'] ?? 'USD'))
				->setValue('transaction_date', $insertQb->createNamedParameter($payment['payment_date'] ?? $payment['created_at']))
				->setValue('payment_method', $insertQb->createNamedParameter($payment['payment_method'] ?? ''))
				->setValue('reference', $insertQb->createNamedParameter($payment['reference'] ?? ''))
				->setValue('user_id', $insertQb->createNamedParameter($payment['user_id']))
				->setValue('created_at', $insertQb->createNamedParameter($payment['created_at'] ?? date('Y-m-d H:i:s')))
				->setValue('updated_at', $insertQb->createNamedParameter($payment['updated_at'] ?? date('Y-m-d H:i:s')));

			// Set invoice_id if exists
			if ($invoiceId) {
				$insertQb->setValue('invoice_id', $insertQb->createNamedParameter($invoiceId));
				
				// Build description
				$description = 'Fatura Ödemesi';
				if (!empty($payment['reference'])) {
					$description .= ' - ' . $payment['reference'];
				} else {
					$description .= ' - Ödeme #' . $payment['id'];
				}
				$insertQb->setValue('description', $insertQb->createNamedParameter($description));
				
				// Build notes with invoice ID
				$notes = 'Fatura ödemesi';
				if (!empty($payment['notes'])) {
					$notes .= ': ' . $payment['notes'];
				}
				$notes .= ' [INVOICE_ID:' . $invoiceId . ']';
				$insertQb->setValue('notes', $insertQb->createNamedParameter($notes));
			} else {
				// No invoice, just use payment notes as description
				$description = !empty($payment['notes']) ? $payment['notes'] : 'Ödeme #' . $payment['id'];
				$insertQb->setValue('description', $insertQb->createNamedParameter($description));
				$insertQb->setValue('notes', $insertQb->createNamedParameter(''));
			}

			try {
				$insertQb->executeStatement();
				$migrated++;
			} catch (\Exception $e) {
				$output->warning("Failed to migrate payment #{$payment['id']}: " . $e->getMessage());
				$skipped++;
			}
		}

		$output->info("Migration completed: {$migrated} payments migrated, {$skipped} skipped.");
	}
}

