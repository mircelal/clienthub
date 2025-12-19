<?php
declare(strict_types=1);

namespace OCA\DomainControl\Db;

use OCP\AppFramework\Db\QBMapper;
use OCP\IDBConnection;

class ServiceMapper extends QBMapper {
	public function __construct(IDBConnection $db) {
		parent::__construct($db, 'dc_services', Service::class);
	}

	/**
	 * @param string|null $userId
	 * @return Service[]
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
	 * @return Service[]
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
	 * @return Service
	 */
	public function find(int $id, ?string $userId): Service {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->where($qb->expr()->eq('id', $qb->createNamedParameter($id)))
			->andWhere($qb->expr()->eq('user_id', $qb->createNamedParameter($userId)));
		
		return $this->findEntity($qb);
	}

	/**
	 * Find services expiring soon
	 * Excludes one-time services
	 */
	public function findExpiringSoon(?string $userId, int $days = 30): array {
		$futureDate = date('Y-m-d', strtotime("+{$days} days"));
		$today = date('Y-m-d');
		
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->where($qb->expr()->eq('user_id', $qb->createNamedParameter($userId)))
			->andWhere($qb->expr()->neq('renewal_interval', $qb->createNamedParameter('one-time')))
			->andWhere($qb->expr()->lte('expiration_date', $qb->createNamedParameter($futureDate)))
			->andWhere($qb->expr()->gte('expiration_date', $qb->createNamedParameter($today)))
			->andWhere($qb->expr()->eq('status', $qb->createNamedParameter('active')))
			->orderBy('expiration_date', 'ASC');
		
		return $this->findEntities($qb);
	}
}

