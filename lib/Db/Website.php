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
 * @method string getName()
 * @method void setName(string $name)
 * @method string getSoftware()
 * @method void setSoftware(string $software)
 * @method string getVersion()
 * @method void setVersion(string $version)
 * @method string getInstallationDate()
 * @method void setInstallationDate(string $installationDate)
 * @method string getStatus()
 * @method void setStatus(string $status)
 * @method string getUrl()
 * @method void setUrl(string $url)
 * @method string getAdminUrl()
 * @method void setAdminUrl(string $adminUrl)
 * @method string getAdminNotes()
 * @method void setAdminNotes(string $adminNotes)
 * @method string getNotes()
 * @method void setNotes(string $notes)
 * @method string getUserId()
 * @method void setUserId(string $userId)
 * @method string getCreatedAt()
 * @method void setCreatedAt(string $createdAt)
 * @method string getUpdatedAt()
 * @method void setUpdatedAt(string $updatedAt)
 */
class Website extends Entity implements \JsonSerializable {
	protected $clientId;
	protected $domainId;
	protected $hostingId;
	protected $name;
	protected $software;
	protected $version;
	protected $installationDate;
	protected $status;
	protected $url;
	protected $adminUrl;
	protected $adminNotes;
	protected $notes;
	protected $userId;
	protected $createdAt;
	protected $updatedAt;

	public function __construct() {
		$this->addType('clientId', 'integer');
		$this->addType('domainId', 'integer');
		$this->addType('hostingId', 'integer');
		$this->addType('name', 'string');
		$this->addType('software', 'string');
		$this->addType('version', 'string');
		$this->addType('installationDate', 'string');
		$this->addType('status', 'string');
		$this->addType('url', 'string');
		$this->addType('adminUrl', 'string');
		$this->addType('adminNotes', 'string');
		$this->addType('notes', 'string');
		$this->addType('userId', 'string');
		$this->addType('createdAt', 'string');
		$this->addType('updatedAt', 'string');
	}

	public function jsonSerialize(): array {
		return [
			'id' => $this->id,
			'clientId' => $this->clientId,
			'domainId' => $this->domainId,
			'hostingId' => $this->hostingId,
			'name' => $this->name,
			'software' => $this->software,
			'version' => $this->version,
			'installationDate' => $this->installationDate,
			'status' => $this->status,
			'url' => $this->url,
			'adminUrl' => $this->adminUrl,
			'adminNotes' => $this->adminNotes,
			'notes' => $this->notes,
			'userId' => $this->userId,
			'createdAt' => $this->createdAt,
			'updatedAt' => $this->updatedAt,
		];
	}
}
