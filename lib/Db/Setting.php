<?php
declare(strict_types=1);

namespace OCA\DomainControl\Db;

use JsonSerializable;
use OCP\AppFramework\Db\Entity;

/**
 * @method string getUserId()
 * @method void setUserId(string $userId)
 * @method string getSettingKey()
 * @method void setSettingKey(string $settingKey)
 * @method string|null getSettingValue()
 * @method void setSettingValue(?string $settingValue)
 * @method string|null getCreatedAt()
 * @method void setCreatedAt(?string $createdAt)
 * @method string|null getUpdatedAt()
 * @method void setUpdatedAt(?string $updatedAt)
 */
class Setting extends Entity implements JsonSerializable
{
	protected ?string $userId = null;
	protected ?string $settingKey = null;
	protected ?string $settingValue = null;
	protected ?string $createdAt = null;
	protected ?string $updatedAt = null;

	public function __construct()
	{
		$this->addType('userId', 'string');
		$this->addType('settingKey', 'string');
		$this->addType('settingValue', 'string');
		$this->addType('createdAt', 'string');
		$this->addType('updatedAt', 'string');
	}

	public function jsonSerialize(): array
	{
		return [
			'id' => $this->id,
			'userId' => $this->userId,
			'settingKey' => $this->settingKey,
			'settingValue' => $this->settingValue,
			'createdAt' => $this->createdAt,
			'updatedAt' => $this->updatedAt,
		];
	}
}

