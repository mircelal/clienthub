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

	/**
	 * Get contacts from Nextcloud Contacts app
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function getContacts(): JSONResponse {
		try {
			// Check if Contacts app is available
			$appManager = \OC::$server->getAppManager();
			if (!$appManager->isEnabledForUser('contacts')) {
				return new JSONResponse([]);
			}

			// Use Contacts app's API if available
			// Try to use Contacts app's controller
			$contacts = $this->fetchContactsFromContactsApp();
			return new JSONResponse($contacts);
		} catch (\Exception $e) {
			// Return empty array on error
			return new JSONResponse([]);
		}
	}

	/**
	 * Fetch contacts from Contacts app using CardDAV
	 */
	private function fetchContactsFromContactsApp(): array {
		// Direct database access is most reliable
		return $this->fetchContactsFromDatabase();
	}

	/**
	 * Fetch contacts directly from database (last resort)
	 */
	private function fetchContactsFromDatabase(): array {
		$contacts = [];
		
		try {
			// Contacts app stores data in cards table (with prefix)
			$db = \OC::$server->getDatabaseConnection();
			$qb = $db->getQueryBuilder();
			
			// Get all addressbooks for user first
			$addressBookQb = $db->getQueryBuilder();
			$addressBookQb->select('id')
				->from('addressbooks')
				->where($addressBookQb->expr()->eq('principaluri', $addressBookQb->createNamedParameter('principals/users/' . $this->userId)));
			
			$addressBookResult = $addressBookQb->executeQuery();
			$addressBookIds = [];
			while ($row = $addressBookResult->fetch()) {
				$addressBookIds[] = $row['id'];
			}
			$addressBookResult->closeCursor();
			
			if (empty($addressBookIds)) {
				return [];
			}
			
			// Get all cards from user's addressbooks
			$qb->select('carddata')
				->from('cards')
				->where($qb->expr()->in('addressbookid', $qb->createNamedParameter($addressBookIds, \Doctrine\DBAL\Connection::PARAM_INT_ARRAY)));
			
			$result = $qb->executeQuery();
			while ($row = $result->fetch()) {
				$contact = $this->parseVCard($row['carddata']);
				if ($contact && $contact['name']) {
					$contacts[] = $contact;
				}
			}
			$result->closeCursor();
		} catch (\Exception $e) {
			\OC::$server->getLogger()->error('Error fetching contacts from database: ' . $e->getMessage());
		}
		
		return $contacts;
	}

	/**
	 * Parse VCard format
	 */
	private function parseVCard(string $vcard): array {
		$contact = [
			'id' => '',
			'name' => '',
			'email' => '',
			'phone' => '',
			'organization' => '',
			'notes' => ''
		];
		
		$lines = explode("\n", $vcard);
		foreach ($lines as $line) {
			$line = trim($line);
			if (strpos($line, 'FN:') === 0) {
				$contact['name'] = substr($line, 3);
			} elseif (preg_match('/^EMAIL[^:]*:(.+)$/i', $line, $matches)) {
				if (empty($contact['email'])) {
					$contact['email'] = trim($matches[1]);
				}
			} elseif (preg_match('/^TEL[^:]*:(.+)$/i', $line, $matches)) {
				if (empty($contact['phone'])) {
					$contact['phone'] = trim($matches[1]);
				}
			} elseif (strpos($line, 'ORG:') === 0) {
				$contact['organization'] = substr($line, 4);
			} elseif (strpos($line, 'NOTE:') === 0) {
				$contact['notes'] = substr($line, 5);
			} elseif (strpos($line, 'UID:') === 0) {
				$contact['id'] = substr($line, 4);
			}
		}
		
		return $contact;
	}
}
