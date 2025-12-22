<?php
declare(strict_types=1);

namespace OCA\DomainControl\Db;

use OCP\AppFramework\Db\QBMapper;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

class ProjectActivityMapper extends QBMapper {
	public function __construct(IDBConnection $db) {
		parent::__construct($db, 'dc_project_activities', ProjectActivity::class);
	}

	/**
	 * @return ProjectActivity[]
	 */
	public function findAll(int $projectId): array {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->where($qb->expr()->eq('project_id', $qb->createNamedParameter($projectId, IQueryBuilder::PARAM_INT)))
			->orderBy('created_at', 'DESC')
			->setMaxResults(100);

		return $this->findEntities($qb);
	}

	/**
	 * @return ProjectActivity[]
	 */
	public function findByType(int $projectId, string $activityType): array {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->where($qb->expr()->eq('project_id', $qb->createNamedParameter($projectId, IQueryBuilder::PARAM_INT)))
			->andWhere($qb->expr()->eq('activity_type', $qb->createNamedParameter($activityType)))
			->orderBy('created_at', 'DESC');

		return $this->findEntities($qb);
	}

	public function find(int $id): ProjectActivity {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->where($qb->expr()->eq('id', $qb->createNamedParameter($id, IQueryBuilder::PARAM_INT)));

		return $this->findEntity($qb);
	}
}

