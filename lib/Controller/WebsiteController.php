<?php
declare(strict_types=1);

namespace OCA\DomainControl\Controller;

use OCA\DomainControl\AppInfo\Application;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;
use OCA\DomainControl\Db\WebsiteMapper;
use OCA\DomainControl\Db\Website;

class WebsiteController extends Controller {
	private $userId;
	private WebsiteMapper $mapper;

	public function __construct(IRequest $request,
	                            WebsiteMapper $mapper,
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
			$websites = $this->mapper->findAll($this->userId);
			return new JSONResponse($websites);
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
			$website = $this->mapper->find($id, $this->userId);
			return new JSONResponse($website);
		} catch (\Exception $e) {
			return new JSONResponse(['error' => 'Website not found'], 404);
		}
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function byClient(int $clientId): JSONResponse {
		try {
			$websites = $this->mapper->findByClient($clientId, $this->userId);
			return new JSONResponse($websites);
		} catch (\Exception $e) {
			return new JSONResponse(['error' => $e->getMessage()], 500);
		}
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function byHosting(int $hostingId): JSONResponse {
		try {
			$websites = $this->mapper->findByHosting($hostingId, $this->userId);
			return new JSONResponse($websites);
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
			
			$name = $data['name'] ?? '';
			if (empty($name)) {
				return new JSONResponse(['error' => 'Website name is required'], 400);
			}
			
			$website = new Website();
			$website->setClientId((int)($data['clientId'] ?? 0));
			$website->setDomainId((int)($data['domainId'] ?? 0));
			$website->setHostingId((int)($data['hostingId'] ?? 0));
			$website->setName($name);
			$website->setSoftware($data['software'] ?? '');
			$website->setVersion($data['version'] ?? '');
			$website->setInstallationDate($data['installationDate'] ?? '');
			$website->setStatus($data['status'] ?? 'active');
			$website->setUrl($data['url'] ?? '');
			$website->setAdminUrl($data['adminUrl'] ?? '');
			$website->setAdminNotes($data['adminNotes'] ?? '');
			$website->setNotes($data['notes'] ?? '');
			$website->setUserId($this->userId);
			$now = date('Y-m-d H:i:s');
			$website->setCreatedAt($now);
			$website->setUpdatedAt($now);
			
			$website = $this->mapper->insert($website);
			return new JSONResponse($website);
		} catch (\Exception $e) {
			return new JSONResponse(['error' => $e->getMessage()], 500);
		}
	}

	/**
	 * @NoAdminRequired
	 */
	public function update(int $id): JSONResponse {
		try {
			$website = $this->mapper->find($id, $this->userId);
			
			$body = file_get_contents('php://input');
			parse_str($body, $data);
			
			if (isset($data['clientId'])) $website->setClientId((int)$data['clientId']);
			if (isset($data['domainId'])) $website->setDomainId((int)$data['domainId']);
			if (isset($data['hostingId'])) $website->setHostingId((int)$data['hostingId']);
			if (isset($data['name']) && $data['name'] !== '') $website->setName($data['name']);
			if (isset($data['software'])) $website->setSoftware($data['software']);
			if (isset($data['version'])) $website->setVersion($data['version']);
			if (isset($data['installationDate'])) $website->setInstallationDate($data['installationDate']);
			if (isset($data['status'])) $website->setStatus($data['status']);
			if (isset($data['url'])) $website->setUrl($data['url']);
			if (isset($data['adminUrl'])) $website->setAdminUrl($data['adminUrl']);
			if (isset($data['adminNotes'])) $website->setAdminNotes($data['adminNotes']);
			if (isset($data['notes'])) $website->setNotes($data['notes']);
			
			$now = date('Y-m-d H:i:s');
			$website->setUpdatedAt($now);
			
			$website = $this->mapper->update($website);
			return new JSONResponse($website);
		} catch (\Exception $e) {
			return new JSONResponse(['error' => $e->getMessage()], 500);
		}
	}

	/**
	 * @NoAdminRequired
	 */
	public function delete(int $id): JSONResponse {
		try {
			$website = $this->mapper->find($id, $this->userId);
			$this->mapper->delete($website);
			return new JSONResponse(['success' => true]);
		} catch (\Exception $e) {
			return new JSONResponse(['error' => $e->getMessage()], 500);
		}
	}
}
