<?php
declare(strict_types=1);

namespace OCA\DomainControl\Db;

use OCP\AppFramework\Db\QBMapper;
use OCP\IDBConnection;

class DomainMapper extends QBMapper {
	public function __construct(IDBConnection $db) {
		parent::__construct($db, 'domaincontrol_domains', Domain::class);
	}

	/**
	 * @param string|null $userId
	 * @return Domain[]
	 */
	public function findAll(?string $userId): array {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->where($qb->expr()->eq('user_id', $qb->createNamedParameter($userId)))
			->orderBy('expiration_date', 'ASC');
		
		return $this->findEntities($qb);
	}

	/**
	 * @param int $clientId
	 * @param string|null $userId
	 * @return Domain[]
	 */
	public function findByClient(int $clientId, ?string $userId): array {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->where($qb->expr()->eq('client_id', $qb->createNamedParameter($clientId)))
			->andWhere($qb->expr()->eq('user_id', $qb->createNamedParameter($userId)))
			->orderBy('expiration_date', 'ASC');
		
		return $this->findEntities($qb);
	}

	/**
	 * @param int $id
	 * @param string|null $userId
	 * @return Domain
	 */
	public function find(int $id, ?string $userId): Domain {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->where($qb->expr()->eq('id', $qb->createNamedParameter($id)))
			->andWhere($qb->expr()->eq('user_id', $qb->createNamedParameter($userId)));
		
		return $this->findEntity($qb);
	}

	/**
	 * Find domains expiring soon
	 * @param string|null $userId
	 * @param int $days Number of days to check ahead (default: 30)
	 * @return Domain[]
	 */
	public function findExpiringSoon(?string $userId, int $days = 30): array {
		$futureDate = date('Y-m-d', strtotime("+{$days} days"));
		$today = date('Y-m-d');
		
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->where($qb->expr()->eq('user_id', $qb->createNamedParameter($userId)))
			->andWhere($qb->expr()->lte('expiration_date', $qb->createNamedParameter($futureDate)))
			->andWhere($qb->expr()->gte('expiration_date', $qb->createNamedParameter($today)))
			->orderBy('expiration_date', 'ASC');
		
		return $this->findEntities($qb);
	}
}
