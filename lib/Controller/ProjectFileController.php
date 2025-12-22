<?php
declare(strict_types=1);

namespace OCA\DomainControl\Controller;

use OCA\DomainControl\AppInfo\Application;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;
use OCA\DomainControl\Db\ProjectFileMapper;
use OCA\DomainControl\Db\ProjectFile;
use OCA\DomainControl\Db\ProjectMapper;
use OCA\DomainControl\Service\ProjectActivityService;
use OCP\Files\IRootFolder;
use OCP\Files\NotPermittedException;

class ProjectFileController extends Controller {
	private $userId;
	private ProjectFileMapper $mapper;
	private ProjectMapper $projectMapper;
	private IRootFolder $rootFolder;
	private ProjectActivityService $activityService;

	public function __construct(IRequest $request,
	                            ProjectFileMapper $mapper,
	                            ProjectMapper $projectMapper,
	                            IRootFolder $rootFolder,
	                            ProjectActivityService $activityService,
	                            $userId) {
		parent::__construct(Application::APP_ID, $request);
		$this->mapper = $mapper;
		$this->projectMapper = $projectMapper;
		$this->rootFolder = $rootFolder;
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
				$files = $this->mapper->findByCategory($projectId, $this->userId, $category);
			} else {
				$files = $this->mapper->findAll($projectId, $this->userId);
			}
			
			return new JSONResponse($files);
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
			
			$file = $this->mapper->find($id, $this->userId);
			return new JSONResponse($file);
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
			$project = $this->projectMapper->findIncludingShared($projectId, $this->userId);
			
			$data = $this->getRequestData();
			
			// Get user folder
			$userFolder = $this->rootFolder->getUserFolder($this->userId);
			
			// Create project folder if it doesn't exist
			$projectFolderName = 'Projects/' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $project->getName());
			if (!$userFolder->nodeExists($projectFolderName)) {
				$userFolder->newFolder($projectFolderName);
			}
			$projectFolder = $userFolder->get($projectFolderName);
			
			// Create Files subfolder if it doesn't exist
			if (!$projectFolder->nodeExists('Files')) {
				$projectFolder->newFolder('Files');
			}
			$filesFolder = $projectFolder->get('Files');
			
			// Handle file upload
			$uploadedFile = $_FILES['file'] ?? null;
			if (!$uploadedFile || $uploadedFile['error'] !== UPLOAD_ERR_OK) {
				return new JSONResponse(['error' => 'File upload failed'], 400);
			}
			
			$fileName = $uploadedFile['name'];
			$filePath = $filesFolder->getPath() . '/' . $fileName;
			
			// Move uploaded file
			move_uploaded_file($uploadedFile['tmp_name'], $filePath);
			
			// Create file record
			$file = new ProjectFile();
			$file->setProjectId($projectId);
			$file->setUserId($this->userId);
			$file->setFilePath($filePath);
			$file->setFileName($fileName);
			$file->setFileSize($uploadedFile['size']);
			$file->setMimeType($uploadedFile['type'] ?? 'application/octet-stream');
			$file->setCategory($data['category'] ?? 'general');
			$file->setDescription($data['description'] ?? '');
			$now = date('Y-m-d H:i:s');
			$file->setCreatedAt($now);
			$file->setUpdatedAt($now);
			
			$file = $this->mapper->insert($file);
			
			// Log activity
			$this->logActivity($projectId, 'file_uploaded', [
				'fileId' => $file->getId(),
				'fileName' => $file->getFileName(),
			]);
			
			return new JSONResponse($file);
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
			
			$file = $this->mapper->find($id, $this->userId);
			
			// Delete physical file
			try {
				$userFolder = $this->rootFolder->getUserFolder($this->userId);
				if ($userFolder->nodeExists($file->getFilePath())) {
					$fileNode = $userFolder->get($file->getFilePath());
					$fileNode->delete();
				}
			} catch (\Exception $e) {
				// File might already be deleted, continue
			}
			
			$this->mapper->delete($file);
			
			// Log activity
			$this->logActivity($projectId, 'file_deleted', [
				'fileId' => $id,
				'fileName' => $file->getFileName(),
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

