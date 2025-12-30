<?php
declare(strict_types=1);

namespace OCA\DomainControl\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\Migration\SimpleMigrationStep;
use OCP\Migration\IOutput;

/**
 * Migration: Restore oc_dc_payments table from backup SQL
 */
class Version3860Date20250128000000 extends SimpleMigrationStep {

	public function changeSchema(IOutput $output, Closure $schemaClosure, array $options): ?ISchemaWrapper {
		/** @var ISchemaWrapper $schema */
		$schema = $schemaClosure();

		// Table will be created via SQL in postSchemaChange
		// This method is required but we'll handle table creation in postSchemaChange
		
		return $schema;
	}

	public function postSchemaChange(IOutput $output, Closure $schemaClosure, array $options): void {
		/** @var \OCP\IDBConnection $connection */
		$connection = \OC::$server->get(\OCP\IDBConnection::class);
		
		// Get table prefix
		$config = \OC::$server->getConfig();
		$tablePrefix = $config->getSystemValue('dbtableprefix', 'oc_');
		$tableName = $tablePrefix . 'dc_payments';
		
		try {
			// Check if table already exists
			$qb = $connection->getQueryBuilder();
			$qb->select('*')
				->from('information_schema.tables')
				->where($qb->expr()->eq('table_schema', $qb->createNamedParameter($connection->getDatabaseName())))
				->andWhere($qb->expr()->eq('table_name', $qb->createNamedParameter($tableName)));
			
			$result = $qb->executeQuery();
			$tableExists = $result->fetchOne() !== false;
			$result->closeCursor();
			
			if ($tableExists) {
				$output->info("Table {$tableName} already exists, skipping creation.");
				return;
			}
			
			// Create table structure
			$connection->executeStatement("
				CREATE TABLE IF NOT EXISTS `{$tableName}` (
					`id` bigint(20) NOT NULL AUTO_INCREMENT,
					`invoice_id` bigint(20) DEFAULT NULL,
					`client_id` bigint(20) NOT NULL,
					`amount` decimal(10,2) NOT NULL,
					`currency` varchar(10) NOT NULL DEFAULT 'USD',
					`payment_date` varchar(255) DEFAULT NULL,
					`payment_method` varchar(50) DEFAULT NULL,
					`reference` varchar(255) DEFAULT NULL,
					`notes` longtext DEFAULT NULL,
					`user_id` varchar(64) NOT NULL,
					`created_at` varchar(255) DEFAULT NULL,
					`updated_at` varchar(255) DEFAULT NULL,
					PRIMARY KEY (`id`),
					KEY `dc_pay_inv_idx` (`invoice_id`),
					KEY `dc_pay_cli_idx` (`client_id`),
					KEY `dc_pay_usr_idx` (`user_id`)
				) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin
			");
			
			$output->info("Table {$tableName} created successfully.");
			
			// Insert data
			$payments = [
				[1, 1, 5, 3500.00, 'AZN', '2025-12-19', 'cash', '', '', 'calal', '2025-12-19 13:30:22', '2025-12-19 13:30:22'],
				[2, 4, 5, 1000.00, 'AZN', '2025-12-19', 'cash', '', '', 'calal', '2025-12-19 13:39:35', '2025-12-19 13:39:35'],
				[3, 9, 15, 150.00, 'AZN', '2025-12-21', 'card', '', '', 'calal', '2025-12-21 17:59:24', '2025-12-21 17:59:24'],
				[4, 10, 21, 2.00, 'AZN', '2025-12-22', 'cash', '', '', 'calal', '2025-12-22 13:09:55', '2025-12-22 13:09:55'],
				[5, 11, 9, 2.00, 'AZN', '2025-12-23', 'cash', '', '', 'calal', '2025-12-23 17:20:56', '2025-12-23 17:20:56'],
				[6, 11, 9, 5.00, 'AZN', '2025-12-24', 'cash', '', '', 'calal', '2025-12-24 08:19:52', '2025-12-24 08:19:52'],
				[7, 11, 9, 35.00, 'AZN', '2025-12-24', 'cash', '', '', 'calal', '2025-12-24 08:55:14', '2025-12-24 08:55:14'],
				[8, 10, 21, 2.00, 'AZN', '2025-12-24', 'cash', '', '', 'calal', '2025-12-24 09:16:42', '2025-12-24 09:16:42'],
				[9, 6, 9, 7.00, 'AZN', '2025-12-24', 'cash', '', '', 'calal', '2025-12-24 10:18:04', '2025-12-24 10:18:04'],
			];
			
			foreach ($payments as $payment) {
				try {
					$qb = $connection->getQueryBuilder();
					$qb->insert($tableName)
						->values([
							'id' => $qb->createNamedParameter($payment[0]),
							'invoice_id' => $qb->createNamedParameter($payment[1] ?: null),
							'client_id' => $qb->createNamedParameter($payment[2]),
							'amount' => $qb->createNamedParameter($payment[3]),
							'currency' => $qb->createNamedParameter($payment[4]),
							'payment_date' => $qb->createNamedParameter($payment[5]),
							'payment_method' => $qb->createNamedParameter($payment[6]),
							'reference' => $qb->createNamedParameter($payment[7]),
							'notes' => $qb->createNamedParameter($payment[8]),
							'user_id' => $qb->createNamedParameter($payment[9]),
							'created_at' => $qb->createNamedParameter($payment[10]),
							'updated_at' => $qb->createNamedParameter($payment[11]),
						]);
					
					$qb->executeStatement();
				} catch (\Exception $e) {
					// Skip if duplicate key (already exists)
					if (strpos($e->getMessage(), 'Duplicate entry') === false) {
						$output->warning("Error inserting payment {$payment[0]}: " . $e->getMessage());
					}
				}
			}
			
			$output->info("Restored " . count($payments) . " payment records to {$tableName}.");
			
		} catch (\Exception $e) {
			$output->warning("Error restoring payments table: " . $e->getMessage());
			\OC::$server->getLogger()->error('Migration Version3860Date20250128000000 error: ' . $e->getMessage(), [
				'exception' => $e,
				'trace' => $e->getTraceAsString()
			]);
		}
	}
}

