<?php
declare(strict_types=1);

namespace OCA\DomainControl\Db;

use OCP\AppFramework\Db\QBMapper;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

class ProjectFileMapper extends QBMapper {
	public function __construct(IDBConnection $db) {
		parent::__construct($db, 'dc_project_files', ProjectFile::class);
	}

	/**
	 * @return ProjectFile[]
	 */
	public function findAll(int $projectId, string $userId): array {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->where($qb->expr()->eq('project_id', $qb->createNamedParameter($projectId, IQueryBuilder::PARAM_INT)))
			->andWhere($qb->expr()->eq('user_id', $qb->createNamedParameter($userId)))
			->orderBy('created_at', 'DESC');

		return $this->findEntities($qb);
	}

	/**
	 * @return ProjectFile[]
	 */
	public function findByCategory(int $projectId, string $userId, string $category): array {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->where($qb->expr()->eq('project_id', $qb->createNamedParameter($projectId, IQueryBuilder::PARAM_INT)))
			->andWhere($qb->expr()->eq('user_id', $qb->createNamedParameter($userId)))
			->andWhere($qb->expr()->eq('category', $qb->createNamedParameter($category)))
			->orderBy('created_at', 'DESC');

		return $this->findEntities($qb);
	}

	public function find(int $id, string $userId): ProjectFile {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->where($qb->expr()->eq('id', $qb->createNamedParameter($id, IQueryBuilder::PARAM_INT)))
			->andWhere($qb->expr()->eq('user_id', $qb->createNamedParameter($userId)));

		return $this->findEntity($qb);
	}
}

