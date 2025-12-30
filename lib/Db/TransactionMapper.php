<?php
declare(strict_types=1);

namespace OCA\DomainControl\Db;

use OCP\AppFramework\Db\QBMapper;
use OCP\IDBConnection;

class TransactionMapper extends QBMapper
{
	public function __construct(IDBConnection $db)
	{
		parent::__construct($db, 'dc_transactions', Transaction::class);
	}

	/**
	 * @param string|null $userId
	 * @return Transaction[]
	 */
	public function findAll(?string $userId): array
	{
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->where($qb->expr()->eq('user_id', $qb->createNamedParameter($userId)))
			->orderBy('transaction_date', 'DESC')
			->addOrderBy('created_at', 'DESC');

		return $this->findEntities($qb);
	}

	/**
	 * @param string $type
	 * @param string|null $userId
	 * @return Transaction[]
	 */
	public function findByType(string $type, ?string $userId): array
	{
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->where($qb->expr()->eq('user_id', $qb->createNamedParameter($userId)))
			->andWhere($qb->expr()->eq('type', $qb->createNamedParameter($type)))
			->orderBy('transaction_date', 'DESC');

		return $this->findEntities($qb);
	}

	/**
	 * @param int $categoryId
	 * @param string|null $userId
	 * @return Transaction[]
	 */
	public function findByCategory(int $categoryId, ?string $userId): array
	{
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->where($qb->expr()->eq('user_id', $qb->createNamedParameter($userId)))
			->andWhere($qb->expr()->eq('category_id', $qb->createNamedParameter($categoryId)))
			->orderBy('transaction_date', 'DESC');

		return $this->findEntities($qb);
	}

	/**
	 * @param int $projectId
	 * @param string|null $userId
	 * @return Transaction[]
	 */
	public function findByProject(int $projectId, ?string $userId): array
	{
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->where($qb->expr()->eq('user_id', $qb->createNamedParameter($userId)))
			->andWhere($qb->expr()->eq('project_id', $qb->createNamedParameter($projectId)))
			->orderBy('transaction_date', 'DESC');

		return $this->findEntities($qb);
	}

	/**
	 * @param int $clientId
	 * @param string|null $userId
	 * @return Transaction[]
	 */
	public function findByClient(int $clientId, ?string $userId): array
	{
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->where($qb->expr()->eq('user_id', $qb->createNamedParameter($userId)))
			->andWhere($qb->expr()->eq('client_id', $qb->createNamedParameter($clientId)))
			->orderBy('transaction_date', 'DESC');

		return $this->findEntities($qb);
	}

	/**
	 * @param int $invoiceId
	 * @param string|null $userId
	 * @return Transaction[]
	 */
	public function findByInvoice(int $invoiceId, ?string $userId): array
	{
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->where($qb->expr()->eq('user_id', $qb->createNamedParameter($userId)))
			->andWhere($qb->expr()->eq('invoice_id', $qb->createNamedParameter($invoiceId)))
			->orderBy('transaction_date', 'DESC');

		return $this->findEntities($qb);
	}

	/**
	 * @param int $id
	 * @param string|null $userId
	 * @return Transaction
	 */
	public function find(int $id, ?string $userId): Transaction
	{
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->where($qb->expr()->eq('id', $qb->createNamedParameter($id)))
			->andWhere($qb->expr()->eq('user_id', $qb->createNamedParameter($userId)));

		return $this->findEntity($qb);
	}

	/**
	 * Get monthly summary
	 * @param string $yearMonth Format: YYYY-MM
	 * @param string|null $userId
	 * @return array
	 */
	public function getMonthlySummary(string $yearMonth, ?string $userId): array
	{
		try {
			// Parse yearMonth (format: YYYY-MM) and create date range
			$parts = explode('-', $yearMonth);
			$year = (int)($parts[0] ?? date('Y'));
			$month = (int)($parts[1] ?? date('m'));
			$startDate = sprintf('%04d-%02d-01', $year, $month);
			$endDate = date('Y-m-t', strtotime($startDate)); // Last day of month
			
			$qb = $this->db->getQueryBuilder();
			$qb->selectAlias($qb->createFunction('COALESCE(SUM(CASE WHEN type = \'income\' THEN amount ELSE 0 END), 0)'), 'total_income')
				->addSelectAlias($qb->createFunction('COALESCE(SUM(CASE WHEN type = \'expense\' THEN amount ELSE 0 END), 0)'), 'total_expense')
				->from($this->getTableName())
				->where($qb->expr()->eq('user_id', $qb->createNamedParameter($userId)))
				->andWhere($qb->expr()->gte('transaction_date', $qb->createNamedParameter($startDate)))
				->andWhere($qb->expr()->lte('transaction_date', $qb->createNamedParameter($endDate)));

			$result = $qb->executeQuery();
			$row = $result->fetch();
			$result->closeCursor();

			return [
				'totalIncome' => (float)($row['total_income'] ?? 0),
				'totalExpense' => (float)($row['total_expense'] ?? 0),
				'net' => (float)($row['total_income'] ?? 0) - (float)($row['total_expense'] ?? 0),
			];
		} catch (\Throwable $e) {
			// Return empty summary if table doesn't exist or query fails
			return [
				'totalIncome' => 0,
				'totalExpense' => 0,
				'net' => 0
			];
		}
	}

	/**
	 * Get summary by category
	 * @param string $yearMonth Format: YYYY-MM
	 * @param string $type 'income' or 'expense'
	 * @param string|null $userId
	 * @return array
	 */
	public function getSummaryByCategory(string $yearMonth, string $type, ?string $userId): array
	{
		try {
			// Parse yearMonth (format: YYYY-MM) and create date range
			$parts = explode('-', $yearMonth);
			$year = (int)($parts[0] ?? date('Y'));
			$month = (int)($parts[1] ?? date('m'));
			$startDate = sprintf('%04d-%02d-01', $year, $month);
			$endDate = date('Y-m-t', strtotime($startDate)); // Last day of month
			
			$qb = $this->db->getQueryBuilder();
			$qb->select('category_id')
				->addSelectAlias($qb->createFunction('COALESCE(SUM(amount), 0)'), 'total')
				->from($this->getTableName())
				->where($qb->expr()->eq('user_id', $qb->createNamedParameter($userId)))
				->andWhere($qb->expr()->eq('type', $qb->createNamedParameter($type)))
				->andWhere($qb->expr()->gte('transaction_date', $qb->createNamedParameter($startDate)))
				->andWhere($qb->expr()->lte('transaction_date', $qb->createNamedParameter($endDate)))
				->groupBy('category_id')
				->orderBy('total', 'DESC');

			$result = $qb->executeQuery();
			$rows = $result->fetchAll();
			$result->closeCursor();

			return $rows;
		} catch (\Throwable $e) {
			// Return empty array if table doesn't exist or query fails
			return [];
		}
	}
}

