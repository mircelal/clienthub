<?php
declare(strict_types=1);

namespace OCA\DomainControl\Db;

use OCP\AppFramework\Db\Entity;

/**
 * @method int getClientId()
 * @method void setClientId(int $clientId)
 * @method int getDomainId()
 * @method void setDomainId(int $domainId)
 * @method int getHostingId()
 * @method void setHostingId(int $hostingId)
 * @method string getSoftware()
 * @method void setSoftware(string $software)
 * @method string getInstallationDate()
 * @method void setInstallationDate(string $installationDate)
 * @method string getNotes()
 * @method void setNotes(string $notes)
 * @method string getUserId()
 * @method void setUserId(string $userId)
 * @method \DateTime getCreatedAt()
 * @method void setCreatedAt(\DateTime $createdAt)
 * @method \DateTime getUpdatedAt()
 * @method void setUpdatedAt(\DateTime $updatedAt)
 */
class Website extends Entity {
	protected $clientId;
	protected $domainId;
	protected $hostingId;
	protected $software;
	protected $installationDate;
	protected $notes;
	protected $userId;
	protected $createdAt;
	protected $updatedAt;

	public function __construct() {
		$this->addType('clientId', 'integer');
		$this->addType('domainId', 'integer');
		$this->addType('hostingId', 'integer');
		$this->addType('software', 'string');
		$this->addType('installationDate', 'string');
		$this->addType('notes', 'string');
		$this->addType('userId', 'string');
		$this->addType('createdAt', 'datetime');
		$this->addType('updatedAt', 'datetime');
	}

	public function jsonSerialize(): array {
		return [
			'id' => $this->id,
			'clientId' => $this->clientId,
			'domainId' => $this->domainId,
			'hostingId' => $this->hostingId,
			'software' => $this->software,
			'installationDate' => $this->installationDate,
			'notes' => $this->notes,
			'userId' => $this->userId,
			'createdAt' => $this->createdAt,
			'updatedAt' => $this->updatedAt,
		];
	}
}

