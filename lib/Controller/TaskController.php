<?php
declare(strict_types=1);

namespace OCA\DomainControl\Controller;

use OCA\DomainControl\AppInfo\Application;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;
use OCA\DomainControl\Db\TaskMapper;
use OCA\DomainControl\Db\ProjectMapper;
use OCA\DomainControl\Db\Task;
use OCA\DomainControl\Service\ProjectActivityService;

class TaskController extends Controller
{
	private $userId;
	private TaskMapper $mapper;
	private ProjectMapper $projectMapper;
	private ProjectActivityService $activityService;

	public function __construct(
		IRequest $request,
		TaskMapper $mapper,
		ProjectMapper $projectMapper,
		ProjectActivityService $activityService,
		$userId
	) {
		parent::__construct(Application::APP_ID, $request);
		$this->mapper = $mapper;
		$this->projectMapper = $projectMapper;
		$this->activityService = $activityService;
		$this->userId = $userId;
	}

	private function getRequestData(): array
	{
		$body = file_get_contents('php://input');
		parse_str($body, $data);
		return $data;
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function index(): JSONResponse
	{
		try {
			$tasks = $this->mapper->findAll($this->userId);
			return new JSONResponse($tasks);
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
			$task = $this->mapper->find($id, $this->userId);
			return new JSONResponse($task);
		} catch (\Exception $e) {
			return new JSONResponse(['error' => 'Task not found'], 404);
		}
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function byProject(int $projectId): JSONResponse
	{
		try {
			// Check if user has access to project
			if (!$this->projectMapper->hasAccess($projectId, $this->userId, 'read')) {
				return new JSONResponse(['error' => 'Access denied'], 403);
			}
			
			$tasks = $this->mapper->findByProjectIncludingShared($projectId, $this->userId);
			return new JSONResponse($tasks);
		} catch (\Exception $e) {
			return new JSONResponse(['error' => $e->getMessage()], 500);
		}
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function byClient(int $clientId): JSONResponse
	{
		try {
			$tasks = $this->mapper->findByClient($clientId, $this->userId);
			return new JSONResponse($tasks);
		} catch (\Exception $e) {
			return new JSONResponse(['error' => $e->getMessage()], 500);
		}
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function pending(): JSONResponse
	{
		try {
			$tasks = $this->mapper->findPending($this->userId);
			return new JSONResponse($tasks);
		} catch (\Exception $e) {
			return new JSONResponse(['error' => $e->getMessage()], 500);
		}
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function approachingDeadline(): JSONResponse
	{
		try {
			$tasks = $this->mapper->findApproachingDeadline($this->userId, 7);
			return new JSONResponse($tasks);
		} catch (\Exception $e) {
			return new JSONResponse(['error' => $e->getMessage()], 500);
		}
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function overdue(): JSONResponse
	{
		try {
			$tasks = $this->mapper->findOverdue($this->userId);
			return new JSONResponse($tasks);
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
			$data = $this->getRequestData();

			$title = $data['title'] ?? '';
			if (empty($title)) {
				return new JSONResponse(['error' => 'Title is required'], 400);
			}

			// Check project access if projectId is provided
			if (isset($data['projectId']) && $data['projectId']) {
				$projectId = (int)$data['projectId'];
				if (!$this->projectMapper->hasAccess($projectId, $this->userId, 'write')) {
					return new JSONResponse(['error' => 'Access denied'], 403);
				}
			}

			$task = new Task();
			$task->setProjectId((int) ($data['projectId'] ?? 0) ?: null);
			$task->setClientId((int) ($data['clientId'] ?? 0) ?: null);
			$task->setParentId((int) ($data['parentId'] ?? 0) ?: null);
			$task->setTitle($title);
			$task->setDescription($data['description'] ?? '');
			$task->setNotes($data['notes'] ?? '');
			$task->setStatus($data['status'] ?? 'todo');
			$task->setPriority($data['priority'] ?? 'medium');
			$task->setDueDate($data['dueDate'] ?? '');
			$task->setAssignedToUserId($data['assignedToUserId'] ?? null);
			$task->setCompletedAt(null);
			$task->setCompletedByUserId(null);
			$task->setCancelledAt(null);
			$task->setUserId($this->userId);
			$now = date('Y-m-d H:i:s');
			$task->setCreatedAt($now);
			$task->setUpdatedAt($now);

			$task = $this->mapper->insert($task);
			
			// Log activity if task has project
			if ($task->getProjectId()) {
				$this->activityService->log($task->getProjectId(), $this->userId, 'task_created', null, [
					'taskId' => $task->getId(),
					'title' => $task->getTitle(),
				]);
			}
			
			return new JSONResponse($task);
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
			$task = $this->mapper->find($id, $this->userId);
			
			// Check project access if task has project
			if ($task->getProjectId()) {
				if (!$this->projectMapper->hasAccess($task->getProjectId(), $this->userId, 'write')) {
					return new JSONResponse(['error' => 'Access denied'], 403);
				}
			}
			
			$data = $this->getRequestData();

			if (isset($data['parentId']))
				$task->setParentId((int) $data['parentId'] ?: null);
			if (isset($data['title']) && $data['title'] !== '')
				$task->setTitle($data['title']);
			if (isset($data['description']))
				$task->setDescription($data['description']);
			if (isset($data['notes']))
				$task->setNotes($data['notes']);
			if (isset($data['assignedToUserId']))
				$task->setAssignedToUserId($data['assignedToUserId'] ?: null);
			if (isset($data['status'])) {
				$task->setStatus($data['status']);
				// Set completed date when status changes to done
				if ($data['status'] === 'done' && !$task->getCompletedAt()) {
					$task->setCompletedAt(date('Y-m-d H:i:s'));
					$task->setCompletedByUserId($this->userId);
				} elseif ($data['status'] !== 'done') {
					$task->setCompletedAt(null);
					$task->setCompletedByUserId(null);
				}

				// Set cancelled date
				if ($data['status'] === 'cancelled' && !$task->getCancelledAt()) {
					$task->setCancelledAt(date('Y-m-d H:i:s'));
				} elseif ($data['status'] !== 'cancelled') {
					$task->setCancelledAt(null);
				}
			}
			if (isset($data['priority']))
				$task->setPriority($data['priority']);
			if (isset($data['dueDate']))
				$task->setDueDate($data['dueDate']);

			$task->setUpdatedAt(date('Y-m-d H:i:s'));

			$task = $this->mapper->update($task);
			
			// Log activity if task has project
			if ($task->getProjectId()) {
				$activityType = ($task->getStatus() === 'done') ? 'task_completed' : 'task_updated';
				$this->activityService->log($task->getProjectId(), $this->userId, $activityType, null, [
					'taskId' => $task->getId(),
					'title' => $task->getTitle(),
				]);
			}
			
			return new JSONResponse($task);
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
			$task = $this->mapper->find($id, $this->userId);
			$projectId = $task->getProjectId();
			$this->mapper->delete($task);
			
			// Log activity if task had project
			if ($projectId) {
				$this->activityService->log($projectId, $this->userId, 'task_deleted', null, [
					'taskId' => $id,
				]);
			}
			
			return new JSONResponse(['success' => true]);
		} catch (\Exception $e) {
			return new JSONResponse(['error' => $e->getMessage()], 500);
		}
	}

	/**
	 * @NoAdminRequired
	 */
	public function toggleStatus(int $id): JSONResponse
	{
		try {
			$task = $this->mapper->find($id, $this->userId);
			
			// Check project access if task has project
			if ($task->getProjectId()) {
				if (!$this->projectMapper->hasAccess($task->getProjectId(), $this->userId, 'read')) {
					return new JSONResponse(['error' => 'Access denied'], 403);
				}
			}

			$currentStatus = $task->getStatus();
			if ($currentStatus === 'done') {
				$task->setStatus('todo');
				$task->setCompletedAt(null);
				$task->setCompletedByUserId(null);
			} else {
				$task->setStatus('done');
				$task->setCompletedAt(date('Y-m-d H:i:s'));
				$task->setCompletedByUserId($this->userId);
				$task->setCancelledAt(null); // Clear cancellation if marked done
			}

		$task->setUpdatedAt(date('Y-m-d H:i:s'));
		$task = $this->mapper->update($task);
		
		// Log activity if task has project
		if ($task->getProjectId()) {
			$activityType = ($task->getStatus() === 'done') ? 'task_completed' : 'task_updated';
			$this->activityService->log($task->getProjectId(), $this->userId, $activityType, null, [
				'taskId' => $task->getId(),
				'title' => $task->getTitle(),
			]);
		}

		return new JSONResponse($task);
		} catch (\Exception $e) {
			return new JSONResponse(['error' => $e->getMessage()], 500);
		}
	}

	/**
	 * @NoAdminRequired
	 */
	public function subtasks(int $id): JSONResponse
	{
		try {
			$subtasks = $this->mapper->findSubtasks($id, $this->userId);
			return new JSONResponse($subtasks);
		} catch (\Exception $e) {
			return new JSONResponse(['error' => $e->getMessage()], 500);
		}
	}
}


