<?php
declare(strict_types=1);

namespace OCA\DomainControl\Controller;

use OCA\DomainControl\AppInfo\Application;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\JSONResponse;
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
	public function index(): JSONResponse {
		try {
			$hostings = $this->mapper->findAll($this->userId);
			return new JSONResponse($hostings);
		} catch (\Exception $e) {
			return new JSONResponse(['error' => $e->getMessage()], 500);
		}
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function show(int $id): JSONResponse {
		try {
			$hosting = $this->mapper->find($id, $this->userId);
			return new JSONResponse($hosting);
		} catch (\Exception $e) {
			return new JSONResponse(['error' => 'Hosting not found'], 404);
		}
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function byClient(int $clientId): JSONResponse {
		try {
			$hostings = $this->mapper->findByClient($clientId, $this->userId);
			return new JSONResponse($hostings);
		} catch (\Exception $e) {
			return new JSONResponse(['error' => $e->getMessage()], 500);
		}
	}

	/**
	 * @NoAdminRequired
	 */
	public function create(): JSONResponse {
		try {
			$body = file_get_contents('php://input');
			parse_str($body, $data);
			
			$provider = $data['provider'] ?? '';
			if (empty($provider)) {
				return new JSONResponse(['error' => 'Provider is required'], 400);
			}
			
			$hosting = new Hosting();
			$hosting->setClientId((int)($data['clientId'] ?? 0));
			$hosting->setProvider($provider);
			$hosting->setPlan($data['plan'] ?? '');
			$hosting->setServerType($data['serverType'] ?? 'external');
			$hosting->setServerIp($data['serverIp'] ?? '');
			$hosting->setStartDate($data['startDate'] ?? '');
			$hosting->setExpirationDate($data['expirationDate'] ?? '');
			$hosting->setLastPaymentDate($data['lastPaymentDate'] ?? '');
			$hosting->setPrice((float)($data['price'] ?? 0));
			$hosting->setCurrency($data['currency'] ?? 'USD');
			$hosting->setRenewalInterval($data['renewalInterval'] ?? 'monthly');
			$hosting->setNotes($data['notes'] ?? '');
			$hosting->setPanelUrl($data['panelUrl'] ?? '');
			$hosting->setPanelNotes($data['panelNotes'] ?? '');
			$hosting->setPaymentHistory('[]');
			$hosting->setUserId($this->userId);
			$now = date('Y-m-d H:i:s');
			$hosting->setCreatedAt($now);
			$hosting->setUpdatedAt($now);
			
			$hosting = $this->mapper->insert($hosting);
			return new JSONResponse($hosting);
		} catch (\Exception $e) {
			return new JSONResponse(['error' => $e->getMessage()], 500);
		}
	}

	/**
	 * @NoAdminRequired
	 */
	public function update(int $id): JSONResponse {
		try {
			$hosting = $this->mapper->find($id, $this->userId);
			
			$body = file_get_contents('php://input');
			parse_str($body, $data);
			
			if (isset($data['clientId'])) $hosting->setClientId((int)$data['clientId']);
			if (isset($data['provider']) && $data['provider'] !== '') $hosting->setProvider($data['provider']);
			if (isset($data['plan'])) $hosting->setPlan($data['plan']);
			if (isset($data['serverType'])) $hosting->setServerType($data['serverType']);
			if (isset($data['serverIp'])) $hosting->setServerIp($data['serverIp']);
			if (isset($data['startDate'])) $hosting->setStartDate($data['startDate']);
			if (isset($data['expirationDate'])) $hosting->setExpirationDate($data['expirationDate']);
			if (isset($data['lastPaymentDate'])) $hosting->setLastPaymentDate($data['lastPaymentDate']);
			if (isset($data['price'])) $hosting->setPrice((float)$data['price']);
			if (isset($data['currency'])) $hosting->setCurrency($data['currency']);
			if (isset($data['renewalInterval'])) $hosting->setRenewalInterval($data['renewalInterval']);
			if (isset($data['notes'])) $hosting->setNotes($data['notes']);
			if (isset($data['panelUrl'])) $hosting->setPanelUrl($data['panelUrl']);
			if (isset($data['panelNotes'])) $hosting->setPanelNotes($data['panelNotes']);
			if (isset($data['paymentHistory'])) $hosting->setPaymentHistory($data['paymentHistory']);
			
			$now = date('Y-m-d H:i:s');
			$hosting->setUpdatedAt($now);
			
			$hosting = $this->mapper->update($hosting);
			return new JSONResponse($hosting);
		} catch (\Exception $e) {
			return new JSONResponse(['error' => $e->getMessage()], 500);
		}
	}

	/**
	 * @NoAdminRequired
	 */
	public function delete(int $id): JSONResponse {
		try {
			$hosting = $this->mapper->find($id, $this->userId);
			$this->mapper->delete($hosting);
			return new JSONResponse(['success' => true]);
		} catch (\Exception $e) {
			return new JSONResponse(['error' => $e->getMessage()], 500);
		}
	}
}
