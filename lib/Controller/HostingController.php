<?php
declare(strict_types=1);

namespace OCA\DomainControl\Controller;

use OCA\DomainControl\AppInfo\Application;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;
use OCA\DomainControl\Db\HostingMapper;
use OCA\DomainControl\Db\Hosting;

class HostingController extends Controller {
	private $userId;
	private HostingMapper $mapper;

	public function __construct(IRequest $request,
	                            HostingMapper $mapper,
	                            $userId) {
		parent::__construct(Application::APP_ID, $request);
		$this->mapper = $mapper;
		$this->userId = $userId;
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function index(): DataResponse {
		$hostings = $this->mapper->findAll($this->userId);
		return new DataResponse($hostings);
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function show(int $id): DataResponse {
		try {
			$hosting = $this->mapper->find($id, $this->userId);
			return new DataResponse($hosting);
		} catch (\Exception $e) {
			return new DataResponse(['error' => 'Hosting not found'], 404);
		}
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function byClient(int $clientId): DataResponse {
		$hostings = $this->mapper->findByClient($clientId, $this->userId);
		return new DataResponse($hostings);
	}

	/**
	 * @NoAdminRequired
	 */
	public function create(): DataResponse {
		$json = file_get_contents('php://input');
		$data = json_decode($json, true) ?? [];
		
		$hosting = new Hosting();
		$hosting->setClientId((int)($data['clientId'] ?? 0));
		$hosting->setProvider($data['provider'] ?? '');
		$hosting->setPlan($data['plan'] ?? '');
		$hosting->setServerIp($data['serverIp'] ?? '');
		$hosting->setInstallationDate($data['installationDate'] ?? '');
		$hosting->setPrice((float)($data['price'] ?? 0));
		$hosting->setRenewalInterval($data['renewalInterval'] ?? 'monthly');
		$hosting->setRenewalReminder((bool)($data['renewalReminder'] ?? true));
		$hosting->setReminderDays((int)($data['reminderDays'] ?? 30));
		$hosting->setUserId($this->userId);
		
		$hosting = $this->mapper->insert($hosting);
		return new DataResponse($hosting);
	}

	/**
	 * @NoAdminRequired
	 */
	public function update(int $id): DataResponse {
		try {
			$hosting = $this->mapper->find($id, $this->userId);
			
			$json = file_get_contents('php://input');
			$data = json_decode($json, true) ?? [];
			
			if (isset($data['clientId'])) $hosting->setClientId((int)$data['clientId']);
			if (isset($data['provider'])) $hosting->setProvider($data['provider']);
			if (isset($data['plan'])) $hosting->setPlan($data['plan']);
			if (isset($data['serverIp'])) $hosting->setServerIp($data['serverIp']);
			if (isset($data['installationDate'])) $hosting->setInstallationDate($data['installationDate']);
			if (isset($data['price'])) $hosting->setPrice((float)$data['price']);
			if (isset($data['renewalInterval'])) $hosting->setRenewalInterval($data['renewalInterval']);
			if (isset($data['renewalReminder'])) $hosting->setRenewalReminder((bool)$data['renewalReminder']);
			if (isset($data['reminderDays'])) $hosting->setReminderDays((int)$data['reminderDays']);
			
			$hosting = $this->mapper->update($hosting);
			return new DataResponse($hosting);
		} catch (\Exception $e) {
			return new DataResponse(['error' => 'Hosting not found'], 404);
		}
	}

	/**
	 * @NoAdminRequired
	 */
	public function delete(int $id): DataResponse {
		try {
			$hosting = $this->mapper->find($id, $this->userId);
			$this->mapper->delete($hosting);
			return new DataResponse(['success' => true]);
		} catch (\Exception $e) {
			return new DataResponse(['error' => 'Hosting not found'], 404);
		}
	}
}

