<?php
declare(strict_types=1);

namespace OCA\DomainControl\Controller;

use OCA\DomainControl\AppInfo\Application;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;
use OCA\DomainControl\Db\ClientNoteMapper;
use OCA\DomainControl\Db\ClientNote;
use OCA\DomainControl\Db\ClientMapper;

class ClientNoteController extends Controller {
	private $userId;
	private ClientNoteMapper $mapper;
	private ClientMapper $clientMapper;

	public function __construct(IRequest $request,
	                            ClientNoteMapper $mapper,
	                            ClientMapper $clientMapper,
	                            $userId) {
		parent::__construct(Application::APP_ID, $request);
		$this->mapper = $mapper;
		$this->clientMapper = $clientMapper;
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
	public function index(int $clientId): JSONResponse {
		try {
			// Verify client exists and user has access
			$this->clientMapper->find($clientId, $this->userId);
			
			$notes = $this->mapper->findAll($clientId, $this->userId);
			
			return new JSONResponse($notes);
		} catch (\Exception $e) {
			return new JSONResponse(['error' => $e->getMessage()], 500);
		}
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function show(int $clientId, int $id): JSONResponse {
		try {
			// Verify client exists and user has access
			$this->clientMapper->find($clientId, $this->userId);
			
			$note = $this->mapper->find($id, $this->userId);
			return new JSONResponse($note);
		} catch (\Exception $e) {
			return new JSONResponse(['error' => $e->getMessage()], 500);
		}
	}

	/**
	 * @NoAdminRequired
	 */
	public function create(int $clientId): JSONResponse {
		try {
			// Verify client exists and user has access
			$this->clientMapper->find($clientId, $this->userId);
			
			$data = $this->getRequestData();
			
			$note = new ClientNote();
			$note->setClientId($clientId);
			$note->setUserId($this->userId);
			$note->setContent($data['content'] ?? '');
			$now = date('Y-m-d H:i:s');
			$note->setCreatedAt($now);
			$note->setUpdatedAt($now);
			
			$note = $this->mapper->insert($note);
			
			return new JSONResponse($note);
		} catch (\Exception $e) {
			return new JSONResponse(['error' => $e->getMessage()], 500);
		}
	}

	/**
	 * @NoAdminRequired
	 */
	public function update(int $clientId, int $id): JSONResponse {
		try {
			// Verify client exists and user has access
			$this->clientMapper->find($clientId, $this->userId);
			
			$note = $this->mapper->find($id, $this->userId);
			$data = $this->getRequestData();
			
			if (isset($data['content'])) {
				$note->setContent($data['content']);
			}
			$note->setUpdatedAt(date('Y-m-d H:i:s'));
			
			$note = $this->mapper->update($note);
			
			return new JSONResponse($note);
		} catch (\Exception $e) {
			return new JSONResponse(['error' => $e->getMessage()], 500);
		}
	}

	/**
	 * @NoAdminRequired
	 */
	public function delete(int $clientId, int $id): JSONResponse {
		try {
			// Verify client exists and user has access
			$this->clientMapper->find($clientId, $this->userId);
			
			$note = $this->mapper->find($id, $this->userId);
			$this->mapper->delete($note);
			
			return new JSONResponse(['success' => true]);
		} catch (\Exception $e) {
			return new JSONResponse(['error' => $e->getMessage()], 500);
		}
	}
}

