<?php
declare(strict_types=1);

namespace OCA\DomainControl\Db;

use OCP\AppFramework\Db\QBMapper;
use OCP\IDBConnection;

class TransactionCategoryMapper extends QBMapper
{
	public function __construct(IDBConnection $db)
	{
		parent::__construct($db, 'dc_tran_cats', TransactionCategory::class);
	}

	/**
	 * @param string|null $userId
	 * @return TransactionCategory[]
	 */
	public function findAll(?string $userId): array
	{
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->where($qb->expr()->orX(
				$qb->expr()->eq('is_predefined', $qb->createNamedParameter(true, \PDO::PARAM_BOOL)),
				$qb->expr()->eq('user_id', $qb->createNamedParameter($userId))
			))
			->orderBy('type', 'ASC')
			->addOrderBy('name', 'ASC');

		return $this->findEntities($qb);
	}

	/**
	 * @param string $type
	 * @param string|null $userId
	 * @return TransactionCategory[]
	 */
	public function findByType(string $type, ?string $userId): array
	{
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->where($qb->expr()->eq('type', $qb->createNamedParameter($type)))
			->andWhere($qb->expr()->orX(
				$qb->expr()->eq('is_predefined', $qb->createNamedParameter(true, \PDO::PARAM_BOOL)),
				$qb->expr()->eq('user_id', $qb->createNamedParameter($userId))
			))
			->orderBy('name', 'ASC');

		return $this->findEntities($qb);
	}

	/**
	 * @param int $id
	 * @param string|null $userId
	 * @return TransactionCategory
	 */
	public function find(int $id, ?string $userId): TransactionCategory
	{
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->where($qb->expr()->eq('id', $qb->createNamedParameter($id)))
			->andWhere($qb->expr()->orX(
				$qb->expr()->eq('is_predefined', $qb->createNamedParameter(true, \PDO::PARAM_BOOL)),
				$qb->expr()->eq('user_id', $qb->createNamedParameter($userId))
			));

		return $this->findEntity($qb);
	}
}

