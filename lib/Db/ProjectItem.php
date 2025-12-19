<?php
declare(strict_types=1);

namespace OCA\DomainControl\Db;

use OCP\AppFramework\Db\Entity;

/**
 * @method int getProjectId()
 * @method void setProjectId(int $projectId)
 * @method string getItemType()
 * @method void setItemType(string $itemType)
 * @method int getItemId()
 * @method void setItemId(int $itemId)
 * @method string getCreatedAt()
 * @method void setCreatedAt(string $createdAt)
 */
class ProjectItem extends Entity implements \JsonSerializable {
	protected $projectId;
	protected $itemType;
	protected $itemId;
	protected $createdAt;

	public function __construct() {
		$this->addType('projectId', 'integer');
		$this->addType('itemType', 'string');
		$this->addType('itemId', 'integer');
		$this->addType('createdAt', 'string');
	}

	public function jsonSerialize(): array {
		return [
			'id' => $this->id,
			'projectId' => $this->projectId,
			'itemType' => $this->itemType,
			'itemId' => $this->itemId,
			'createdAt' => $this->createdAt,
		];
	}
}


