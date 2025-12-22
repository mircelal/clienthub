<?php
declare(strict_types=1);

namespace OCA\DomainControl\Controller;

use OCA\DomainControl\AppInfo\Application;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;
use OCA\DomainControl\Db\ProjectNoteMapper;
use OCA\DomainControl\Db\ProjectNote;
use OCA\DomainControl\Db\ProjectMapper;
use OCA\DomainControl\Service\ProjectActivityService;

class ProjectNoteController extends Controller {
	private $userId;
	private ProjectNoteMapper $mapper;
	private ProjectMapper $projectMapper;
	private ProjectActivityService $activityService;

	public function __construct(IRequest $request,
	                            ProjectNoteMapper $mapper,
	                            ProjectMapper $projectMapper,
	                            ProjectActivityService $activityService,
	                            $userId) {
		parent::__construct(Application::APP_ID, $request);
		$this->mapper = $mapper;
		$this->projectMapper = $projectMapper;
		$this->activityService = $activityService;
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
	public function index(int $projectId): JSONResponse {
		try {
			// Verify project exists and user has access
			$this->projectMapper->findIncludingShared($projectId, $this->userId);
			
			$category = $this->request->getParam('category');
			if ($category) {
				$notes = $this->mapper->findByCategory($projectId, $this->userId, $category);
			} else {
				$notes = $this->mapper->findAll($projectId, $this->userId);
			}
			
			return new JSONResponse($notes);
		} catch (\Exception $e) {
			return new JSONResponse(['error' => $e->getMessage()], 500);
		}
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function show(int $projectId, int $id): JSONResponse {
		try {
			// Verify project exists and user has access
			$this->projectMapper->findIncludingShared($projectId, $this->userId);
			
			$note = $this->mapper->find($id, $this->userId);
			return new JSONResponse($note);
		} catch (\Exception $e) {
			return new JSONResponse(['error' => $e->getMessage()], 500);
		}
	}

	/**
	 * @NoAdminRequired
	 */
	public function create(int $projectId): JSONResponse {
		try {
			// Verify project exists and user has access
			$this->projectMapper->findIncludingShared($projectId, $this->userId);
			
			$data = $this->getRequestData();
			
			$note = new ProjectNote();
			$note->setProjectId($projectId);
			$note->setUserId($this->userId);
			$note->setCategory($data['category'] ?? 'general');
			$note->setTitle($data['title'] ?? '');
			$note->setContent($data['content'] ?? '');
			$note->setStatus($data['status'] ?? null);
			$now = date('Y-m-d H:i:s');
			$note->setCreatedAt($now);
			$note->setUpdatedAt($now);
			
			$note = $this->mapper->insert($note);
			
			// Log activity
			$this->logActivity($projectId, 'note_created', [
				'noteId' => $note->getId(),
				'title' => $note->getTitle(),
			]);
			
			return new JSONResponse($note);
		} catch (\Exception $e) {
			return new JSONResponse(['error' => $e->getMessage()], 500);
		}
	}

	/**
	 * @NoAdminRequired
	 */
	public function update(int $projectId, int $id): JSONResponse {
		try {
			// Verify project exists and user has access
			$this->projectMapper->findIncludingShared($projectId, $this->userId);
			
			$note = $this->mapper->find($id, $this->userId);
			$data = $this->getRequestData();
			
			if (isset($data['title'])) {
				$note->setTitle($data['title']);
			}
			if (isset($data['content'])) {
				$note->setContent($data['content']);
			}
			if (isset($data['category'])) {
				$note->setCategory($data['category']);
			}
			if (isset($data['status'])) {
				$note->setStatus($data['status']);
			}
			$note->setUpdatedAt(date('Y-m-d H:i:s'));
			
			$note = $this->mapper->update($note);
			
			// Log activity
			$this->logActivity($projectId, 'note_updated', [
				'noteId' => $note->getId(),
				'title' => $note->getTitle(),
			]);
			
			return new JSONResponse($note);
		} catch (\Exception $e) {
			return new JSONResponse(['error' => $e->getMessage()], 500);
		}
	}

	/**
	 * @NoAdminRequired
	 */
	public function delete(int $projectId, int $id): JSONResponse {
		try {
			// Verify project exists and user has access
			$this->projectMapper->findIncludingShared($projectId, $this->userId);
			
			$note = $this->mapper->find($id, $this->userId);
			$this->mapper->delete($note);
			
			// Log activity
			$this->logActivity($projectId, 'note_deleted', [
				'noteId' => $id,
			]);
			
			return new JSONResponse(['success' => true]);
		} catch (\Exception $e) {
			return new JSONResponse(['error' => $e->getMessage()], 500);
		}
	}

	private function logActivity(int $projectId, string $type, array $metadata = []): void {
		$this->activityService->log($projectId, $this->userId, $type, null, $metadata);
	}
}

