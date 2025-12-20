<?php
declare(strict_types=1);

namespace OCA\DomainControl\Db;

use OCP\AppFramework\Db\Entity;

/**
 * @method int getDebtId()
 * @method void setDebtId(int $debtId)
 * @method float getAmount()
 * @method void setAmount(float $amount)
 * @method string|null getPaymentDate()
 * @method void setPaymentDate(?string $paymentDate)
 * @method string|null getPaymentMethod()
 * @method void setPaymentMethod(?string $paymentMethod)
 * @method string|null getReference()
 * @method void setReference(?string $reference)
 * @method string|null getNotes()
 * @method void setNotes(?string $notes)
 * @method string getUserId()
 * @method void setUserId(string $userId)
 * @method string getCreatedAt()
 * @method void setCreatedAt(string $createdAt)
 * @method string getUpdatedAt()
 * @method void setUpdatedAt(string $updatedAt)
 */
class DebtPayment extends Entity implements \JsonSerializable
{
	protected $debtId;
	protected $amount;
	protected $paymentDate;
	protected $paymentMethod;
	protected $reference;
	protected $notes;
	protected $userId;
	protected $createdAt;
	protected $updatedAt;

	public function __construct()
	{
		$this->addType('debtId', 'integer');
		$this->addType('amount', 'float');
		$this->addType('paymentDate', 'string');
		$this->addType('paymentMethod', 'string');
		$this->addType('reference', 'string');
		$this->addType('notes', 'string');
		$this->addType('userId', 'string');
		$this->addType('createdAt', 'string');
		$this->addType('updatedAt', 'string');
	}

	public function jsonSerialize(): array
	{
		return [
			'id' => $this->id,
			'debtId' => $this->debtId,
			'amount' => $this->amount,
			'paymentDate' => $this->paymentDate,
			'paymentMethod' => $this->paymentMethod,
			'reference' => $this->reference,
			'notes' => $this->notes,
			'userId' => $this->userId,
			'createdAt' => $this->createdAt,
			'updatedAt' => $this->updatedAt,
		];
	}
}

