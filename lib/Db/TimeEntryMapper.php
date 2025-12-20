<?php
declare(strict_types=1);

namespace OCA\DomainControl\Db;

use OCP\AppFramework\Db\QBMapper;
use OCP\IDBConnection;

class TimeEntryMapper extends QBMapper {
	public function __construct(IDBConnection $db) {
		parent::__construct($db, 'dc_time_entries', TimeEntry::class);
	}

	/**
	 * @param int $projectId
	 * @param string|null $userId (not used for filtering, but kept for compatibility)
	 * @return TimeEntry[]
	 */
	public function findByProject(int $projectId, ?string $userId): array {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->where($qb->expr()->eq('project_id', $qb->createNamedParameter($projectId)))
			->orderBy('start_time', 'DESC');
		
		return $this->findEntities($qb);
	}

	/**
	 * @param int $projectId
	 * @param string|null $userId
	 * @return TimeEntry|null
	 */
	public function findRunning(int $projectId, ?string $userId): ?TimeEntry {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->where($qb->expr()->eq('project_id', $qb->createNamedParameter($projectId)))
			->andWhere($qb->expr()->eq('user_id', $qb->createNamedParameter($userId)))
			->andWhere($qb->expr()->eq('is_running', $qb->createNamedParameter(true, \PDO::PARAM_BOOL)))
			->setMaxResults(1);
		
		try {
			return $this->findEntity($qb);
		} catch (\Exception $e) {
			return null;
		}
	}

	/**
	 * @param int $id
	 * @param string|null $userId
	 * @return TimeEntry
	 */
	public function find(int $id, ?string $userId): TimeEntry {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->where($qb->expr()->eq('id', $qb->createNamedParameter($id)))
			->andWhere($qb->expr()->eq('user_id', $qb->createNamedParameter($userId)));
		
		return $this->findEntity($qb);
	}

	/**
	 * @param int $projectId
	 * @param string|null $userId (not used for filtering, but kept for compatibility)
	 * @return int Total duration in seconds (all users combined)
	 */
	public function getTotalDuration(int $projectId, ?string $userId): int {
		$qb = $this->db->getQueryBuilder();
		$qb->selectAlias($qb->createFunction('COALESCE(SUM(duration), 0)'), 'total')
			->from($this->getTableName())
			->where($qb->expr()->eq('project_id', $qb->createNamedParameter($projectId)))
			->andWhere($qb->expr()->eq('is_running', $qb->createNamedParameter(false, \PDO::PARAM_BOOL)));
		
		$result = $qb->executeQuery();
		$row = $result->fetch();
		$result->closeCursor();
		
		return (int)($row['total'] ?? 0);
	}
}

