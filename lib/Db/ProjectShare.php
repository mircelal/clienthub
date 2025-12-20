<?php
declare(strict_types=1);

namespace OCA\DomainControl\Db;

use OCP\AppFramework\Db\Entity;

/**
 * @method int getProjectId()
 * @method void setProjectId(int $projectId)
 * @method string getSharedWithUserId()
 * @method void setSharedWithUserId(string $sharedWithUserId)
 * @method string getPermissionLevel()
 * @method void setPermissionLevel(string $permissionLevel)
 * @method string getSharedByUserId()
 * @method void setSharedByUserId(string $sharedByUserId)
 * @method string getCreatedAt()
 * @method void setCreatedAt(string $createdAt)
 */
class ProjectShare extends Entity implements \JsonSerializable
{
	protected $projectId;
	protected $sharedWithUserId;
	protected $permissionLevel;
	protected $sharedByUserId;
	protected $createdAt;

	public function __construct()
	{
		$this->addType('projectId', 'integer');
		$this->addType('sharedWithUserId', 'string');
		$this->addType('permissionLevel', 'string');
		$this->addType('sharedByUserId', 'string');
		$this->addType('createdAt', 'string');
	}

	public function jsonSerialize(): array
	{
		return [
			'id' => $this->id,
			'projectId' => $this->projectId,
			'sharedWithUserId' => $this->sharedWithUserId,
			'permissionLevel' => $this->permissionLevel,
			'sharedByUserId' => $this->sharedByUserId,
			'createdAt' => $this->createdAt,
		];
	}
}

