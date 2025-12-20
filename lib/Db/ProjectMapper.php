<?php
declare(strict_types=1);

namespace OCA\DomainControl\Db;

use OCP\AppFramework\Db\QBMapper;
use OCP\IDBConnection;

class ProjectMapper extends QBMapper {
	public function __construct(IDBConnection $db) {
		parent::__construct($db, 'dc_projects', Project::class);
	}

	/**
	 * @param string|null $userId
	 * @return Project[]
	 */
	public function findAll(?string $userId): array {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->where($qb->expr()->eq('user_id', $qb->createNamedParameter($userId)))
			->orderBy('created_at', 'DESC');
		
		return $this->findEntities($qb);
	}

	/**
	 * @param int $clientId
	 * @param string|null $userId
	 * @return Project[]
	 */
	public function findByClient(int $clientId, ?string $userId): array {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->where($qb->expr()->eq('client_id', $qb->createNamedParameter($clientId)))
			->andWhere($qb->expr()->eq('user_id', $qb->createNamedParameter($userId)))
			->orderBy('created_at', 'DESC');
		
		return $this->findEntities($qb);
	}

	/**
	 * @param int $id
	 * @param string|null $userId
	 * @return Project
	 */
	public function find(int $id, ?string $userId): Project {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->where($qb->expr()->eq('id', $qb->createNamedParameter($id)))
			->andWhere($qb->expr()->eq('user_id', $qb->createNamedParameter($userId)));
		
		return $this->findEntity($qb);
	}

	/**
	 * Find active projects
	 */
	public function findActive(?string $userId): array {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->where($qb->expr()->eq('user_id', $qb->createNamedParameter($userId)))
			->andWhere($qb->expr()->eq('status', $qb->createNamedParameter('active')))
			->orderBy('deadline', 'ASC');
		
		return $this->findEntities($qb);
	}

	/**
	 * Find projects with approaching deadlines
	 */
	public function findApproachingDeadline(?string $userId, int $days = 7): array {
		$futureDate = date('Y-m-d', strtotime("+{$days} days"));
		$today = date('Y-m-d');
		
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->where($qb->expr()->eq('user_id', $qb->createNamedParameter($userId)))
			->andWhere($qb->expr()->eq('status', $qb->createNamedParameter('active')))
			->andWhere($qb->expr()->lte('deadline', $qb->createNamedParameter($futureDate)))
			->andWhere($qb->expr()->gte('deadline', $qb->createNamedParameter($today)))
			->orderBy('deadline', 'ASC');
		
		return $this->findEntities($qb);
	}

	/**
	 * Find all projects including shared ones
	 * @param string|null $userId
	 * @return Project[]
	 */
	public function findAllIncludingShared(?string $userId): array {
		$qb = $this->db->getQueryBuilder();
		
		// Get owned projects
		$qb->select('p.*')
			->from($this->getTableName(), 'p')
			->where($qb->expr()->eq('p.user_id', $qb->createNamedParameter($userId)));
		
		// Union with shared projects
		$qb2 = $this->db->getQueryBuilder();
		$qb2->select('p.*')
			->from($this->getTableName(), 'p')
			->innerJoin('p', 'dc_project_shares', 'ps', $qb2->expr()->eq('p.id', 'ps.project_id'))
			->where($qb2->expr()->eq('ps.shared_with_user_id', $qb2->createNamedParameter($userId)));
		
		// Combine results
		$ownedProjects = $this->findEntities($qb);
		$sharedProjects = $this->findEntities($qb2);
		
		// Merge and remove duplicates
		$allProjects = [];
		$projectIds = [];
		foreach ($ownedProjects as $project) {
			$allProjects[] = $project;
			$projectIds[$project->getId()] = true;
		}
		foreach ($sharedProjects as $project) {
			if (!isset($projectIds[$project->getId()])) {
				$allProjects[] = $project;
				$projectIds[$project->getId()] = true;
			}
		}
		
		// Sort by created_at DESC
		usort($allProjects, function($a, $b) {
			return strcmp($b->getCreatedAt(), $a->getCreatedAt());
		});
		
		return $allProjects;
	}

	/**
	 * Find project including shared ones
	 * @param int $id
	 * @param string|null $userId
	 * @return Project
	 * @throws \OCP\AppFramework\Db\DoesNotExistException
	 */
	public function findIncludingShared(int $id, ?string $userId): Project {
		// First try as owner
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->where($qb->expr()->eq('id', $qb->createNamedParameter($id)))
			->andWhere($qb->expr()->eq('user_id', $qb->createNamedParameter($userId)))
			->setMaxResults(1);
		
		try {
			return $this->findEntity($qb);
		} catch (\OCP\AppFramework\Db\DoesNotExistException $e) {
			// Try as shared project
			$qb2 = $this->db->getQueryBuilder();
			$qb2->select('p.*')
				->from($this->getTableName(), 'p')
				->innerJoin('p', 'dc_project_shares', 'ps', $qb2->expr()->eq('p.id', 'ps.project_id'))
				->where($qb2->expr()->eq('p.id', $qb2->createNamedParameter($id)))
				->andWhere($qb2->expr()->eq('ps.shared_with_user_id', $qb2->createNamedParameter($userId)))
				->setMaxResults(1);
			
			return $this->findEntity($qb2);
		}
	}

	/**
	 * Check if user has access to project
	 * @param int $projectId
	 * @param string $userId
	 * @param string $permission
	 * @return bool
	 */
	public function hasAccess(int $projectId, string $userId, string $permission = 'read'): bool {
		// Check if user is owner
		$qb = $this->db->getQueryBuilder();
		$qb->select('id')
			->from($this->getTableName())
			->where($qb->expr()->eq('id', $qb->createNamedParameter($projectId)))
			->andWhere($qb->expr()->eq('user_id', $qb->createNamedParameter($userId)))
			->setMaxResults(1);
		
		$result = $qb->executeQuery();
		$row = $result->fetch();
		$result->closeCursor();
		
		if ($row) {
			return true; // Owner has all permissions
		}
		
		// Check if user has shared access
		$qb2 = $this->db->getQueryBuilder();
		$qb2->select('permission_level')
			->from('dc_project_shares')
			->where($qb2->expr()->eq('project_id', $qb2->createNamedParameter($projectId)))
			->andWhere($qb2->expr()->eq('shared_with_user_id', $qb2->createNamedParameter($userId)))
			->setMaxResults(1);
		
		$result2 = $qb2->executeQuery();
		$row2 = $result2->fetch();
		$result2->closeCursor();
		
		if (!$row2) {
			return false; // No access
		}
		
		// If permission is 'read', any access is enough
		if ($permission === 'read') {
			return true;
		}
		
		// For 'write' permission, check if user has write access
		return $row2['permission_level'] === 'write';
	}
}

