<?php

namespace OCA\DomainControl\Db;

use OCP\AppFramework\Db\QBMapper;
use OCP\IDBConnection;
use OCA\DomainControl\Db\Inventory;

class InventoryMapper extends QBMapper
{
    public function __construct(IDBConnection $db)
    {
        parent::__construct($db, 'dc_inventory', Inventory::class);
    }

    public function findAll()
    {
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')
            ->from('dc_inventory');
        return $this->findEntities($qb);
    }

    public function findByStatus($status)
    {
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')
            ->from('dc_inventory')
            ->where($qb->expr()->eq('status', $qb->createNamedParameter($status)));
        return $this->findEntities($qb);
    }

    public function findByCategory($categoryId)
    {
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')
            ->from('dc_inventory')
            ->where($qb->expr()->eq('category_id', $qb->createNamedParameter($categoryId, \PDO::PARAM_INT)));
        return $this->findEntities($qb);
    }

    public function findByWarehouse($warehouseId)
    {
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')
            ->from('dc_inventory')
            ->where($qb->expr()->eq('warehouse_id', $qb->createNamedParameter($warehouseId, \PDO::PARAM_INT)));
        return $this->findEntities($qb);
    }

    public function find(int $id): Inventory
    {
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')
            ->from($this->getTableName())
            ->where($qb->expr()->eq('id', $qb->createNamedParameter($id)));
        return $this->findEntity($qb);
    }
}
