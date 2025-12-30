<?php
namespace OCA\DomainControl\Db;

use OCP\AppFramework\Db\QBMapper;
use OCP\IDBConnection;

class OrderMapper extends QBMapper
{
    public function __construct(IDBConnection $db)
    {
        parent::__construct($db, 'dc_orders', Order::class);
    }

    public function findAll()
    {
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')
            ->from('dc_orders')
            ->orderBy('order_date', 'DESC')
            ->addOrderBy('id', 'DESC');
        return $this->findEntities($qb);
    }

    public function find(int $id): Order
    {
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')
            ->from($this->getTableName())
            ->where($qb->expr()->eq('id', $qb->createNamedParameter($id)));
        return $this->findEntity($qb);
    }

    public function findByOrderNumber(string $orderNumber): ?Order
    {
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')
            ->from($this->getTableName())
            ->where($qb->expr()->eq('order_number', $qb->createNamedParameter($orderNumber)));
        try {
            return $this->findEntity($qb);
        } catch (\Exception $e) {
            return null;
        }
    }

    public function findByClientId(int $clientId)
    {
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')
            ->from('dc_orders')
            ->where($qb->expr()->eq('client_id', $qb->createNamedParameter($clientId)))
            ->orderBy('order_date', 'DESC');
        return $this->findEntities($qb);
    }

    public function findRecentOrders($limit = 50)
    {
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')
            ->from('dc_orders')
            ->orderBy('order_date', 'DESC')
            ->addOrderBy('id', 'DESC')
            ->setMaxResults($limit);
        return $this->findEntities($qb);
    }

    public function generateOrderNumber(): string
    {
        $prefix = 'ORD';
        $date = date('Ymd');
        
        // Get the last order number for today
        $qb = $this->db->getQueryBuilder();
        $qb->select('order_number')
            ->from($this->getTableName())
            ->where($qb->expr()->like('order_number', $qb->createNamedParameter($prefix . $date . '%')))
            ->orderBy('id', 'DESC')
            ->setMaxResults(1);
        
        try {
            $result = $qb->executeQuery();
            $row = $result->fetch();
            $result->closeCursor();
            
            if ($row) {
                $lastNumber = $row['order_number'];
                $lastSequence = (int)substr($lastNumber, -4);
                $newSequence = $lastSequence + 1;
            } else {
                $newSequence = 1;
            }
        } catch (\Exception $e) {
            $newSequence = 1;
        }
        
        return $prefix . $date . str_pad($newSequence, 4, '0', STR_PAD_LEFT);
    }
}


