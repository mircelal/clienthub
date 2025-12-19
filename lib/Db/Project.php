<?php
declare(strict_types=1);

namespace OCA\DomainControl\Db;

use OCP\AppFramework\Db\Entity;

/**
 * @method int getClientId()
 * @method void setClientId(int $clientId)
 * @method string getName()
 * @method void setName(string $name)
 * @method string getDescription()
 * @method void setDescription(string $description)
 * @method string getStatus()
 * @method void setStatus(string $status)
 * @method string getStartDate()
 * @method void setStartDate(string $startDate)
 * @method string getDeadline()
 * @method void setDeadline(string $deadline)
 * @method float getBudget()
 * @method void setBudget(float $budget)
 * @method string getCurrency()
 * @method void setCurrency(string $currency)
 * @method string getNotes()
 * @method void setNotes(string $notes)
 * @method string getUserId()
 * @method void setUserId(string $userId)
 * @method string getCreatedAt()
 * @method void setCreatedAt(string $createdAt)
 * @method string getUpdatedAt()
 * @method void setUpdatedAt(string $updatedAt)
 */
class Project extends Entity implements \JsonSerializable {
	protected $clientId;
	protected $name;
	protected $description;
	protected $status;
	protected $startDate;
	protected $deadline;
	protected $budget;
	protected $currency;
	protected $notes;
	protected $userId;
	protected $createdAt;
	protected $updatedAt;

	public function __construct() {
		$this->addType('clientId', 'integer');
		$this->addType('name', 'string');
		$this->addType('description', 'string');
		$this->addType('status', 'string');
		$this->addType('startDate', 'string');
		$this->addType('deadline', 'string');
		$this->addType('budget', 'float');
		$this->addType('currency', 'string');
		$this->addType('notes', 'string');
		$this->addType('userId', 'string');
		$this->addType('createdAt', 'string');
		$this->addType('updatedAt', 'string');
	}

	public function jsonSerialize(): array {
		return [
			'id' => $this->id,
			'clientId' => $this->clientId,
			'name' => $this->name,
			'description' => $this->description,
			'status' => $this->status,
			'startDate' => $this->startDate,
			'deadline' => $this->deadline,
			'budget' => $this->budget,
			'currency' => $this->currency,
			'notes' => $this->notes,
			'userId' => $this->userId,
			'createdAt' => $this->createdAt,
			'updatedAt' => $this->updatedAt,
		];
	}
}


