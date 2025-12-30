<?php
declare(strict_types=1);

namespace OCA\DomainControl\Db;

use OCP\AppFramework\Db\Entity;

/**
 * @method int getProjectId()
 * @method void setProjectId(int $projectId)
 * @method string getUserId()
 * @method void setUserId(string $userId)
 * @method string getActivityType()
 * @method void setActivityType(string $activityType)
 * @method string getDescription()
 * @method void setDescription(string $description)
 * @method string getMetadata()
 * @method void setMetadata(string $metadata)
 * @method string getCreatedAt()
 * @method void setCreatedAt(string $createdAt)
 */
class ProjectActivity extends Entity implements \JsonSerializable {
	protected $projectId;
	protected $userId;
	protected $activityType;
	protected $description;
	protected $metadata;
	protected $createdAt;

	public function __construct() {
		$this->addType('projectId', 'integer');
		$this->addType('userId', 'string');
		$this->addType('activityType', 'string');
		$this->addType('description', 'string');
		$this->addType('metadata', 'string');
		$this->addType('createdAt', 'string');
	}

	public function jsonSerialize(): array {
		return [
			'id' => $this->id,
			'projectId' => $this->projectId,
			'userId' => $this->userId,
			'activityType' => $this->activityType,
			'description' => $this->description,
			'metadata' => $this->metadata ? json_decode($this->metadata, true) : null,
			'createdAt' => $this->createdAt,
		];
	}
}

