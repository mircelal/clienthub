<?php
declare(strict_types=1);

namespace OCA\DomainControl\Db;

use OCP\AppFramework\Db\Entity;

/**
 * @method string getName()
 * @method void setName(string $name)
 * @method string getDescription()
 * @method void setDescription(string $description)
 * @method float getDefaultPrice()
 * @method void setDefaultPrice(float $defaultPrice)
 * @method string getDefaultCurrency()
 * @method void setDefaultCurrency(string $defaultCurrency)
 * @method string getRenewalInterval()
 * @method void setRenewalInterval(string $renewalInterval)
 * @method int getIsPredefined()
 * @method void setIsPredefined(int $isPredefined)
 * @method string getUserId()
 * @method void setUserId(string $userId)
 * @method string getCreatedAt()
 * @method void setCreatedAt(string $createdAt)
 * @method string getUpdatedAt()
 * @method void setUpdatedAt(string $updatedAt)
 */
class ServiceType extends Entity implements \JsonSerializable {
	protected $name;
	protected $description;
	protected $defaultPrice;
	protected $defaultCurrency;
	protected $renewalInterval;
	protected $isPredefined;
	protected $userId;
	protected $createdAt;
	protected $updatedAt;

	public function __construct() {
		$this->addType('name', 'string');
		$this->addType('description', 'string');
		$this->addType('defaultPrice', 'float');
		$this->addType('defaultCurrency', 'string');
		$this->addType('renewalInterval', 'string');
		$this->addType('isPredefined', 'integer');
		$this->addType('userId', 'string');
		$this->addType('createdAt', 'string');
		$this->addType('updatedAt', 'string');
	}

	public function jsonSerialize(): array {
		return [
			'id' => $this->id,
			'name' => $this->name,
			'description' => $this->description,
			'defaultPrice' => $this->defaultPrice,
			'defaultCurrency' => $this->defaultCurrency,
			'renewalInterval' => $this->renewalInterval,
			'isPredefined' => $this->isPredefined,
			'userId' => $this->userId,
			'createdAt' => $this->createdAt,
			'updatedAt' => $this->updatedAt,
		];
	}
}

