<?php
declare(strict_types=1);

namespace OCA\DomainControl\Controller;

use OCA\DomainControl\AppInfo\Application;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;
use OCA\DomainControl\Db\ProjectMapper;
use OCA\DomainControl\Db\ProjectItemMapper;
use OCA\DomainControl\Db\TaskMapper;
use OCA\DomainControl\Db\ProjectShareMapper;
use OCA\DomainControl\Db\Project;
use OCA\DomainControl\Db\ProjectItem;

class ProjectController extends Controller {
	private $userId;
	private ProjectMapper $mapper;
	private ProjectItemMapper $itemMapper;
	private TaskMapper $taskMapper;
	private ProjectShareMapper $shareMapper;

	public function __construct(IRequest $request,
	                            ProjectMapper $mapper,
	                            ProjectItemMapper $itemMapper,
	                            TaskMapper $taskMapper,
	                            ProjectShareMapper $shareMapper,
	                            $userId) {
		parent::__construct(Application::APP_ID, $request);
		$this->mapper = $mapper;
		$this->itemMapper = $itemMapper;
		$this->taskMapper = $taskMapper;
		$this->shareMapper = $shareMapper;
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
			$projects = $this->mapper->findAllIncludingShared($this->userId);
			return new JSONResponse($projects);
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
			$project = $this->mapper->findIncludingShared($id, $this->userId);
			$items = $this->itemMapper->findByProject($id);
			$tasks = $this->taskMapper->findByProjectIncludingShared($id, $this->userId);
			$shares = $this->shareMapper->findByProject($id);
			
			$result = $project->jsonSerialize();
			$result['items'] = $items;
			$result['tasks'] = $tasks;
			$result['shares'] = $shares;
			$result['isOwner'] = ($project->getUserId() === $this->userId);
			
			return new JSONResponse($result);
		} catch (\Exception $e) {
			return new JSONResponse(['error' => 'Project not found'], 404);
		}
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function byClient(int $clientId): JSONResponse {
		try {
			$projects = $this->mapper->findByClient($clientId, $this->userId);
			return new JSONResponse($projects);
		} catch (\Exception $e) {
			return new JSONResponse(['error' => $e->getMessage()], 500);
		}
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function active(): JSONResponse {
		try {
			$projects = $this->mapper->findActive($this->userId);
			return new JSONResponse($projects);
		} catch (\Exception $e) {
			return new JSONResponse(['error' => $e->getMessage()], 500);
		}
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function approachingDeadline(): JSONResponse {
		try {
			$projects = $this->mapper->findApproachingDeadline($this->userId, 7);
			return new JSONResponse($projects);
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
			
			$project = new Project();
			$project->setClientId((int)($data['clientId'] ?? 0));
			$project->setName($name);
			$project->setProjectType($data['projectType'] ?? '');
			$project->setDescription($data['description'] ?? '');
			$project->setStatus($data['status'] ?? 'active');
			$project->setStartDate($data['startDate'] ?? date('Y-m-d'));
			$project->setDeadline($data['deadline'] ?? '');
			$project->setBudget((float)($data['budget'] ?? 0));
			$project->setCurrency($data['currency'] ?? 'USD');
			$project->setNotes($data['notes'] ?? '');
			$project->setUserId($this->userId);
			$now = date('Y-m-d H:i:s');
			$project->setCreatedAt($now);
			$project->setUpdatedAt($now);
			
			$project = $this->mapper->insert($project);
			return new JSONResponse($project);
		} catch (\Exception $e) {
			return new JSONResponse(['error' => $e->getMessage()], 500);
		}
	}

	/**
	 * @NoAdminRequired
	 */
	public function update(int $id): JSONResponse {
		try {
			// Check access - must be owner or have write permission
			$project = $this->mapper->findIncludingShared($id, $this->userId);
			if (!$this->mapper->hasAccess($id, $this->userId, 'write')) {
				return new JSONResponse(['error' => 'Access denied'], 403);
			}
			
			$data = $this->getRequestData();
			
			if (isset($data['clientId'])) $project->setClientId((int)$data['clientId']);
			if (isset($data['name']) && $data['name'] !== '') $project->setName($data['name']);
			if (isset($data['projectType'])) $project->setProjectType($data['projectType']);
			if (isset($data['description'])) $project->setDescription($data['description']);
			if (isset($data['status'])) $project->setStatus($data['status']);
			if (isset($data['startDate'])) $project->setStartDate($data['startDate']);
			if (isset($data['deadline'])) $project->setDeadline($data['deadline']);
			if (isset($data['budget'])) $project->setBudget((float)$data['budget']);
			if (isset($data['currency'])) $project->setCurrency($data['currency']);
			if (isset($data['notes'])) $project->setNotes($data['notes']);
			
			$project->setUpdatedAt(date('Y-m-d H:i:s'));
			
			$project = $this->mapper->update($project);
			return new JSONResponse($project);
		} catch (\Exception $e) {
			return new JSONResponse(['error' => $e->getMessage()], 500);
		}
	}
	
	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function items(int $id): JSONResponse {
		try {
			// Check access
			if (!$this->mapper->hasAccess($id, $this->userId, 'read')) {
				return new JSONResponse(['error' => 'Access denied'], 403);
			}
			
			$items = $this->itemMapper->findByProject($id);
			return new JSONResponse($items);
		} catch (\Exception $e) {
			return new JSONResponse(['error' => $e->getMessage()], 500);
		}
	}

	/**
	 * @NoAdminRequired
	 */
	public function delete(int $id): JSONResponse {
		try {
			// Only owner can delete
			$project = $this->mapper->find($id, $this->userId);
			if ($project->getUserId() !== $this->userId) {
				return new JSONResponse(['error' => 'Only project owner can delete'], 403);
			}
			
			// Delete items first
			$this->itemMapper->deleteByProject($id);
			// Delete shares
			$shares = $this->shareMapper->findByProject($id);
			foreach ($shares as $share) {
				$this->shareMapper->delete($share);
			}
			$this->mapper->delete($project);
			return new JSONResponse(['success' => true]);
		} catch (\Exception $e) {
			return new JSONResponse(['error' => $e->getMessage()], 500);
		}
	}

	/**
	 * @NoAdminRequired
	 */
	public function addItem(int $id): JSONResponse {
		try {
			// Check access - must be owner or have write permission
			if (!$this->mapper->hasAccess($id, $this->userId, 'write')) {
				return new JSONResponse(['error' => 'Access denied'], 403);
			}
			
			$project = $this->mapper->findIncludingShared($id, $this->userId);
			$data = $this->getRequestData();
			
			$itemType = $data['itemType'] ?? '';
			$itemId = (int)($data['itemId'] ?? 0);
			
			if (empty($itemType) || $itemId === 0) {
				return new JSONResponse(['error' => 'Item type and ID are required'], 400);
			}
			
			// Check if already exists
			if ($this->itemMapper->exists($id, $itemType, $itemId)) {
				return new JSONResponse(['error' => 'Item already linked to project'], 400);
			}
			
			$item = new ProjectItem();
			$item->setProjectId($id);
			$item->setItemType($itemType);
			$item->setItemId($itemId);
			$item->setCreatedAt(date('Y-m-d H:i:s'));
			
			$item = $this->itemMapper->insert($item);
			return new JSONResponse($item);
		} catch (\Exception $e) {
			return new JSONResponse(['error' => $e->getMessage()], 500);
		}
	}

	/**
	 * @NoAdminRequired
	 */
	public function removeItem(int $id, int $itemId): JSONResponse {
		try {
			// Check access - must be owner or have write permission
			if (!$this->mapper->hasAccess($id, $this->userId, 'write')) {
				return new JSONResponse(['error' => 'Access denied'], 403);
			}
			
			$project = $this->mapper->findIncludingShared($id, $this->userId);
			$item = $this->itemMapper->find($itemId);
			
			if ($item->getProjectId() !== $id) {
				return new JSONResponse(['error' => 'Item does not belong to this project'], 400);
			}
			
			$this->itemMapper->delete($item);
			return new JSONResponse(['success' => true]);
		} catch (\Exception $e) {
			return new JSONResponse(['error' => $e->getMessage()], 500);
		}
	}
}


