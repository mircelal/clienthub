<?php
declare(strict_types=1);

namespace OCA\DomainControl\Db;

use OCP\AppFramework\Db\QBMapper;
use OCP\IDBConnection;

class PaymentMapper extends QBMapper {
	public function __construct(IDBConnection $db) {
		parent::__construct($db, 'dc_payments', Payment::class);
	}

	/**
	 * @param string|null $userId
	 * @return Payment[]
	 */
	public function findAll(?string $userId): array {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->where($qb->expr()->eq('user_id', $qb->createNamedParameter($userId)))
			->orderBy('payment_date', 'DESC');
		
		return $this->findEntities($qb);
	}

	/**
	 * @param int $clientId
	 * @param string|null $userId
	 * @return Payment[]
	 */
	public function findByClient(int $clientId, ?string $userId): array {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->where($qb->expr()->eq('client_id', $qb->createNamedParameter($clientId)))
			->andWhere($qb->expr()->eq('user_id', $qb->createNamedParameter($userId)))
			->orderBy('payment_date', 'DESC');
		
		return $this->findEntities($qb);
	}

	/**
	 * @param int $invoiceId
	 * @param string|null $userId
	 * @return Payment[]
	 */
	public function findByInvoice(int $invoiceId, ?string $userId): array {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->where($qb->expr()->eq('invoice_id', $qb->createNamedParameter($invoiceId)))
			->andWhere($qb->expr()->eq('user_id', $qb->createNamedParameter($userId)))
			->orderBy('payment_date', 'DESC');
		
		return $this->findEntities($qb);
	}

	/**
	 * @param int $id
	 * @param string|null $userId
	 * @return Payment
	 */
	public function find(int $id, ?string $userId): Payment {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->where($qb->expr()->eq('id', $qb->createNamedParameter($id)))
			->andWhere($qb->expr()->eq('user_id', $qb->createNamedParameter($userId)));
		
		return $this->findEntity($qb);
	}

	/**
	 * Get total payments for current month
	 */
	public function getTotalThisMonth(?string $userId): float {
		$startOfMonth = date('Y-m-01');
		$endOfMonth = date('Y-m-t');
		
		$qb = $this->db->getQueryBuilder();
		$qb->select($qb->createFunction('COALESCE(SUM(amount), 0) as total'))
			->from($this->getTableName())
			->where($qb->expr()->eq('user_id', $qb->createNamedParameter($userId)))
			->andWhere($qb->expr()->gte('payment_date', $qb->createNamedParameter($startOfMonth)))
			->andWhere($qb->expr()->lte('payment_date', $qb->createNamedParameter($endOfMonth)));
		
		$result = $qb->executeQuery();
		$total = (float) $result->fetchOne();
		$result->closeCursor();
		
		return $total;
	}
}

