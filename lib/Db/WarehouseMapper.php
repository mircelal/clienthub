<?php

namespace OCA\DomainControl\Db;

use OCP\AppFramework\Db\QBMapper;
use OCP\IDBConnection;

class WarehouseMapper extends QBMapper
{
    public function __construct(IDBConnection $db)
    {
        parent::__construct($db, 'dc_warehouses', Warehouse::class);
    }

    public function findAll()
    {
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')
            ->from('dc_warehouses');
        return $this->findEntities($qb);
    }

    /**
     * @param int $id
     * @return Warehouse
     */
    public function find($id)
    {
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')
            ->from($this->getTableName())
            ->where($qb->expr()->eq('id', $qb->createNamedParameter($id)));
        
        return $this->findEntity($qb);
    }
}
