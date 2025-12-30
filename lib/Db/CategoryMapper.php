<?php

namespace OCA\DomainControl\Db;

use OCP\AppFramework\Db\QBMapper;
use OCP\IDBConnection;

class CategoryMapper extends QBMapper
{
    public function __construct(IDBConnection $db)
    {
        parent::__construct($db, 'dc_inv_categories', Category::class);
    }

    public function findAll()
    {
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')
            ->from('dc_inv_categories');
        return $this->findEntities($qb);
    }

    /**
     * @param int $id
     * @return Category
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
