<?php
declare(strict_types=1);

namespace OCA\DomainControl\Db;

use OCP\AppFramework\Db\QBMapper;
use OCP\IDBConnection;

class InvoiceItemMapper extends QBMapper {
	public function __construct(IDBConnection $db) {
		parent::__construct($db, 'dc_invoice_items', InvoiceItem::class);
	}

	/**
	 * @param int $invoiceId
	 * @return InvoiceItem[]
	 */
	public function findByInvoice(int $invoiceId): array {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->where($qb->expr()->eq('invoice_id', $qb->createNamedParameter($invoiceId)))
			->orderBy('id', 'ASC');
		
		return $this->findEntities($qb);
	}

	/**
	 * @param int $id
	 * @return InvoiceItem
	 */
	public function find(int $id): InvoiceItem {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->where($qb->expr()->eq('id', $qb->createNamedParameter($id)));
		
		return $this->findEntity($qb);
	}

	/**
	 * Delete all items for an invoice
	 */
	public function deleteByInvoice(int $invoiceId): void {
		$qb = $this->db->getQueryBuilder();
		$qb->delete($this->getTableName())
			->where($qb->expr()->eq('invoice_id', $qb->createNamedParameter($invoiceId)));
		$qb->executeStatement();
	}
}

