<?php
declare(strict_types=1);

namespace OCA\DomainControl\Db;

use OCP\AppFramework\Db\Entity;

/**
 * @method int getClientId()
 * @method void setClientId(int $clientId)
 * @method int getServiceTypeId()
 * @method void setServiceTypeId(int $serviceTypeId)
 * @method string getName()
 * @method void setName(string $name)
 * @method float getPrice()
 * @method void setPrice(float $price)
 * @method string getCurrency()
 * @method void setCurrency(string $currency)
 * @method string getStartDate()
 * @method void setStartDate(string $startDate)
 * @method string getExpirationDate()
 * @method void setExpirationDate(string $expirationDate)
 * @method string getRenewalInterval()
 * @method void setRenewalInterval(string $renewalInterval)
 * @method string getStatus()
 * @method void setStatus(string $status)
 * @method string getNotes()
 * @method void setNotes(string $notes)
 * @method string getUserId()
 * @method void setUserId(string $userId)
 * @method string getCreatedAt()
 * @method void setCreatedAt(string $createdAt)
 * @method string getUpdatedAt()
 * @method void setUpdatedAt(string $updatedAt)
 */
class Service extends Entity implements \JsonSerializable {
	protected $clientId;
	protected $serviceTypeId;
	protected $name;
	protected $price;
	protected $currency;
	protected $startDate;
	protected $expirationDate;
	protected $renewalInterval;
	protected $status;
	protected $notes;
	protected $userId;
	protected $createdAt;
	protected $updatedAt;

	public function __construct() {
		$this->addType('clientId', 'integer');
		$this->addType('serviceTypeId', 'integer');
		$this->addType('name', 'string');
		$this->addType('price', 'float');
		$this->addType('currency', 'string');
		$this->addType('startDate', 'string');
		$this->addType('expirationDate', 'string');
		$this->addType('renewalInterval', 'string');
		$this->addType('status', 'string');
		$this->addType('notes', 'string');
		$this->addType('userId', 'string');
		$this->addType('createdAt', 'string');
		$this->addType('updatedAt', 'string');
	}

	public function jsonSerialize(): array {
		return [
			'id' => $this->id,
			'clientId' => $this->clientId,
			'serviceTypeId' => $this->serviceTypeId,
			'name' => $this->name,
			'price' => $this->price,
			'currency' => $this->currency,
			'startDate' => $this->startDate,
			'expirationDate' => $this->expirationDate,
			'renewalInterval' => $this->renewalInterval,
			'status' => $this->status,
			'notes' => $this->notes,
			'userId' => $this->userId,
			'createdAt' => $this->createdAt,
			'updatedAt' => $this->updatedAt,
		];
	}
}


