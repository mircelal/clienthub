<?php
declare(strict_types=1);

namespace OCA\DomainControl\Db;

use OCP\AppFramework\Db\Entity;

/**
 * @method string getName()
 * @method void setName(string $name)
 * @method string getEmail()
 * @method void setEmail(string $email)
 * @method string getPhone()
 * @method void setPhone(string $phone)
 * @method string|null getCompany()
 * @method void setCompany(string|null $company)
 * @method string|null getAddress()
 * @method void setAddress(string|null $address)
 * @method string getNotes()
 * @method void setNotes(string $notes)
 * @method string getUserId()
 * @method void setUserId(string $userId)
 * @method string|null getNcUserId()
 * @method void setNcUserId(string|null $ncUserId)
 * @method string getCreatedAt()
 * @method void setCreatedAt(string $createdAt)
 * @method string getUpdatedAt()
 * @method void setUpdatedAt(string $updatedAt)
 */
class Client extends Entity implements \JsonSerializable {
	protected $name;
	protected $email;
	protected $phone;
	protected $company = null;
	protected $address = null;
	protected $notes;
	protected $userId;
	protected $ncUserId = null;
	protected $createdAt;
	protected $updatedAt;

	public function __construct() {
		$this->addType('name', 'string');
		$this->addType('email', 'string');
		$this->addType('phone', 'string');
		$this->addType('company', 'string');
		$this->addType('address', 'string');
		$this->addType('notes', 'string');
		$this->addType('userId', 'string');
		// ncUserId is nullable, so we don't use addType for it
		// This allows NULL values to remain NULL instead of being cast to empty string
		$this->addType('createdAt', 'string');
		$this->addType('updatedAt', 'string');
	}

	public function jsonSerialize(): array {
		return [
			'id' => $this->id,
			'name' => $this->name,
			'email' => $this->email,
			'phone' => $this->phone,
			'company' => $this->company,
			'address' => $this->address,
			'notes' => $this->notes,
			'userId' => $this->userId,
			'nextcloudUserId' => $this->ncUserId, // Use full name in API response for clarity
			'createdAt' => $this->createdAt,
			'updatedAt' => $this->updatedAt,
		];
	}
}

