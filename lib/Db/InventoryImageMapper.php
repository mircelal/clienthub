<?php

namespace OCA\DomainControl\Db;

use OCP\AppFramework\Db\QBMapper;
use OCP\IDBConnection;

class InventoryImageMapper extends QBMapper
{
    public function __construct(IDBConnection $db)
    {
        parent::__construct($db, 'dc_inventory_images', InventoryImage::class);
    }

    public function findByInventoryId($inventoryId)
    {
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')
            ->from('dc_inventory_images')
            ->where($qb->expr()->eq('inventory_id', $qb->createNamedParameter($inventoryId)))
            ->orderBy('is_primary', 'DESC')
            ->addOrderBy('sort_order', 'ASC');
        return $this->findEntities($qb);
    }

    public function findPrimaryByInventoryId($inventoryId)
    {
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')
            ->from('dc_inventory_images')
            ->where($qb->expr()->eq('inventory_id', $qb->createNamedParameter($inventoryId)))
            ->andWhere($qb->expr()->eq('is_primary', $qb->createNamedParameter(1)))
            ->setMaxResults(1);
        try {
            return $this->findEntity($qb);
        } catch (\Exception $e) {
            return null;
        }
    }

    public function deleteByInventoryId($inventoryId)
    {
        $qb = $this->db->getQueryBuilder();
        $qb->delete('dc_inventory_images')
            ->where($qb->expr()->eq('inventory_id', $qb->createNamedParameter($inventoryId)));
        return $qb->execute();
    }
}


