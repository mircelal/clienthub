<?php
declare(strict_types=1);

namespace OCA\DomainControl\Controller;

use OCA\DomainControl\AppInfo\Application;
use OCA\DomainControl\Service\EmailService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;
use OCA\DomainControl\Db\DomainMapper;
use OCA\DomainControl\Db\Domain;

class DomainController extends Controller
{
	private $userId;
	private DomainMapper $mapper;
	private EmailService $emailService;

	public function __construct(
		IRequest $request,
		DomainMapper $mapper,
		EmailService $emailService,
		$userId
	) {
		parent::__construct(Application::APP_ID, $request);
		$this->mapper = $mapper;
		$this->emailService = $emailService;
		$this->userId = $userId;
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function index(): JSONResponse
	{
		try {
			$domains = $this->mapper->findAll($this->userId);
			return new JSONResponse($domains);
		} catch (\Exception $e) {
			return new JSONResponse(['error' => $e->getMessage()], 500);
		}
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function show(int $id): JSONResponse
	{
		try {
			$domain = $this->mapper->find($id, $this->userId);
			return new JSONResponse($domain);
		} catch (\Exception $e) {
			return new JSONResponse(['error' => 'Domain not found'], 404);
		}
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function byClient(int $clientId): JSONResponse
	{
		try {
			$domains = $this->mapper->findByClient($clientId, $this->userId);
			return new JSONResponse($domains);
		} catch (\Exception $e) {
			return new JSONResponse(['error' => $e->getMessage()], 500);
		}
	}

	/**
	 * @NoAdminRequired
	 */
	public function create(): JSONResponse
	{
		try {
			$body = file_get_contents('php://input');
			parse_str($body, $data);

			$domainName = $data['domainName'] ?? '';
			if (empty($domainName)) {
				return new JSONResponse(['error' => 'Domain name is required'], 400);
			}

			$domain = new Domain();
			$domain->setClientId((int) ($data['clientId'] ?? 0));
			$domain->setHostingId(isset($data['hostingId']) && $data['hostingId'] !== '' ? (int) $data['hostingId'] : null);
			$domain->setDomainName($domainName);
			$domain->setRegistrar($data['registrar'] ?? '');
			$domain->setRegistrationDate($data['registrationDate'] ?? '');
			$domain->setExpirationDate($data['expirationDate'] ?? '');
			$domain->setPrice((float) ($data['price'] ?? 0));
			$domain->setCurrency($data['currency'] ?? 'USD');
			$domain->setRenewalInterval($data['renewalInterval'] ?? '1');
			$domain->setNotes($data['notes'] ?? '');
			$domain->setPanelNotes($data['panelNotes'] ?? '');
			$domain->setRenewalHistory('[]');
			$domain->setUserId($this->userId);
			$now = date('Y-m-d H:i:s');
			$domain->setCreatedAt($now);
			$domain->setUpdatedAt($now);

			$domain = $this->mapper->insert($domain);
			return new JSONResponse($domain);
		} catch (\Exception $e) {
			return new JSONResponse(['error' => $e->getMessage()], 500);
		}
	}

	/**
	 * @NoAdminRequired
	 */
	public function update(int $id): JSONResponse
	{
		try {
			$domain = $this->mapper->find($id, $this->userId);

			$body = file_get_contents('php://input');
			parse_str($body, $data);

			if (isset($data['clientId']))
				$domain->setClientId((int) $data['clientId']);
			if (isset($data['hostingId'])) {
				$domain->setHostingId($data['hostingId'] === '' || $data['hostingId'] === null ? null : (int) $data['hostingId']);
			}
			if (isset($data['domainName']) && $data['domainName'] !== '')
				$domain->setDomainName($data['domainName']);
			if (isset($data['registrar']))
				$domain->setRegistrar($data['registrar']);
			if (isset($data['registrationDate']))
				$domain->setRegistrationDate($data['registrationDate']);
			if (isset($data['expirationDate']))
				$domain->setExpirationDate($data['expirationDate']);
			if (isset($data['price']))
				$domain->setPrice((float) $data['price']);
			if (isset($data['currency']))
				$domain->setCurrency($data['currency']);
			if (isset($data['renewalInterval']))
				$domain->setRenewalInterval($data['renewalInterval']);
			if (isset($data['notes']))
				$domain->setNotes($data['notes']);
			if (isset($data['panelNotes']))
				$domain->setPanelNotes($data['panelNotes']);
			if (isset($data['renewalHistory']))
				$domain->setRenewalHistory($data['renewalHistory']);

			$now = date('Y-m-d H:i:s');
			$domain->setUpdatedAt($now);

			$domain = $this->mapper->update($domain);
			return new JSONResponse($domain);
		} catch (\Exception $e) {
			return new JSONResponse(['error' => $e->getMessage()], 500);
		}
	}

	/**
	 * @NoAdminRequired
	 */
	public function delete(int $id): JSONResponse
	{
		try {
			$domain = $this->mapper->find($id, $this->userId);
			$this->mapper->delete($domain);
			return new JSONResponse(['success' => true]);
		} catch (\Exception $e) {
			return new JSONResponse(['error' => $e->getMessage()], 500);
		}
	}

	/**
	 * Send expiration reminder emails for domains expiring soon
	 * @NoAdminRequired
	 */
	public function sendExpirationReminders(): JSONResponse
	{
		try {
			$days = (int) ($this->request->getParam('days') ?? 30);
			$result = $this->emailService->sendDomainExpirationReminders($this->userId, $days);
			$sentCount = count($result['sent']);
			$stats = $result['stats'];

			return new JSONResponse([
				'success' => true,
				'sent_count' => $sentCount,
				'emails' => $result['sent'],
				'stats' => $stats,
				'message' => $sentCount > 0
					? $sentCount . ' e-posta gönderildi'
					: 'E-posta gönderilecek domain bulunamadı. (' . $stats['total_found'] . ' domain incelendi)'
			]);
		} catch (\Exception $e) {
			return new JSONResponse([
				'error' => $e->getMessage(),
				'trace' => $e->getTraceAsString()
			], 500);
		}
	}
}
