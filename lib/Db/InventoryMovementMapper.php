<?php
namespace OCA\DomainControl\Db;

use OCP\AppFramework\Db\QBMapper;
use OCP\IDBConnection;

class InventoryMovementMapper extends QBMapper
{
    public function __construct(IDBConnection $db)
    {
        parent::__construct($db, 'dc_inv_movements', InventoryMovement::class);
    }

    public function findAll()
    {
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')
            ->from('dc_inv_movements')
            ->orderBy('date_out', 'DESC');
        return $this->findEntities($qb);
    }

    public function findByInventoryId($inventoryId)
    {
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')
            ->from('dc_inv_movements')
            ->where($qb->expr()->eq('inventory_id', $qb->createNamedParameter($inventoryId)))
            ->orderBy('date_out', 'DESC');
        return $this->findEntities($qb);
    }

    public function findByClientId($clientId)
    {
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')
            ->from('dc_inv_movements')
            ->where($qb->expr()->eq('client_id', $qb->createNamedParameter($clientId)))
            ->orderBy('date_out', 'DESC');
        return $this->findEntities($qb);
    }

    public function findRecentSales($limit = 50)
    {
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')
            ->from('dc_inv_movements')
            ->where($qb->expr()->in('type', $qb->createNamedParameter(['sale', 'rent'], \Doctrine\DBAL\Connection::PARAM_STR_ARRAY)))
            ->orderBy('date_out', 'DESC')
            ->setMaxResults($limit);
        return $this->findEntities($qb);
    }

    public function findActiveRentals()
    {
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')
            ->from('dc_inv_movements')
            ->where($qb->expr()->eq('type', $qb->createNamedParameter('rent')))
            ->andWhere($qb->expr()->isNull('date_returned'))
            ->orderBy('date_due', 'ASC');
        return $this->findEntities($qb);
    }

    public function findByOrderId($orderId)
    {
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')
            ->from('dc_inv_movements')
            ->where($qb->expr()->eq('order_id', $qb->createNamedParameter($orderId)))
            ->orderBy('id', 'ASC');
        return $this->findEntities($qb);
    }

    public function findReturns()
    {
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')
            ->from('dc_inv_movements')
            ->where($qb->expr()->isNotNull('date_returned'))
            ->orderBy('date_returned', 'DESC');
        return $this->findEntities($qb);
    }

    public function find(int $id): InventoryMovement
    {
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')
            ->from($this->getTableName())
            ->where($qb->expr()->eq('id', $qb->createNamedParameter($id)));
        return $this->findEntity($qb);
    }
}

