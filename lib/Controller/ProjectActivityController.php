<?php
declare(strict_types=1);

namespace OCA\DomainControl\Controller;

use OCA\DomainControl\AppInfo\Application;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;
use OCA\DomainControl\Db\ProjectActivityMapper;
use OCA\DomainControl\Db\ProjectActivity;
use OCA\DomainControl\Db\ProjectMapper;

class ProjectActivityController extends Controller {
	private $userId;
	private ProjectActivityMapper $mapper;
	private ProjectMapper $projectMapper;

	public function __construct(IRequest $request,
	                            ProjectActivityMapper $mapper,
	                            ProjectMapper $projectMapper,
	                            $userId) {
		parent::__construct(Application::APP_ID, $request);
		$this->mapper = $mapper;
		$this->projectMapper = $projectMapper;
		$this->userId = $userId;
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function index(int $projectId): JSONResponse {
		try {
			// Verify project exists and user has access
			$this->projectMapper->findIncludingShared($projectId, $this->userId);
			
			$activityType = $this->request->getParam('type');
			if ($activityType) {
				$activities = $this->mapper->findByType($projectId, $activityType);
			} else {
				$activities = $this->mapper->findAll($projectId);
			}
			
			return new JSONResponse($activities);
		} catch (\Exception $e) {
			return new JSONResponse(['error' => $e->getMessage()], 500);
		}
	}
}

