<?php
declare(strict_types=1);

namespace OCA\DomainControl\Controller;

use OCA\DomainControl\AppInfo\Application;
use OCA\DomainControl\Service\CalendarService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;
use OCA\DomainControl\Db\HostingMapper;
use OCA\DomainControl\Db\Hosting;

class HostingController extends Controller {
	private $userId;
	private HostingMapper $mapper;
	private CalendarService $calendarService;

	public function __construct(IRequest $request,
	                            HostingMapper $mapper,
	                            CalendarService $calendarService,
	                            $userId) {
		parent::__construct(Application::APP_ID, $request);
		$this->mapper = $mapper;
		$this->calendarService = $calendarService;
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
			
			$clientId = isset($data['clientId']) && $data['clientId'] !== '' ? (int) $data['clientId'] : null;
			if (empty($clientId) || $clientId <= 0) {
				return new JSONResponse(['error' => 'Client ID is required'], 400);
			}
			
			$hosting = new Hosting();
			$hosting->setClientId($clientId);
			$hosting->setPackageId(isset($data['packageId']) && $data['packageId'] !== '' ? (int)$data['packageId'] : null);
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
			
			// Add calendar event if expiration date is set
			if (!empty($hosting->getExpirationDate())) {
				$hostingName = $hosting->getProvider() . ($hosting->getPlan() ? ' - ' . $hosting->getPlan() : '');
				$eventUid = $this->calendarService->addHostingExpirationEvent(
					$this->userId,
					$hostingName,
					$hosting->getExpirationDate()
				);
				if ($eventUid) {
					$notes = $hosting->getNotes() ?: '';
					$hosting->setNotes(trim($notes) . "\n[CALENDAR_UID:" . $eventUid . "]");
					$hosting = $this->mapper->update($hosting);
				}
			}
			
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
			$oldExpirationDate = $hosting->getExpirationDate();
			$oldNotes = $hosting->getNotes() ?: '';
			
			$body = file_get_contents('php://input');
			parse_str($body, $data);
			
			if (isset($data['clientId'])) $hosting->setClientId((int)$data['clientId']);
			if (isset($data['packageId'])) $hosting->setPackageId($data['packageId'] !== '' ? (int)$data['packageId'] : null);
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
			
			// Handle calendar event for expiration date
			$existingUid = null;
			if (preg_match('/\[CALENDAR_UID:([^\]]+)\]/', $oldNotes, $matches)) {
				$existingUid = $matches[1];
			}

			if (isset($data['expirationDate'])) {
				$newExpirationDate = $data['expirationDate'];
				
				if (!empty($newExpirationDate)) {
					// Remove old event if exists
					if ($existingUid) {
						$this->calendarService->removeEvent($this->userId, $existingUid);
						$oldNotes = preg_replace('/\s*\[CALENDAR_UID:[^\]]+\]\s*/', '', $oldNotes);
					}
					
					// Create new event
					$hostingName = $hosting->getProvider() . ($hosting->getPlan() ? ' - ' . $hosting->getPlan() : '');
					$eventUid = $this->calendarService->addHostingExpirationEvent(
						$this->userId,
						$hostingName,
						$newExpirationDate
					);
					
					if ($eventUid) {
						$notes = $hosting->getNotes() ?: '';
						$notes = preg_replace('/\s*\[CALENDAR_UID:[^\]]+\]\s*/', '', $notes);
						$hosting->setNotes(trim($notes) . "\n[CALENDAR_UID:" . $eventUid . "]");
					}
				} elseif ($existingUid) {
					// Remove event if expiration date is cleared
					$this->calendarService->removeEvent($this->userId, $existingUid);
					$notes = $hosting->getNotes() ?: '';
					$hosting->setNotes(preg_replace('/\s*\[CALENDAR_UID:[^\]]+\]\s*/', '', $notes));
				}
			} elseif ((isset($data['provider']) || isset($data['plan'])) && $existingUid && $oldExpirationDate) {
				// Update event if provider/plan changed and expiration date exists
				$this->calendarService->removeEvent($this->userId, $existingUid);
				$hostingName = $hosting->getProvider() . ($hosting->getPlan() ? ' - ' . $hosting->getPlan() : '');
				$eventUid = $this->calendarService->addHostingExpirationEvent(
					$this->userId,
					$hostingName,
					$oldExpirationDate
				);
				if ($eventUid) {
					$oldNotes = preg_replace('/\s*\[CALENDAR_UID:[^\]]+\]\s*/', '', $oldNotes);
					$hosting->setNotes(trim($oldNotes) . "\n[CALENDAR_UID:" . $eventUid . "]");
				}
			}
			
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
			
			// Remove calendar event if exists
			$notes = $hosting->getNotes() ?: '';
			if (preg_match('/\[CALENDAR_UID:([^\]]+)\]/', $notes, $matches)) {
				$this->calendarService->removeEvent($this->userId, $matches[1]);
			}
			
			$this->mapper->delete($hosting);
			return new JSONResponse(['success' => true]);
		} catch (\Exception $e) {
			return new JSONResponse(['error' => $e->getMessage()], 500);
		}
	}
}
