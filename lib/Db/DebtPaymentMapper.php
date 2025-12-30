<?php
declare(strict_types=1);

namespace OCA\DomainControl\Db;

use OCP\AppFramework\Db\QBMapper;
use OCP\IDBConnection;

class DebtPaymentMapper extends QBMapper
{
	public function __construct(IDBConnection $db)
	{
		parent::__construct($db, 'dc_debt_pays', DebtPayment::class);
	}

	/**
	 * @param int $debtId
	 * @param string|null $userId
	 * @return DebtPayment[]
	 */
	public function findByDebt(int $debtId, ?string $userId): array
	{
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->where($qb->expr()->eq('debt_id', $qb->createNamedParameter($debtId)))
			->andWhere($qb->expr()->eq('user_id', $qb->createNamedParameter($userId)))
			->orderBy('payment_date', 'DESC');

		return $this->findEntities($qb);
	}

	/**
	 * @param int $id
	 * @param string|null $userId
	 * @return DebtPayment
	 */
	public function find(int $id, ?string $userId): DebtPayment
	{
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->where($qb->expr()->eq('id', $qb->createNamedParameter($id)))
			->andWhere($qb->expr()->eq('user_id', $qb->createNamedParameter($userId)));

		return $this->findEntity($qb);
	}

	/**
	 * Get total paid amount for a debt
	 * @param int $debtId
	 * @param string|null $userId
	 * @return float
	 */
	public function getTotalPaid(int $debtId, ?string $userId): float
	{
		$qb = $this->db->getQueryBuilder();
		$qb->selectAlias($qb->createFunction('COALESCE(SUM(amount), 0)'), 'total')
			->from($this->getTableName())
			->where($qb->expr()->eq('debt_id', $qb->createNamedParameter($debtId)))
			->andWhere($qb->expr()->eq('user_id', $qb->createNamedParameter($userId)));

		$result = $qb->executeQuery();
		$row = $result->fetch();
		$result->closeCursor();

		return (float)($row['total'] ?? 0);
	}
}

