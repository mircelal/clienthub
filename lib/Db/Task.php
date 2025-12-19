<?php
declare(strict_types=1);

namespace OCA\DomainControl\Db;

use OCP\AppFramework\Db\Entity;

/**
 * @method int getProjectId()
 * @method void setProjectId(int $projectId)
 * @method int getClientId()
 * @method void setClientId(int $clientId)
 * @method string getTitle()
 * @method void setTitle(string $title)
 * @method string getDescription()
 * @method void setDescription(string $description)
 * @method string getStatus()
 * @method void setStatus(string $status)
 * @method string getPriority()
 * @method void setPriority(string $priority)
 * @method string getDueDate()
 * @method void setDueDate(string $dueDate)
 * @method string getCompletedAt()
 * @method void setCompletedAt(string $completedAt)
 * @method string getUserId()
 * @method void setUserId(string $userId)
 * @method string getCreatedAt()
 * @method void setCreatedAt(string $createdAt)
 * @method string getUpdatedAt()
 * @method void setUpdatedAt(string $updatedAt)
 */
class Task extends Entity implements \JsonSerializable {
	protected $projectId;
	protected $clientId;
	protected $title;
	protected $description;
	protected $status;
	protected $priority;
	protected $dueDate;
	protected $completedAt;
	protected $userId;
	protected $createdAt;
	protected $updatedAt;

	public function __construct() {
		$this->addType('projectId', 'integer');
		$this->addType('clientId', 'integer');
		$this->addType('title', 'string');
		$this->addType('description', 'string');
		$this->addType('status', 'string');
		$this->addType('priority', 'string');
		$this->addType('dueDate', 'string');
		$this->addType('completedAt', 'string');
		$this->addType('userId', 'string');
		$this->addType('createdAt', 'string');
		$this->addType('updatedAt', 'string');
	}

	public function jsonSerialize(): array {
		return [
			'id' => $this->id,
			'projectId' => $this->projectId,
			'clientId' => $this->clientId,
			'title' => $this->title,
			'description' => $this->description,
			'status' => $this->status,
			'priority' => $this->priority,
			'dueDate' => $this->dueDate,
			'completedAt' => $this->completedAt,
			'userId' => $this->userId,
			'createdAt' => $this->createdAt,
			'updatedAt' => $this->updatedAt,
		];
	}
}


