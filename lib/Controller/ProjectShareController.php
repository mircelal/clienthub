<?php
declare(strict_types=1);

namespace OCA\DomainControl\Controller;

use OCA\DomainControl\AppInfo\Application;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;
use OCA\DomainControl\Db\ProjectShareMapper;
use OCA\DomainControl\Db\ProjectMapper;
use OCA\DomainControl\Service\ProjectActivityService;

class ProjectShareController extends Controller
{
	private $userId;
	private ProjectShareMapper $shareMapper;
	private ProjectMapper $projectMapper;
	private ProjectActivityService $activityService;

	public function __construct(
		IRequest $request,
		ProjectShareMapper $shareMapper,
		ProjectMapper $projectMapper,
		ProjectActivityService $activityService,
		$userId
	) {
		parent::__construct(Application::APP_ID, $request);
		$this->shareMapper = $shareMapper;
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
	 * Get shares for a project
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function index(int $projectId): JSONResponse
	{
		try {
			// Check if user is project owner
			$project = $this->projectMapper->find($projectId, $this->userId);
			if (!$project) {
				return new JSONResponse(['error' => 'Project not found or access denied'], 404);
			}

			$shares = $this->shareMapper->findByProject($projectId);
			return new JSONResponse($shares);
		} catch (\Exception $e) {
			return new JSONResponse(['error' => $e->getMessage()], 500);
		}
	}

	/**
	 * Share project with user
	 * @NoAdminRequired
	 */
	public function share(int $projectId): JSONResponse
	{
		try {
			// Check if user is project owner
			$project = $this->projectMapper->find($projectId, $this->userId);
			if (!$project) {
				return new JSONResponse(['error' => 'Project not found or access denied'], 404);
			}

			$data = $this->getRequestData();
			$sharedWithUserId = $data['sharedWithUserId'] ?? null;
			$permissionLevel = $data['permissionLevel'] ?? 'read';

			if (!$sharedWithUserId) {
				return new JSONResponse(['error' => 'sharedWithUserId is required'], 400);
			}

			if (!in_array($permissionLevel, ['read', 'write'])) {
				return new JSONResponse(['error' => 'Invalid permission level'], 400);
			}

			// Check if already shared
			$existing = $this->shareMapper->findByProjectAndUser($projectId, $sharedWithUserId);
			if ($existing) {
				return new JSONResponse(['error' => 'Project already shared with this user'], 400);
			}

			// Don't allow sharing with yourself
			if ($sharedWithUserId === $this->userId) {
				return new JSONResponse(['error' => 'Cannot share with yourself'], 400);
			}

			$share = $this->shareMapper->share($projectId, $sharedWithUserId, $permissionLevel, $this->userId);
			
			// Log activity
			$this->activityService->log($projectId, $this->userId, 'project_shared', null, [
				'sharedWithUserId' => $sharedWithUserId,
				'permissionLevel' => $permissionLevel,
			]);
			
			return new JSONResponse($share);
		} catch (\Exception $e) {
			return new JSONResponse(['error' => $e->getMessage()], 500);
		}
	}

	/**
	 * Unshare project with user
	 * @NoAdminRequired
	 */
	public function unshare(int $projectId, string $sharedWithUserId): JSONResponse
	{
		try {
			// Check if user is project owner
			$project = $this->projectMapper->find($projectId, $this->userId);
			if (!$project) {
				return new JSONResponse(['error' => 'Project not found or access denied'], 404);
			}

			$this->shareMapper->unshare($projectId, $sharedWithUserId);
			
			// Log activity
			$this->activityService->log($projectId, $this->userId, 'project_unshared', null, [
				'sharedWithUserId' => $sharedWithUserId,
			]);
			
			return new JSONResponse(['success' => true]);
		} catch (\Exception $e) {
			return new JSONResponse(['error' => $e->getMessage()], 500);
		}
	}
}

