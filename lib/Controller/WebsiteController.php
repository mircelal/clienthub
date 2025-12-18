<?php
declare(strict_types=1);

namespace OCA\DomainControl\Controller;

use OCA\DomainControl\AppInfo\Application;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\DataResponse;
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
	public function index(): DataResponse {
		$websites = $this->mapper->findAll($this->userId);
		return new DataResponse($websites);
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function show(int $id): DataResponse {
		try {
			$website = $this->mapper->find($id, $this->userId);
			return new DataResponse($website);
		} catch (\Exception $e) {
			return new DataResponse(['error' => 'Website not found'], 404);
		}
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function byClient(int $clientId): DataResponse {
		$websites = $this->mapper->findByClient($clientId, $this->userId);
		return new DataResponse($websites);
	}

	/**
	 * @NoAdminRequired
	 */
	public function create(): DataResponse {
		$json = file_get_contents('php://input');
		$data = json_decode($json, true) ?? [];
		
		$website = new Website();
		$website->setClientId((int)($data['clientId'] ?? 0));
		$website->setDomainId((int)($data['domainId'] ?? 0));
		$website->setHostingId((int)($data['hostingId'] ?? 0));
		$website->setSoftware($data['software'] ?? '');
		$website->setInstallationDate($data['installationDate'] ?? '');
		$website->setNotes($data['notes'] ?? '');
		$website->setUserId($this->userId);
		
		$website = $this->mapper->insert($website);
		return new DataResponse($website);
	}

	/**
	 * @NoAdminRequired
	 */
	public function update(int $id): DataResponse {
		try {
			$website = $this->mapper->find($id, $this->userId);
			
			$json = file_get_contents('php://input');
			$data = json_decode($json, true) ?? [];
			
			if (isset($data['clientId'])) $website->setClientId((int)$data['clientId']);
			if (isset($data['domainId'])) $website->setDomainId((int)$data['domainId']);
			if (isset($data['hostingId'])) $website->setHostingId((int)$data['hostingId']);
			if (isset($data['software'])) $website->setSoftware($data['software']);
			if (isset($data['installationDate'])) $website->setInstallationDate($data['installationDate']);
			if (isset($data['notes'])) $website->setNotes($data['notes']);
			
			$website = $this->mapper->update($website);
			return new DataResponse($website);
		} catch (\Exception $e) {
			return new DataResponse(['error' => 'Website not found'], 404);
		}
	}

	/**
	 * @NoAdminRequired
	 */
	public function delete(int $id): DataResponse {
		try {
			$website = $this->mapper->find($id, $this->userId);
			$this->mapper->delete($website);
			return new DataResponse(['success' => true]);
		} catch (\Exception $e) {
			return new DataResponse(['error' => 'Website not found'], 404);
		}
	}
}

