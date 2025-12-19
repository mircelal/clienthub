<?php
declare(strict_types=1);

namespace OCA\DomainControl\Db;

use OCP\AppFramework\Db\QBMapper;
use OCP\IDBConnection;

class InvoiceMapper extends QBMapper {
	public function __construct(IDBConnection $db) {
		parent::__construct($db, 'dc_invoices', Invoice::class);
	}

	/**
	 * @param string|null $userId
	 * @return Invoice[]
	 */
	public function findAll(?string $userId): array {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->where($qb->expr()->eq('user_id', $qb->createNamedParameter($userId)))
			->orderBy('issue_date', 'DESC');
		
		return $this->findEntities($qb);
	}

	/**
	 * @param int $clientId
	 * @param string|null $userId
	 * @return Invoice[]
	 */
	public function findByClient(int $clientId, ?string $userId): array {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->where($qb->expr()->eq('client_id', $qb->createNamedParameter($clientId)))
			->andWhere($qb->expr()->eq('user_id', $qb->createNamedParameter($userId)))
			->orderBy('issue_date', 'DESC');
		
		return $this->findEntities($qb);
	}

	/**
	 * @param int $id
	 * @param string|null $userId
	 * @return Invoice
	 */
	public function find(int $id, ?string $userId): Invoice {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->where($qb->expr()->eq('id', $qb->createNamedParameter($id)))
			->andWhere($qb->expr()->eq('user_id', $qb->createNamedParameter($userId)));
		
		return $this->findEntity($qb);
	}

	/**
	 * Find overdue invoices
	 */
	public function findOverdue(?string $userId): array {
		$today = date('Y-m-d');
		
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->where($qb->expr()->eq('user_id', $qb->createNamedParameter($userId)))
			->andWhere($qb->expr()->lt('due_date', $qb->createNamedParameter($today)))
			->andWhere($qb->expr()->in('status', $qb->createNamedParameter(['draft', 'sent'], \Doctrine\DBAL\Connection::PARAM_STR_ARRAY)))
			->orderBy('due_date', 'ASC');
		
		return $this->findEntities($qb);
	}

	/**
	 * Find upcoming due invoices
	 */
	public function findUpcomingDue(?string $userId, int $days = 30): array {
		$futureDate = date('Y-m-d', strtotime("+{$days} days"));
		$today = date('Y-m-d');
		
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->where($qb->expr()->eq('user_id', $qb->createNamedParameter($userId)))
			->andWhere($qb->expr()->lte('due_date', $qb->createNamedParameter($futureDate)))
			->andWhere($qb->expr()->gte('due_date', $qb->createNamedParameter($today)))
			->andWhere($qb->expr()->in('status', $qb->createNamedParameter(['draft', 'sent'], \Doctrine\DBAL\Connection::PARAM_STR_ARRAY)))
			->orderBy('due_date', 'ASC');
		
		return $this->findEntities($qb);
	}

	/**
	 * Find unpaid invoices
	 */
	public function findUnpaid(?string $userId): array {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->where($qb->expr()->eq('user_id', $qb->createNamedParameter($userId)))
			->andWhere($qb->expr()->in('status', $qb->createNamedParameter(['draft', 'sent', 'overdue'], \Doctrine\DBAL\Connection::PARAM_STR_ARRAY)))
			->orderBy('due_date', 'ASC');
		
		return $this->findEntities($qb);
	}

	/**
	 * Generate next invoice number - uses timestamp for uniqueness
	 */
	public function generateInvoiceNumber(?string $userId): string {
		$year = date('Y');
		$month = date('m');
		$timestamp = time();
		
		// Use timestamp-based unique number to avoid duplicates
		$uniquePart = substr((string)$timestamp, -6);
		
		return sprintf("INV-%s%s-%s", $year, $month, $uniquePart);
	}
	
	/**
	 * Check if invoice number exists
	 */
	public function invoiceNumberExists(string $invoiceNumber, ?string $userId): bool {
		$qb = $this->db->getQueryBuilder();
		$qb->select($qb->createFunction('COUNT(*)'))
			->from($this->getTableName())
			->where($qb->expr()->eq('user_id', $qb->createNamedParameter($userId)))
			->andWhere($qb->expr()->eq('invoice_number', $qb->createNamedParameter($invoiceNumber)));
		
		$result = $qb->executeQuery();
		$count = (int) $result->fetchOne();
		$result->closeCursor();
		
		return $count > 0;
	}
}

