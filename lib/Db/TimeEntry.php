<?php
declare(strict_types=1);

namespace OCA\DomainControl\Db;

use OCP\AppFramework\Db\Entity;

/**
 * @method int getProjectId()
 * @method void setProjectId(int $projectId)
 * @method int|null getTaskId()
 * @method void setTaskId(?int $taskId)
 * @method string getDescription()
 * @method void setDescription(string $description)
 * @method string getStartTime()
 * @method void setStartTime(string $startTime)
 * @method string|null getEndTime()
 * @method void setEndTime(?string $endTime)
 * @method int getDuration()
 * @method void setDuration(int $duration)
 * @method bool getIsRunning()
 * @method void setIsRunning(bool $isRunning)
 * @method string getUserId()
 * @method void setUserId(string $userId)
 * @method string getCreatedAt()
 * @method void setCreatedAt(string $createdAt)
 * @method string getUpdatedAt()
 * @method void setUpdatedAt(string $updatedAt)
 */
class TimeEntry extends Entity implements \JsonSerializable {
	protected $projectId;
	protected $taskId;
	protected $description;
	protected $startTime;
	protected $endTime;
	protected $duration;
	protected $isRunning;
	protected $userId;
	protected $createdAt;
	protected $updatedAt;

	public function __construct() {
		$this->addType('projectId', 'integer');
		$this->addType('taskId', 'integer');
		$this->addType('description', 'string');
		$this->addType('startTime', 'string');
		$this->addType('endTime', 'string');
		$this->addType('duration', 'integer');
		$this->addType('isRunning', 'boolean');
		$this->addType('userId', 'string');
		$this->addType('createdAt', 'string');
		$this->addType('updatedAt', 'string');
		
		// Set default values
		$this->setDuration(0);
		$this->setIsRunning(false);
	}

	public function jsonSerialize(): array {
		return [
			'id' => $this->id,
			'projectId' => $this->projectId,
			'taskId' => $this->taskId,
			'description' => $this->description,
			'startTime' => $this->startTime,
			'endTime' => $this->endTime,
			'duration' => $this->duration,
			'isRunning' => $this->isRunning,
			'userId' => $this->userId,
			'createdAt' => $this->createdAt,
			'updatedAt' => $this->updatedAt,
		];
	}
}

