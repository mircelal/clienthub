<?php
declare(strict_types=1);

namespace OCA\DomainControl\Db;

use OCP\AppFramework\Db\Entity;

/**
 * @method int getClientId()
 * @method void setClientId(int $clientId)
 * @method string getDomainName()
 * @method void setDomainName(string $domainName)
 * @method string getRegistrar()
 * @method void setRegistrar(string $registrar)
 * @method string getRegistrationDate()
 * @method void setRegistrationDate(string $registrationDate)
 * @method string getExpirationDate()
 * @method void setExpirationDate(string $expirationDate)
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
 * @method string getPanelNotes()
 * @method void setPanelNotes(string $panelNotes)
 * @method string getRenewalHistory()
 * @method void setRenewalHistory(string $renewalHistory)
 * @method string getUserId()
 * @method void setUserId(string $userId)
 * @method string getCreatedAt()
 * @method void setCreatedAt(string $createdAt)
 * @method string getUpdatedAt()
 * @method void setUpdatedAt(string $updatedAt)
 */
class Domain extends Entity implements \JsonSerializable {
	protected $clientId;
	protected $domainName;
	protected $registrar;
	protected $registrationDate;
	protected $expirationDate;
	protected $price;
	protected $currency;
	protected $renewalInterval;
	protected $renewalReminder;
	protected $reminderDays;
	protected $notes;
	protected $panelNotes;
	protected $renewalHistory;
	protected $userId;
	protected $createdAt;
	protected $updatedAt;

	public function __construct() {
		$this->addType('clientId', 'integer');
		$this->addType('domainName', 'string');
		$this->addType('registrar', 'string');
		$this->addType('registrationDate', 'string');
		$this->addType('expirationDate', 'string');
		$this->addType('price', 'float');
		$this->addType('currency', 'string');
		$this->addType('renewalInterval', 'string');
		$this->addType('renewalReminder', 'boolean');
		$this->addType('reminderDays', 'integer');
		$this->addType('notes', 'string');
		$this->addType('panelNotes', 'string');
		$this->addType('renewalHistory', 'string');
		$this->addType('userId', 'string');
		$this->addType('createdAt', 'string');
		$this->addType('updatedAt', 'string');
	}

	public function jsonSerialize(): array {
		return [
			'id' => $this->id,
			'clientId' => $this->clientId,
			'domainName' => $this->domainName,
			'registrar' => $this->registrar,
			'registrationDate' => $this->registrationDate,
			'expirationDate' => $this->expirationDate,
			'price' => $this->price,
			'currency' => $this->currency,
			'renewalInterval' => $this->renewalInterval,
			'renewalReminder' => $this->renewalReminder,
			'reminderDays' => $this->reminderDays,
			'notes' => $this->notes,
			'panelNotes' => $this->panelNotes,
			'renewalHistory' => $this->renewalHistory,
			'userId' => $this->userId,
			'createdAt' => $this->createdAt,
			'updatedAt' => $this->updatedAt,
		];
	}
}
