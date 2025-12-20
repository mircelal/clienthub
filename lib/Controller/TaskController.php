<?php
declare(strict_types=1);

namespace OCA\DomainControl\Controller;

use OCA\DomainControl\AppInfo\Application;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;
use OCA\DomainControl\Db\TaskMapper;
use OCA\DomainControl\Db\Task;

class TaskController extends Controller
{
	private $userId;
	private TaskMapper $mapper;

	public function __construct(
		IRequest $request,
		TaskMapper $mapper,
		$userId
	) {
		parent::__construct(Application::APP_ID, $request);
		$this->mapper = $mapper;
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
			$tasks = $this->mapper->findByProject($projectId, $this->userId);
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
			$task->setCompletedAt(null);
			$task->setCancelledAt(null);
			$task->setUserId($this->userId);
			$now = date('Y-m-d H:i:s');
			$task->setCreatedAt($now);
			$task->setUpdatedAt($now);

			$task = $this->mapper->insert($task);
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
			$data = $this->getRequestData();

			if (isset($data['parentId']))
				$task->setParentId((int) $data['parentId'] ?: null);
			if (isset($data['title']) && $data['title'] !== '')
				$task->setTitle($data['title']);
			if (isset($data['description']))
				$task->setDescription($data['description']);
			if (isset($data['notes']))
				$task->setNotes($data['notes']);
			if (isset($data['status'])) {
				$task->setStatus($data['status']);
				// Set completed date when status changes to done
				if ($data['status'] === 'done' && !$task->getCompletedAt()) {
					$task->setCompletedAt(date('Y-m-d H:i:s'));
				} elseif ($data['status'] !== 'done') {
					$task->setCompletedAt(null);
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
			$this->mapper->delete($task);
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

			$currentStatus = $task->getStatus();
			if ($currentStatus === 'done') {
				$task->setStatus('todo');
				$task->setCompletedAt(null);
			} else {
				$task->setStatus('done');
				$task->setCompletedAt(date('Y-m-d H:i:s'));
				$task->setCancelledAt(null); // Clear cancellation if marked done
			}

			$task->setUpdatedAt(date('Y-m-d H:i:s'));
			$task = $this->mapper->update($task);

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


