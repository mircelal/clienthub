<?php
declare(strict_types=1);

namespace OCA\DomainControl\Db;

use OCP\AppFramework\Db\Entity;

/**
 * @method int|null getProjectId()
 * @method void setProjectId(?int $projectId)
 * @method int|null getClientId()
 * @method void setClientId(?int $clientId)
 * @method int|null getParentId()
 * @method void setParentId(?int $parentId)
 * @method string getTitle()
 * @method void setTitle(string $title)
 * @method string|null getDescription()
 * @method void setDescription(?string $description)
 * @method string|null getNotes()
 * @method void setNotes(?string $notes)
 * @method string getStatus()
 * @method void setStatus(string $status)
 * @method string getPriority()
 * @method void setPriority(string $priority)
 * @method string|null getDueDate()
 * @method void setDueDate(?string $dueDate)
 * @method string|null getCompletedAt()
 * @method void setCompletedAt(?string $completedAt)
 * @method string|null getCancelledAt()
 * @method void setCancelledAt(?string $cancelledAt)
 * @method string getUserId()
 * @method void setUserId(string $userId)
 * @method string getCreatedAt()
 * @method void setCreatedAt(string $createdAt)
 * @method string getUpdatedAt()
 * @method void setUpdatedAt(string $updatedAt)
 */
class Task extends Entity implements \JsonSerializable
{
	protected $projectId;
	protected $clientId;
	protected $parentId;
	protected $title;
	protected $description;
	protected $notes;
	protected $status;
	protected $priority;
	protected $dueDate;
	protected $completedAt;
	protected $cancelledAt;
	protected $userId;
	protected $createdAt;
	protected $updatedAt;

	public function __construct()
	{
		$this->addType('projectId', 'integer');
		$this->addType('clientId', 'integer');
		$this->addType('parentId', 'integer');
		$this->addType('title', 'string');
		$this->addType('description', 'string');
		$this->addType('notes', 'string');
		$this->addType('status', 'string');
		$this->addType('priority', 'string');
		$this->addType('dueDate', 'string');
		$this->addType('completedAt', 'string');
		$this->addType('cancelledAt', 'string');
		$this->addType('userId', 'string');
		$this->addType('createdAt', 'string');
		$this->addType('updatedAt', 'string');
	}

	public function jsonSerialize(): array
	{
		return [
			'id' => $this->id,
			'projectId' => $this->projectId,
			'clientId' => $this->clientId,
			'parentId' => $this->parentId,
			'title' => $this->title,
			'description' => $this->description,
			'notes' => $this->notes,
			'status' => $this->status,
			'priority' => $this->priority,
			'dueDate' => $this->dueDate,
			'completedAt' => $this->completedAt,
			'cancelledAt' => $this->cancelledAt,
			'userId' => $this->userId,
			'createdAt' => $this->createdAt,
			'updatedAt' => $this->updatedAt,
		];
	}
}


