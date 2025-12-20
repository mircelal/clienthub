<?php
declare(strict_types=1);

namespace OCA\DomainControl\Db;

use OCP\AppFramework\Db\Entity;

/**
 * @method string getName()
 * @method void setName(string $name)
 * @method string getType()
 * @method void setType(string $type)
 * @method string|null getIcon()
 * @method void setIcon(?string $icon)
 * @method string|null getColor()
 * @method void setColor(?string $color)
 * @method string|null getUserId()
 * @method void setUserId(?string $userId)
 * @method bool getIsPredefined()
 * @method void setIsPredefined(bool $isPredefined)
 * @method string getCreatedAt()
 * @method void setCreatedAt(string $createdAt)
 * @method string getUpdatedAt()
 * @method void setUpdatedAt(string $updatedAt)
 */
class TransactionCategory extends Entity implements \JsonSerializable
{
	protected $name;
	protected $type;
	protected $icon;
	protected $color;
	protected $userId;
	protected $isPredefined;
	protected $createdAt;
	protected $updatedAt;

	public function __construct()
	{
		$this->addType('name', 'string');
		$this->addType('type', 'string');
		$this->addType('icon', 'string');
		$this->addType('color', 'string');
		$this->addType('userId', 'string');
		$this->addType('isPredefined', 'boolean');
		$this->addType('createdAt', 'string');
		$this->addType('updatedAt', 'string');
	}

	public function jsonSerialize(): array
	{
		return [
			'id' => $this->id,
			'name' => $this->name,
			'type' => $this->type,
			'icon' => $this->icon,
			'color' => $this->color,
			'userId' => $this->userId,
			'isPredefined' => $this->isPredefined,
			'createdAt' => $this->createdAt,
			'updatedAt' => $this->updatedAt,
		];
	}
}

