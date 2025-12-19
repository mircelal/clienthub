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
 * @method string getServerType()
 * @method void setServerType(string $serverType)
 * @method string getServerIp()
 * @method void setServerIp(string $serverIp)
 * @method string getInstallationDate()
 * @method void setInstallationDate(string $installationDate)
 * @method string getStartDate()
 * @method void setStartDate(string $startDate)
 * @method string getExpirationDate()
 * @method void setExpirationDate(string $expirationDate)
 * @method string getLastPaymentDate()
 * @method void setLastPaymentDate(string $lastPaymentDate)
 * @method float getPrice()
 * @method void setPrice(float $price)
 * @method string getCurrency()
 * @method void setCurrency(string $currency)
 * @method string getRenewalInterval()
 * @method void setRenewalInterval(string $renewalInterval)
 * @method bool getRenewalReminder()
 * @method void setRenewalReminder(bool $renewalReminder)
 * @method int getReminderDays()
 * @method void setReminderDays(int $reminderDays)
 * @method string getNotes()
 * @method void setNotes(string $notes)
 * @method string getPanelUrl()
 * @method void setPanelUrl(string $panelUrl)
 * @method string getPanelNotes()
 * @method void setPanelNotes(string $panelNotes)
 * @method string getPaymentHistory()
 * @method void setPaymentHistory(string $paymentHistory)
 * @method string getUserId()
 * @method void setUserId(string $userId)
 * @method string getCreatedAt()
 * @method void setCreatedAt(string $createdAt)
 * @method string getUpdatedAt()
 * @method void setUpdatedAt(string $updatedAt)
 */
class Hosting extends Entity implements \JsonSerializable {
	protected $clientId;
	protected $provider;
	protected $plan;
	protected $serverType;
	protected $serverIp;
	protected $installationDate; // Legacy field for backward compatibility
	protected $startDate;
	protected $expirationDate;
	protected $lastPaymentDate;
	protected $price;
	protected $currency;
	protected $renewalInterval;
	protected $renewalReminder; // Legacy field
	protected $reminderDays; // Legacy field
	protected $notes;
	protected $panelUrl;
	protected $panelNotes;
	protected $paymentHistory;
	protected $userId;
	protected $createdAt;
	protected $updatedAt;

	public function __construct() {
		$this->addType('clientId', 'integer');
		$this->addType('provider', 'string');
		$this->addType('plan', 'string');
		$this->addType('serverType', 'string');
		$this->addType('serverIp', 'string');
		$this->addType('installationDate', 'string');
		$this->addType('startDate', 'string');
		$this->addType('expirationDate', 'string');
		$this->addType('lastPaymentDate', 'string');
		$this->addType('price', 'float');
		$this->addType('currency', 'string');
		$this->addType('renewalInterval', 'string');
		$this->addType('renewalReminder', 'boolean');
		$this->addType('reminderDays', 'integer');
		$this->addType('notes', 'string');
		$this->addType('panelUrl', 'string');
		$this->addType('panelNotes', 'string');
		$this->addType('paymentHistory', 'string');
		$this->addType('userId', 'string');
		$this->addType('createdAt', 'string');
		$this->addType('updatedAt', 'string');
	}

	public function jsonSerialize(): array {
		return [
			'id' => $this->id,
			'clientId' => $this->clientId,
			'provider' => $this->provider,
			'plan' => $this->plan,
			'serverType' => $this->serverType,
			'serverIp' => $this->serverIp,
			'installationDate' => $this->installationDate,
			'startDate' => $this->startDate ?? $this->installationDate, // Fallback to old field
			'expirationDate' => $this->expirationDate,
			'lastPaymentDate' => $this->lastPaymentDate,
			'price' => $this->price,
			'currency' => $this->currency,
			'renewalInterval' => $this->renewalInterval,
			'renewalReminder' => $this->renewalReminder,
			'reminderDays' => $this->reminderDays,
			'notes' => $this->notes,
			'panelUrl' => $this->panelUrl,
			'panelNotes' => $this->panelNotes,
			'paymentHistory' => $this->paymentHistory,
			'userId' => $this->userId,
			'createdAt' => $this->createdAt,
			'updatedAt' => $this->updatedAt,
		];
	}
}
