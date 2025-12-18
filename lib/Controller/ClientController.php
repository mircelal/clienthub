<?php
declare(strict_types=1);

namespace OCA\DomainControl\Controller;

use OCA\DomainControl\AppInfo\Application;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;
use OCA\DomainControl\Db\ClientMapper;
use OCA\DomainControl\Db\Client;

class ClientController extends Controller {
	private $userId;
	private ClientMapper $mapper;

	public function __construct(IRequest $request,
	                            ClientMapper $mapper,
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
			$clients = $this->mapper->findAll($this->userId);
			return new JSONResponse($clients);
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
			$client = $this->mapper->find($id, $this->userId);
			return new JSONResponse($client);
		} catch (\Exception $e) {
			return new JSONResponse(['error' => 'Client not found'], 404);
		}
	}

	/**
	 * @NoAdminRequired
	 */
	public function create(): JSONResponse {
		try {
			// Read raw body and parse
			$body = file_get_contents('php://input');
			parse_str($body, $data);
			
			$name = $data['name'] ?? '';
			$email = $data['email'] ?? '';
			$phone = $data['phone'] ?? '';
			$notes = $data['notes'] ?? '';
			
			if (empty($name)) {
				return new JSONResponse(['error' => 'Name is required'], 400);
			}
			
			$client = new Client();
			$client->setName($name);
			$client->setEmail($email);
			$client->setPhone($phone);
			$client->setNotes($notes);
			$client->setUserId($this->userId);
			$now = date('Y-m-d H:i:s');
			$client->setCreatedAt($now);
			$client->setUpdatedAt($now);
			
			$client = $this->mapper->insert($client);
			
			return new JSONResponse($client);
		} catch (\Exception $e) {
			return new JSONResponse(['error' => $e->getMessage()], 500);
		}
	}

	/**
	 * @NoAdminRequired
	 */
	public function update(int $id): JSONResponse {
		try {
			$client = $this->mapper->find($id, $this->userId);
			
			// Read raw body and parse
			$body = file_get_contents('php://input');
			parse_str($body, $data);
			
			if (isset($data['name']) && $data['name'] !== '') {
				$client->setName($data['name']);
			}
			if (isset($data['email'])) {
				$client->setEmail($data['email']);
			}
			if (isset($data['phone'])) {
				$client->setPhone($data['phone']);
			}
			if (isset($data['notes'])) {
				$client->setNotes($data['notes']);
			}
			
			$now = date('Y-m-d H:i:s');
			$client->setUpdatedAt($now);
			
			$client = $this->mapper->update($client);
			return new JSONResponse($client);
		} catch (\Exception $e) {
			return new JSONResponse(['error' => $e->getMessage()], 500);
		}
	}

	/**
	 * @NoAdminRequired
	 */
	public function delete(int $id): JSONResponse {
		try {
			$client = $this->mapper->find($id, $this->userId);
			$this->mapper->delete($client);
			return new JSONResponse(['success' => true]);
		} catch (\Exception $e) {
			return new JSONResponse(['error' => $e->getMessage()], 500);
		}
	}
}
