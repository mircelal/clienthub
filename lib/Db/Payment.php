<?php
declare(strict_types=1);

namespace OCA\DomainControl\Db;

use OCP\AppFramework\Db\Entity;

/**
 * @method int getInvoiceId()
 * @method void setInvoiceId(int $invoiceId)
 * @method int getClientId()
 * @method void setClientId(int $clientId)
 * @method float getAmount()
 * @method void setAmount(float $amount)
 * @method string getCurrency()
 * @method void setCurrency(string $currency)
 * @method string getPaymentDate()
 * @method void setPaymentDate(string $paymentDate)
 * @method string getPaymentMethod()
 * @method void setPaymentMethod(string $paymentMethod)
 * @method string getReference()
 * @method void setReference(string $reference)
 * @method string getNotes()
 * @method void setNotes(string $notes)
 * @method string getUserId()
 * @method void setUserId(string $userId)
 * @method string getCreatedAt()
 * @method void setCreatedAt(string $createdAt)
 * @method string getUpdatedAt()
 * @method void setUpdatedAt(string $updatedAt)
 */
class Payment extends Entity implements \JsonSerializable {
	protected $invoiceId;
	protected $clientId;
	protected $amount;
	protected $currency;
	protected $paymentDate;
	protected $paymentMethod;
	protected $reference;
	protected $notes;
	protected $userId;
	protected $createdAt;
	protected $updatedAt;

	public function __construct() {
		$this->addType('invoiceId', 'integer');
		$this->addType('clientId', 'integer');
		$this->addType('amount', 'float');
		$this->addType('currency', 'string');
		$this->addType('paymentDate', 'string');
		$this->addType('paymentMethod', 'string');
		$this->addType('reference', 'string');
		$this->addType('notes', 'string');
		$this->addType('userId', 'string');
		$this->addType('createdAt', 'string');
		$this->addType('updatedAt', 'string');
	}

	public function jsonSerialize(): array {
		return [
			'id' => $this->id,
			'invoiceId' => $this->invoiceId,
			'clientId' => $this->clientId,
			'amount' => $this->amount,
			'currency' => $this->currency,
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


