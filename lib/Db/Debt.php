<?php
declare(strict_types=1);

namespace OCA\DomainControl\Db;

use OCP\AppFramework\Db\Entity;

/**
 * @method string getType()
 * @method void setType(string $type)
 * @method string getDebtType()
 * @method void setDebtType(string $debtType)
 * @method string|null getCreditorDebtorName()
 * @method void setCreditorDebtorName(?string $creditorDebtorName)
 * @method int|null getClientId()
 * @method void setClientId(?int $clientId)
 * @method float getTotalAmount()
 * @method void setTotalAmount(float $totalAmount)
 * @method float getPaidAmount()
 * @method void setPaidAmount(float $paidAmount)
 * @method string getCurrency()
 * @method void setCurrency(string $currency)
 * @method float|null getInterestRate()
 * @method void setInterestRate(?float $interestRate)
 * @method string|null getStartDate()
 * @method void setStartDate(?string $startDate)
 * @method string|null getDueDate()
 * @method void setDueDate(?string $dueDate)
 * @method string|null getNextPaymentDate()
 * @method void setNextPaymentDate(?string $nextPaymentDate)
 * @method string|null getPaymentFrequency()
 * @method void setPaymentFrequency(?string $paymentFrequency)
 * @method float|null getPaymentAmount()
 * @method void setPaymentAmount(?float $paymentAmount)
 * @method string|null getDescription()
 * @method void setDescription(?string $description)
 * @method string getStatus()
 * @method void setStatus(string $status)
 * @method string|null getNotes()
 * @method void setNotes(?string $notes)
 * @method string getUserId()
 * @method void setUserId(string $userId)
 * @method string getCreatedAt()
 * @method void setCreatedAt(string $createdAt)
 * @method string getUpdatedAt()
 * @method void setUpdatedAt(string $updatedAt)
 */
class Debt extends Entity implements \JsonSerializable
{
	protected $type;
	protected $debtType;
	protected $creditorDebtorName;
	protected $clientId;
	protected $totalAmount;
	protected $paidAmount;
	protected $currency;
	protected $interestRate;
	protected $startDate;
	protected $dueDate;
	protected $nextPaymentDate;
	protected $paymentFrequency;
	protected $paymentAmount;
	protected $description;
	protected $status;
	protected $notes;
	protected $userId;
	protected $createdAt;
	protected $updatedAt;

	public function __construct()
	{
		$this->addType('type', 'string');
		$this->addType('debtType', 'string');
		$this->addType('creditorDebtorName', 'string');
		$this->addType('clientId', 'integer');
		$this->addType('totalAmount', 'float');
		$this->addType('paidAmount', 'float');
		$this->addType('currency', 'string');
		$this->addType('interestRate', 'float');
		$this->addType('startDate', 'string');
		$this->addType('dueDate', 'string');
		$this->addType('nextPaymentDate', 'string');
		$this->addType('paymentFrequency', 'string');
		$this->addType('paymentAmount', 'float');
		$this->addType('description', 'string');
		$this->addType('status', 'string');
		$this->addType('notes', 'string');
		$this->addType('userId', 'string');
		$this->addType('createdAt', 'string');
		$this->addType('updatedAt', 'string');
	}

	/**
	 * Override setter to convert empty strings to null for date fields
	 */
	public function setStartDate(?string $startDate): void
	{
		$this->startDate = !empty($startDate) ? $startDate : null;
	}

	/**
	 * Override setter to convert empty strings to null for date fields
	 */
	public function setDueDate(?string $dueDate): void
	{
		$this->dueDate = !empty($dueDate) ? $dueDate : null;
	}

	/**
	 * Override setter to convert empty strings to null for date fields
	 */
	public function setNextPaymentDate(?string $nextPaymentDate): void
	{
		$this->nextPaymentDate = !empty($nextPaymentDate) ? $nextPaymentDate : null;
	}

	public function jsonSerialize(): array
	{
		return [
			'id' => $this->id,
			'type' => $this->type,
			'debtType' => $this->debtType,
			'creditorDebtorName' => $this->creditorDebtorName,
			'clientId' => $this->clientId,
			'totalAmount' => $this->totalAmount,
			'paidAmount' => $this->paidAmount,
			'currency' => $this->currency,
			'interestRate' => $this->interestRate,
			'startDate' => $this->startDate,
			'dueDate' => $this->dueDate,
			'nextPaymentDate' => $this->nextPaymentDate,
			'paymentFrequency' => $this->paymentFrequency,
			'paymentAmount' => $this->paymentAmount,
			'description' => $this->description,
			'status' => $this->status,
			'notes' => $this->notes,
			'userId' => $this->userId,
			'createdAt' => $this->createdAt,
			'updatedAt' => $this->updatedAt,
		];
	}
}

