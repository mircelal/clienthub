<?php
declare(strict_types=1);

namespace OCA\DomainControl\Db;

use OCP\AppFramework\Db\QBMapper;
use OCP\IDBConnection;

class ProjectShareMapper extends QBMapper
{
	public function __construct(IDBConnection $db)
	{
		parent::__construct($db, 'dc_project_shares', ProjectShare::class);
	}

	/**
	 * @param int $projectId
	 * @return ProjectShare[]
	 */
	public function findByProject(int $projectId): array
	{
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->where($qb->expr()->eq('project_id', $qb->createNamedParameter($projectId)))
			->orderBy('created_at', 'DESC');

		return $this->findEntities($qb);
	}

	/**
	 * @param string $userId
	 * @return ProjectShare[]
	 */
	public function findSharedWithUser(string $userId): array
	{
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->where($qb->expr()->eq('shared_with_user_id', $qb->createNamedParameter($userId)))
			->orderBy('created_at', 'DESC');

		return $this->findEntities($qb);
	}

	/**
	 * @param int $projectId
	 * @param string $sharedWithUserId
	 * @return ProjectShare|null
	 */
	public function findByProjectAndUser(int $projectId, string $sharedWithUserId): ?ProjectShare
	{
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->where($qb->expr()->eq('project_id', $qb->createNamedParameter($projectId)))
			->andWhere($qb->expr()->eq('shared_with_user_id', $qb->createNamedParameter($sharedWithUserId)))
			->setMaxResults(1);

		try {
			return $this->findEntity($qb);
		} catch (\Exception $e) {
			return null;
		}
	}

	/**
	 * Share project with user
	 * @param int $projectId
	 * @param string $sharedWithUserId
	 * @param string $permissionLevel
	 * @param string $sharedByUserId
	 * @return ProjectShare
	 */
	public function share(int $projectId, string $sharedWithUserId, string $permissionLevel, string $sharedByUserId): ProjectShare
	{
		$share = new ProjectShare();
		$share->setProjectId($projectId);
		$share->setSharedWithUserId($sharedWithUserId);
		$share->setPermissionLevel($permissionLevel);
		$share->setSharedByUserId($sharedByUserId);
		$share->setCreatedAt(date('Y-m-d H:i:s'));

		return $this->insert($share);
	}

	/**
	 * Unshare project with user
	 * @param int $projectId
	 * @param string $sharedWithUserId
	 * @return void
	 */
	public function unshare(int $projectId, string $sharedWithUserId): void
	{
		$share = $this->findByProjectAndUser($projectId, $sharedWithUserId);
		if ($share) {
			$this->delete($share);
		}
	}
}

