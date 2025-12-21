<?php
declare(strict_types=1);

namespace OCA\DomainControl\Db;

use OCP\AppFramework\Db\Entity;

/**
 * @method string getName()
 * @method void setName(string $name)
 * @method string getProvider()
 * @method void setProvider(string $provider)
 * @method float getPriceMonthly()
 * @method void setPriceMonthly(float $priceMonthly)
 * @method float getPriceYearly()
 * @method void setPriceYearly(float $priceYearly)
 * @method string getCurrency()
 * @method void setCurrency(string $currency)
 * @method int getDiskSpaceGb()
 * @method void setDiskSpaceGb(int $diskSpaceGb)
 * @method int getTrafficGb()
 * @method void setTrafficGb(int $trafficGb)
 * @method bool getBandwidthUnlimited()
 * @method void setBandwidthUnlimited(bool $bandwidthUnlimited)
 * @method int getDomainsAllowed()
 * @method void setDomainsAllowed(int $domainsAllowed)
 * @method int getDatabasesAllowed()
 * @method void setDatabasesAllowed(int $databasesAllowed)
 * @method int getEmailAccounts()
 * @method void setEmailAccounts(int $emailAccounts)
 * @method int getFtpAccounts()
 * @method void setFtpAccounts(int $ftpAccounts)
 * @method bool getSslIncluded()
 * @method void setSslIncluded(bool $sslIncluded)
 * @method bool getBackupIncluded()
 * @method void setBackupIncluded(bool $backupIncluded)
 * @method string getDescription()
 * @method void setDescription(string $description)
 * @method bool getIsActive()
 * @method void setIsActive(bool $isActive)
 * @method string getUserId()
 * @method void setUserId(string $userId)
 * @method string getCreatedAt()
 * @method void setCreatedAt(string $createdAt)
 * @method string getUpdatedAt()
 * @method void setUpdatedAt(string $updatedAt)
 */
class HostingPackage extends Entity implements \JsonSerializable {
	protected $name;
	protected $provider;
	protected $priceMonthly;
	protected $priceYearly;
	protected $currency;
	protected $diskSpaceGb;
	protected $trafficGb;
	protected $bandwidthUnlimited;
	protected $domainsAllowed;
	protected $databasesAllowed;
	protected $emailAccounts;
	protected $ftpAccounts;
	protected $sslIncluded;
	protected $backupIncluded;
	protected $description;
	protected $isActive;
	protected $userId;
	protected $createdAt;
	protected $updatedAt;

	public function __construct() {
		$this->addType('name', 'string');
		$this->addType('provider', 'string');
		$this->addType('priceMonthly', 'float');
		$this->addType('priceYearly', 'float');
		$this->addType('currency', 'string');
		$this->addType('diskSpaceGb', 'integer');
		$this->addType('trafficGb', 'integer');
		$this->addType('bandwidthUnlimited', 'boolean');
		$this->addType('domainsAllowed', 'integer');
		$this->addType('databasesAllowed', 'integer');
		$this->addType('emailAccounts', 'integer');
		$this->addType('ftpAccounts', 'integer');
		$this->addType('sslIncluded', 'boolean');
		$this->addType('backupIncluded', 'boolean');
		$this->addType('description', 'string');
		$this->addType('isActive', 'boolean');
		$this->addType('userId', 'string');
		$this->addType('createdAt', 'string');
		$this->addType('updatedAt', 'string');
	}

	public function jsonSerialize(): array {
		return [
			'id' => $this->id,
			'name' => $this->name,
			'provider' => $this->provider,
			'priceMonthly' => $this->priceMonthly,
			'priceYearly' => $this->priceYearly,
			'currency' => $this->currency,
			'diskSpaceGb' => $this->diskSpaceGb,
			'trafficGb' => $this->trafficGb,
			'bandwidthUnlimited' => $this->bandwidthUnlimited,
			'domainsAllowed' => $this->domainsAllowed,
			'databasesAllowed' => $this->databasesAllowed,
			'emailAccounts' => $this->emailAccounts,
			'ftpAccounts' => $this->ftpAccounts,
			'sslIncluded' => $this->sslIncluded,
			'backupIncluded' => $this->backupIncluded,
			'description' => $this->description,
			'isActive' => $this->isActive,
			'userId' => $this->userId,
			'createdAt' => $this->createdAt,
			'updatedAt' => $this->updatedAt,
		];
	}
}

