<?php
declare(strict_types=1);

namespace OCA\DomainControl\Db;

use OCP\AppFramework\Db\Entity;

/**
 * @method string getType()
 * @method void setType(string $type)
 * @method int|null getCategoryId()
 * @method void setCategoryId(?int $categoryId)
 * @method int|null getProjectId()
 * @method void setProjectId(?int $projectId)
 * @method int|null getClientId()
 * @method void setClientId(?int $clientId)
 * @method float getAmount()
 * @method void setAmount(float $amount)
 * @method string getCurrency()
 * @method void setCurrency(string $currency)
 * @method string|null getTransactionDate()
 * @method void setTransactionDate(?string $transactionDate)
 * @method string|null getDescription()
 * @method void setDescription(?string $description)
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
class Transaction extends Entity implements \JsonSerializable
{
	protected $type;
	protected $categoryId;
	protected $projectId;
	protected $clientId;
	protected $amount;
	protected $currency;
	protected $transactionDate;
	protected $description;
	protected $paymentMethod;
	protected $reference;
	protected $notes;
	protected $userId;
	protected $createdAt;
	protected $updatedAt;

	public function __construct()
	{
		$this->addType('type', 'string');
		$this->addType('categoryId', 'integer');
		$this->addType('projectId', 'integer');
		$this->addType('clientId', 'integer');
		$this->addType('amount', 'float');
		$this->addType('currency', 'string');
		$this->addType('transactionDate', 'string');
		$this->addType('description', 'string');
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
			'type' => $this->type,
			'categoryId' => $this->categoryId,
			'projectId' => $this->projectId,
			'clientId' => $this->clientId,
			'amount' => $this->amount,
			'currency' => $this->currency,
			'transactionDate' => $this->transactionDate,
			'description' => $this->description,
			'paymentMethod' => $this->paymentMethod,
			'reference' => $this->reference,
			'notes' => $this->notes,
			'userId' => $this->userId,
			'createdAt' => $this->createdAt,
			'updatedAt' => $this->updatedAt,
		];
	}
}

