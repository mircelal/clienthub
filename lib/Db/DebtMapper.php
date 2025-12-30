<?php
declare(strict_types=1);

namespace OCA\DomainControl\Db;

use OCP\AppFramework\Db\QBMapper;
use OCP\IDBConnection;

class DebtMapper extends QBMapper
{
	public function __construct(IDBConnection $db)
	{
		parent::__construct($db, 'dc_debts', Debt::class);
	}

	/**
	 * @param string|null $userId
	 * @return Debt[]
	 */
	public function findAll(?string $userId): array
	{
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->where($qb->expr()->eq('user_id', $qb->createNamedParameter($userId)))
			->orderBy('next_payment_date', 'ASC')
			->addOrderBy('due_date', 'ASC');

		return $this->findEntities($qb);
	}

	/**
	 * @param string $type
	 * @param string|null $userId
	 * @return Debt[]
	 */
	public function findByType(string $type, ?string $userId): array
	{
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->where($qb->expr()->eq('user_id', $qb->createNamedParameter($userId)))
			->andWhere($qb->expr()->eq('type', $qb->createNamedParameter($type)))
			->orderBy('next_payment_date', 'ASC');

		return $this->findEntities($qb);
	}

	/**
	 * @param string $status
	 * @param string|null $userId
	 * @return Debt[]
	 */
	public function findByStatus(string $status, ?string $userId): array
	{
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->where($qb->expr()->eq('user_id', $qb->createNamedParameter($userId)))
			->andWhere($qb->expr()->eq('status', $qb->createNamedParameter($status)))
			->orderBy('next_payment_date', 'ASC');

		return $this->findEntities($qb);
	}

	/**
	 * @param int $id
	 * @param string|null $userId
	 * @return Debt
	 */
	public function find(int $id, ?string $userId): Debt
	{
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->where($qb->expr()->eq('id', $qb->createNamedParameter($id)))
			->andWhere($qb->expr()->eq('user_id', $qb->createNamedParameter($userId)));

		return $this->findEntity($qb);
	}

	/**
	 * Find upcoming payments
	 * @param int $days
	 * @param string|null $userId
	 * @return Debt[]
	 */
	public function findUpcomingPayments(int $days, ?string $userId): array
	{
		$today = date('Y-m-d');
		$futureDate = date('Y-m-d', strtotime("+{$days} days"));

		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->where($qb->expr()->eq('user_id', $qb->createNamedParameter($userId)))
			->andWhere($qb->expr()->eq('status', $qb->createNamedParameter('active')))
			->andWhere($qb->expr()->isNotNull('next_payment_date'))
			->andWhere($qb->expr()->lte('next_payment_date', $qb->createNamedParameter($futureDate)))
			->andWhere($qb->expr()->gte('next_payment_date', $qb->createNamedParameter($today)))
			->orderBy('next_payment_date', 'ASC');

		return $this->findEntities($qb);
	}

	/**
	 * Find overdue debts
	 * @param string|null $userId
	 * @return Debt[]
	 */
	public function findOverdue(?string $userId): array
	{
		$today = date('Y-m-d');

		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->where($qb->expr()->eq('user_id', $qb->createNamedParameter($userId)))
			->andWhere($qb->expr()->eq('status', $qb->createNamedParameter('active')))
			->andWhere($qb->expr()->orX(
				$qb->expr()->andX(
					$qb->expr()->isNotNull('next_payment_date'),
					$qb->expr()->lt('next_payment_date', $qb->createNamedParameter($today))
				),
				$qb->expr()->andX(
					$qb->expr()->isNotNull('due_date'),
					$qb->expr()->lt('due_date', $qb->createNamedParameter($today))
				)
			))
			->orderBy('next_payment_date', 'ASC');

		return $this->findEntities($qb);
	}
}

