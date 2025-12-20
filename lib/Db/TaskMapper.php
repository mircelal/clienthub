<?php
declare(strict_types=1);

namespace OCA\DomainControl\Db;

use OCP\AppFramework\Db\QBMapper;
use OCP\IDBConnection;

class TaskMapper extends QBMapper
{
	public function __construct(IDBConnection $db)
	{
		parent::__construct($db, 'dc_tasks', Task::class);
	}

	/**
	 * @param string|null $userId
	 * @return Task[]
	 */
	public function findAll(?string $userId): array
	{
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->where($qb->expr()->eq('user_id', $qb->createNamedParameter($userId)))
			->orderBy('due_date', 'ASC');

		return $this->findEntities($qb);
	}

	/**
	 * @param int $projectId
	 * @param string|null $userId
	 * @return Task[]
	 */
	public function findByProject(int $projectId, ?string $userId): array
	{
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->where($qb->expr()->eq('project_id', $qb->createNamedParameter($projectId)))
			->andWhere($qb->expr()->eq('user_id', $qb->createNamedParameter($userId)))
			->orderBy('due_date', 'ASC');

		return $this->findEntities($qb);
	}

	/**
	 * @param int $clientId
	 * @param string|null $userId
	 * @return Task[]
	 */
	public function findByClient(int $clientId, ?string $userId): array
	{
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->where($qb->expr()->eq('client_id', $qb->createNamedParameter($clientId)))
			->andWhere($qb->expr()->eq('user_id', $qb->createNamedParameter($userId)))
			->orderBy('due_date', 'ASC');

		return $this->findEntities($qb);
	}

	/**
	 * @param int $id
	 * @param string|null $userId
	 * @return Task
	 */
	public function find(int $id, ?string $userId): Task
	{
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->where($qb->expr()->eq('id', $qb->createNamedParameter($id)))
			->andWhere($qb->expr()->eq('user_id', $qb->createNamedParameter($userId)));

		return $this->findEntity($qb);
	}

	/**
	 * Find pending tasks (todo or in_progress)
	 */
	public function findPending(?string $userId): array
	{
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->where($qb->expr()->eq('user_id', $qb->createNamedParameter($userId)))
			->andWhere($qb->expr()->in('status', $qb->createNamedParameter(['todo', 'in_progress'], \Doctrine\DBAL\Connection::PARAM_STR_ARRAY)))
			->orderBy('due_date', 'ASC');

		return $this->findEntities($qb);
	}

	/**
	 * Find tasks with approaching deadlines
	 */
	public function findApproachingDeadline(?string $userId, int $days = 7): array
	{
		$futureDate = date('Y-m-d', strtotime("+{$days} days"));
		$today = date('Y-m-d');

		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->where($qb->expr()->eq('user_id', $qb->createNamedParameter($userId)))
			->andWhere($qb->expr()->in('status', $qb->createNamedParameter(['todo', 'in_progress'], \Doctrine\DBAL\Connection::PARAM_STR_ARRAY)))
			->andWhere($qb->expr()->lte('due_date', $qb->createNamedParameter($futureDate)))
			->andWhere($qb->expr()->gte('due_date', $qb->createNamedParameter($today)))
			->orderBy('due_date', 'ASC');

		return $this->findEntities($qb);
	}

	/**
	 * Find overdue tasks
	 */
	public function findOverdue(?string $userId): array
	{
		$today = date('Y-m-d');

		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->where($qb->expr()->eq('user_id', $qb->createNamedParameter($userId)))
			->andWhere($qb->expr()->in('status', $qb->createNamedParameter(['todo', 'in_progress'], \Doctrine\DBAL\Connection::PARAM_STR_ARRAY)))
			->andWhere($qb->expr()->lt('due_date', $qb->createNamedParameter($today)))
			->andWhere($qb->expr()->isNotNull('due_date'))
			->orderBy('due_date', 'ASC');

		return $this->findEntities($qb);
	}

	/**
	 * @param int $parentId
	 * @param string|null $userId
	 * @return Task[]
	 */
	public function findSubtasks(int $parentId, ?string $userId): array
	{
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->where($qb->expr()->eq('parent_id', $qb->createNamedParameter($parentId)))
			->andWhere($qb->expr()->eq('user_id', $qb->createNamedParameter($userId)))
			->orderBy('created_at', 'ASC');

		return $this->findEntities($qb);
	}
}

