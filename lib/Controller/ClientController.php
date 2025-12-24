<?php
declare(strict_types=1);

namespace OCA\DomainControl\Controller;

use OCA\DomainControl\AppInfo\Application;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;
use OCP\IGroupManager;
use OCA\DomainControl\Db\ClientMapper;
use OCA\DomainControl\Db\Client;

class ClientController extends Controller {
	private $userId;
	private ClientMapper $mapper;
	private IGroupManager $groupManager;

	public function __construct(IRequest $request,
	                            ClientMapper $mapper,
	                            IGroupManager $groupManager,
	                            $userId) {
		parent::__construct(Application::APP_ID, $request);
		$this->mapper = $mapper;
		$this->groupManager = $groupManager;
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
			if (isset($data['ncUserId'])) {
				$client->setNcUserId($data['ncUserId']);
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
	 * Test endpoint: Create Nextcloud user for testing
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function testCreateNextcloudUser(): JSONResponse {
		try {
			// Get request data
			$body = file_get_contents('php://input');
			$data = json_decode($body, true) ?: [];
			
		$testUsername = $data['username'] ?? 'test_user_' . time();
		$testPassword = $data['password'] ?? bin2hex(random_bytes(8));
		$testDisplayName = $data['displayName'] ?? 'Test User';
		$testEmail = $data['email'] ?? null;
		$testGroup = $data['group'] ?? null;
		$testQuota = $data['quota'] ?? null;
		
		// Validate: Password or email is required (Nextcloud requirement)
		if (empty($testPassword) && empty($testEmail)) {
			return new JSONResponse([
				'success' => false,
				'error' => 'Password or email address is required'
			], 400);
		}
		
		// Get UserManager
		$userManager = \OC::$server->getUserManager();
		
		// Check if user already exists
		if ($userManager->userExists($testUsername)) {
			return new JSONResponse([
				'success' => false,
				'error' => 'User already exists',
				'username' => $testUsername
			], 400);
		}
		
		// Create user (password is required for createUser method)
		if (empty($testPassword)) {
			return new JSONResponse([
				'success' => false,
				'error' => 'Password is required to create user via API'
			], 400);
		}
		
		$user = $userManager->createUser($testUsername, $testPassword);
			
			if (!$user) {
				return new JSONResponse([
					'success' => false,
					'error' => 'Failed to create user'
				], 500);
			}
			
		// Set display name
		$user->setDisplayName($testDisplayName);
		
		// Set email if provided (optional, but recommended)
		if (!empty($testEmail)) {
			$user->setEMailAddress($testEmail);
		}
		
		// Add to group if provided
		if (!empty($testGroup)) {
			$group = $this->groupManager->get($testGroup);
			if ($group) {
				$group->addUser($user);
			}
		}
		
		// Set quota if provided
		if ($testQuota !== null && $testQuota !== '') {
			$quotaBytes = (float)$testQuota * 1024 * 1024 * 1024; // Convert GB to bytes
			$user->setQuota((string)$quotaBytes);
		}
			
			return new JSONResponse([
				'success' => true,
				'username' => $testUsername,
				'displayName' => $testDisplayName,
				'email' => $testEmail,
				'group' => $testGroup,
				'quota' => $testQuota,
				'message' => 'Test user created successfully'
			]);
		} catch (\Exception $e) {
			return new JSONResponse([
				'success' => false,
				'error' => $e->getMessage(),
				'trace' => $e->getTraceAsString()
			], 500);
		}
	}

	/**
	 * Get list of Nextcloud groups
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function getNextcloudGroups(): JSONResponse {
		try {
			$groups = [];
			$logger = \OC::$server->getLogger();
			
			// Method 1: search() with empty string (should return all groups)
			try {
				$foundGroups = $this->groupManager->search('');
				$logger->info('IGroupManager->search("") returned ' . count($foundGroups) . ' groups');
				foreach ($foundGroups as $group) {
					if ($group) {
						$gid = $group->getGID();
						$groups[] = $gid;
						$logger->debug('Found group: ' . $gid);
					}
				}
			} catch (\Exception $e) {
				$logger->warning('search() method failed: ' . $e->getMessage());
			}
			
			// Method 2: callAll() - iterate through all groups
			if (empty($groups)) {
				try {
					$count = 0;
					$this->groupManager->callAll(function ($group) use (&$groups, &$count) {
						if ($group) {
							$gid = $group->getGID();
							$groups[] = $gid;
							$count++;
						}
					});
					$logger->info('callAll() method found ' . $count . ' groups');
				} catch (\Exception $e) {
					$logger->warning('callAll() method failed: ' . $e->getMessage());
				}
			}
			
			// Remove duplicates and sort
			$groups = array_unique($groups);
			sort($groups);
			
			// Final log
			$logger->info('Total unique groups found: ' . count($groups), [
				'groups' => $groups,
				'userId' => $this->userId
			]);
			
			return new JSONResponse(array_values($groups));
		} catch (\Exception $e) {
			// Log error with full details
			\OC::$server->getLogger()->error('Error loading Nextcloud groups: ' . $e->getMessage(), [
				'exception' => $e,
				'trace' => $e->getTraceAsString()
			]);
			return new JSONResponse([
				'error' => $e->getMessage(),
				'debug' => 'Check Nextcloud logs for details'
			], 500);
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
