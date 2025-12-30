<?php
declare(strict_types=1);

namespace OCA\DomainControl\Db;

use OCP\AppFramework\Db\QBMapper;
use OCP\IDBConnection;

class ProjectItemMapper extends QBMapper {
	public function __construct(IDBConnection $db) {
		parent::__construct($db, 'dc_project_items', ProjectItem::class);
	}

	/**
	 * @param int $projectId
	 * @return ProjectItem[]
	 */
	public function findByProject(int $projectId): array {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->where($qb->expr()->eq('project_id', $qb->createNamedParameter($projectId)))
			->orderBy('created_at', 'ASC');
		
		return $this->findEntities($qb);
	}

	/**
	 * @param int $id
	 * @return ProjectItem
	 */
	public function find(int $id): ProjectItem {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->where($qb->expr()->eq('id', $qb->createNamedParameter($id)));
		
		return $this->findEntity($qb);
	}

	/**
	 * Check if item is already linked to project
	 */
	public function exists(int $projectId, string $itemType, int $itemId): bool {
		$qb = $this->db->getQueryBuilder();
		$qb->select($qb->createFunction('COUNT(*)'))
			->from($this->getTableName())
			->where($qb->expr()->eq('project_id', $qb->createNamedParameter($projectId)))
			->andWhere($qb->expr()->eq('item_type', $qb->createNamedParameter($itemType)))
			->andWhere($qb->expr()->eq('item_id', $qb->createNamedParameter($itemId)));
		
		$result = $qb->executeQuery();
		$count = (int) $result->fetchOne();
		$result->closeCursor();
		
		return $count > 0;
	}

	/**
	 * Delete all items for a project
	 */
	public function deleteByProject(int $projectId): void {
		$qb = $this->db->getQueryBuilder();
		$qb->delete($this->getTableName())
			->where($qb->expr()->eq('project_id', $qb->createNamedParameter($projectId)));
		$qb->executeStatement();
	}
}

