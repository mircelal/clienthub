<?php
declare(strict_types=1);

namespace OCA\DomainControl\Db;

use OCP\AppFramework\Db\Entity;

/**
 * @method int getClientId()
 * @method void setClientId(int $clientId)
 * @method string getProvider()
 * @method void setProvider(string $provider)
 * @method string getPlan()
 * @method void setPlan(string $plan)
 * @method string getServerIp()
 * @method void setServerIp(string $serverIp)
 * @method string getInstallationDate()
 * @method void setInstallationDate(string $installationDate)
 * @method float getPrice()
 * @method void setPrice(float $price)
 * @method string getRenewalInterval()
 * @method void setRenewalInterval(string $renewalInterval)
 * @method bool getRenewalReminder()
 * @method void setRenewalReminder(bool $renewalReminder)
 * @method int getReminderDays()
 * @method void setReminderDays(int $reminderDays)
 * @method string getUserId()
 * @method void setUserId(string $userId)
 * @method \DateTime getCreatedAt()
 * @method void setCreatedAt(\DateTime $createdAt)
 * @method \DateTime getUpdatedAt()
 * @method void setUpdatedAt(\DateTime $updatedAt)
 */
class Hosting extends Entity {
	protected $clientId;
	protected $provider;
	protected $plan;
	protected $serverIp;
	protected $installationDate;
	protected $price;
	protected $renewalInterval;
	protected $renewalReminder;
	protected $reminderDays;
	protected $userId;
	protected $createdAt;
	protected $updatedAt;

	public function __construct() {
		$this->addType('clientId', 'integer');
		$this->addType('provider', 'string');
		$this->addType('plan', 'string');
		$this->addType('serverIp', 'string');
		$this->addType('installationDate', 'string');
		$this->addType('price', 'float');
		$this->addType('renewalInterval', 'string');
		$this->addType('renewalReminder', 'boolean');
		$this->addType('reminderDays', 'integer');
		$this->addType('userId', 'string');
		$this->addType('createdAt', 'datetime');
		$this->addType('updatedAt', 'datetime');
	}

	public function jsonSerialize(): array {
		return [
			'id' => $this->id,
			'clientId' => $this->clientId,
			'provider' => $this->provider,
			'plan' => $this->plan,
			'serverIp' => $this->serverIp,
			'installationDate' => $this->installationDate,
			'price' => $this->price,
			'renewalInterval' => $this->renewalInterval,
			'renewalReminder' => $this->renewalReminder,
			'reminderDays' => $this->reminderDays,
			'userId' => $this->userId,
			'createdAt' => $this->createdAt,
			'updatedAt' => $this->updatedAt,
		];
	}
}

