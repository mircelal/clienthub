<?php
declare(strict_types=1);

namespace OCA\DomainControl\Db;

use OCP\AppFramework\Db\Entity;

/**
 * @method int getClientId()
 * @method void setClientId(int $clientId)
 * @method string getInvoiceNumber()
 * @method void setInvoiceNumber(string $invoiceNumber)
 * @method string getTitle()
 * @method void setTitle(string $title)
 * @method string getIssueDate()
 * @method void setIssueDate(string $issueDate)
 * @method string getDueDate()
 * @method void setDueDate(string $dueDate)
 * @method float getTotalAmount()
 * @method void setTotalAmount(float $totalAmount)
 * @method float getPaidAmount()
 * @method void setPaidAmount(float $paidAmount)
 * @method string getCurrency()
 * @method void setCurrency(string $currency)
 * @method string getStatus()
 * @method void setStatus(string $status)
 * @method string getNotes()
 * @method void setNotes(string $notes)
 * @method string getUserId()
 * @method void setUserId(string $userId)
 * @method string getCreatedAt()
 * @method void setCreatedAt(string $createdAt)
 * @method string getUpdatedAt()
 * @method void setUpdatedAt(string $updatedAt)
 */
class Invoice extends Entity implements \JsonSerializable {
	protected $clientId;
	protected $invoiceNumber;
	protected $title;
	protected $issueDate;
	protected $dueDate;
	protected $totalAmount;
	protected $paidAmount;
	protected $currency;
	protected $status;
	protected $notes;
	protected $userId;
	protected $createdAt;
	protected $updatedAt;

	public function __construct() {
		$this->addType('clientId', 'integer');
		$this->addType('invoiceNumber', 'string');
		$this->addType('title', 'string');
		$this->addType('issueDate', 'string');
		$this->addType('dueDate', 'string');
		$this->addType('totalAmount', 'float');
		$this->addType('paidAmount', 'float');
		$this->addType('currency', 'string');
		$this->addType('status', 'string');
		$this->addType('notes', 'string');
		$this->addType('userId', 'string');
		$this->addType('createdAt', 'string');
		$this->addType('updatedAt', 'string');
	}

	public function jsonSerialize(): array {
		$balance = max(0, ($this->totalAmount ?? 0) - ($this->paidAmount ?? 0));
		
		return [
			'id' => $this->id,
			'clientId' => $this->clientId,
			'invoiceNumber' => $this->invoiceNumber,
			'title' => $this->title,
			'issueDate' => $this->issueDate,
			'dueDate' => $this->dueDate,
			'totalAmount' => $this->totalAmount,
			'total' => $this->totalAmount, // Alias for frontend compatibility
			'paidAmount' => $this->paidAmount,
			'balance' => $balance, // Calculated field
			'currency' => $this->currency,
			'status' => $this->status,
			'notes' => $this->notes,
			'userId' => $this->userId,
			'createdAt' => $this->createdAt,
			'updatedAt' => $this->updatedAt,
		];
	}
}


