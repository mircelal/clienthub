<?php
declare(strict_types=1);

namespace OCA\DomainControl\Controller;

use OCA\DomainControl\AppInfo\Application;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;
use OCA\DomainControl\Db\ServiceMapper;
use OCA\DomainControl\Db\Service;

class ServiceController extends Controller {
	private $userId;
	private ServiceMapper $mapper;

	public function __construct(IRequest $request,
	                            ServiceMapper $mapper,
	                            $userId) {
		parent::__construct(Application::APP_ID, $request);
		$this->mapper = $mapper;
		$this->userId = $userId;
	}

	private function getRequestData(): array {
		$body = file_get_contents('php://input');
		parse_str($body, $data);
		return $data;
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function index(): JSONResponse {
		try {
			$services = $this->mapper->findAll($this->userId);
			return new JSONResponse($services);
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
			$service = $this->mapper->find($id, $this->userId);
			return new JSONResponse($service);
		} catch (\Exception $e) {
			return new JSONResponse(['error' => 'Service not found'], 404);
		}
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function byClient(int $clientId): JSONResponse {
		try {
			$services = $this->mapper->findByClient($clientId, $this->userId);
			return new JSONResponse($services);
		} catch (\Exception $e) {
			return new JSONResponse(['error' => $e->getMessage()], 500);
		}
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function expiringSoon(): JSONResponse {
		try {
			$services = $this->mapper->findExpiringSoon($this->userId, 30);
			return new JSONResponse($services);
		} catch (\Exception $e) {
			return new JSONResponse(['error' => $e->getMessage()], 500);
		}
	}

	/**
	 * @NoAdminRequired
	 */
	public function create(): JSONResponse {
		try {
			$data = $this->getRequestData();
			
			$name = $data['name'] ?? '';
			if (empty($name)) {
				return new JSONResponse(['error' => 'Name is required'], 400);
			}
			
			$service = new Service();
			$service->setClientId((int)($data['clientId'] ?? 0));
			$service->setServiceTypeId((int)($data['serviceTypeId'] ?? 0));
			$service->setName($name);
			$service->setPrice((float)($data['price'] ?? 0));
			$service->setCurrency($data['currency'] ?? 'USD');
			$service->setStartDate($data['startDate'] ?? '');
			$service->setExpirationDate($data['expirationDate'] ?? '');
			$service->setRenewalInterval($data['renewalInterval'] ?? 'monthly');
			$service->setStatus($data['status'] ?? 'active');
			$service->setNotes($data['notes'] ?? '');
			$service->setUserId($this->userId);
			$now = date('Y-m-d H:i:s');
			$service->setCreatedAt($now);
			$service->setUpdatedAt($now);
			
			$service = $this->mapper->insert($service);
			return new JSONResponse($service);
		} catch (\Exception $e) {
			return new JSONResponse(['error' => $e->getMessage()], 500);
		}
	}

	/**
	 * @NoAdminRequired
	 */
	public function update(int $id): JSONResponse {
		try {
			$service = $this->mapper->find($id, $this->userId);
			$data = $this->getRequestData();
			
			if (isset($data['clientId'])) $service->setClientId((int)$data['clientId']);
			if (isset($data['serviceTypeId'])) $service->setServiceTypeId((int)$data['serviceTypeId']);
			if (isset($data['name']) && $data['name'] !== '') $service->setName($data['name']);
			if (isset($data['price'])) $service->setPrice((float)$data['price']);
			if (isset($data['currency'])) $service->setCurrency($data['currency']);
			if (isset($data['startDate'])) $service->setStartDate($data['startDate']);
			if (isset($data['expirationDate'])) $service->setExpirationDate($data['expirationDate']);
			if (isset($data['renewalInterval'])) $service->setRenewalInterval($data['renewalInterval']);
			if (isset($data['status'])) $service->setStatus($data['status']);
			if (isset($data['notes'])) $service->setNotes($data['notes']);
			
			$service->setUpdatedAt(date('Y-m-d H:i:s'));
			
			$service = $this->mapper->update($service);
			return new JSONResponse($service);
		} catch (\Exception $e) {
			return new JSONResponse(['error' => $e->getMessage()], 500);
		}
	}

	/**
	 * @NoAdminRequired
	 */
	public function delete(int $id): JSONResponse {
		try {
			$service = $this->mapper->find($id, $this->userId);
			$this->mapper->delete($service);
			return new JSONResponse(['success' => true]);
		} catch (\Exception $e) {
			return new JSONResponse(['error' => $e->getMessage()], 500);
		}
	}

	/**
	 * @NoAdminRequired
	 */
	public function extend(int $id): JSONResponse {
		try {
			$service = $this->mapper->find($id, $this->userId);
			
			// Tek seferlik hizmetler için uzatma yapılamaz
			if ($service->getRenewalInterval() === 'one-time') {
				return new JSONResponse(['error' => 'Tek seferlik hizmetler için süre uzatma yapılamaz'], 400);
			}
			
			$data = $this->getRequestData();
			
			$months = (int)($data['months'] ?? 1);
			$currentExpiry = $service->getExpirationDate();
			
			if (empty($currentExpiry)) {
				$baseDate = date('Y-m-d');
			} else {
				$baseDate = $currentExpiry;
			}
			
			$newExpiry = date('Y-m-d', strtotime($baseDate . " +{$months} months"));
			$service->setExpirationDate($newExpiry);
			$service->setUpdatedAt(date('Y-m-d H:i:s'));
			
			$service = $this->mapper->update($service);
			return new JSONResponse($service);
		} catch (\Exception $e) {
			return new JSONResponse(['error' => $e->getMessage()], 500);
		}
	}
}


