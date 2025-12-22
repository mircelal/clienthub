<?php
declare(strict_types=1);

namespace OCA\DomainControl\Db;

use OCP\IDBConnection;
use OCP\AppFramework\Db\QBMapper;

class SettingMapper extends QBMapper
{
	public function __construct(IDBConnection $db)
	{
		parent::__construct($db, 'domaincontrol_settings', Setting::class);
	}

	/**
	 * Get all settings for a user
	 * @return Setting[]
	 */
	public function findAll(string $userId): array
	{
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->where($qb->expr()->eq('user_id', $qb->createNamedParameter($userId)));

		return $this->findEntities($qb);
	}

	/**
	 * Get a specific setting by key
	 */
	public function findByKey(string $userId, string $key): ?Setting
	{
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->where($qb->expr()->eq('user_id', $qb->createNamedParameter($userId)))
			->andWhere($qb->expr()->eq('setting_key', $qb->createNamedParameter($key)))
			->setMaxResults(1);

		return $this->findEntity($qb);
	}

	/**
	 * Get setting value by key (returns the value directly)
	 */
	public function getValue(string $userId, string $key, ?string $default = null): ?string
	{
		$setting = $this->findByKey($userId, $key);
		return $setting ? $setting->getSettingValue() : $default;
	}

	/**
	 * Set a setting value
	 */
	public function setValue(string $userId, string $key, ?string $value): Setting
	{
		$setting = $this->findByKey($userId, $key);
		
		if ($setting) {
			$setting->setSettingValue($value);
			$setting->setUpdatedAt(date('Y-m-d H:i:s'));
			return $this->update($setting);
		} else {
			$setting = new Setting();
			$setting->setUserId($userId);
			$setting->setSettingKey($key);
			$setting->setSettingValue($value);
			$now = date('Y-m-d H:i:s');
			$setting->setCreatedAt($now);
			$setting->setUpdatedAt($now);
			return $this->insert($setting);
		}
	}

	/**
	 * Delete a setting
	 */
	public function deleteByKey(string $userId, string $key): void
	{
		$setting = $this->findByKey($userId, $key);
		if ($setting) {
			$this->delete($setting);
		}
	}
}

