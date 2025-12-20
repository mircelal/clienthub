<?php
declare(strict_types=1);

namespace OCA\DomainControl\Controller;

use OCA\DomainControl\AppInfo\Application;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;
use OCA\DomainControl\Db\TimeEntryMapper;
use OCA\DomainControl\Db\TimeEntry;
use OCA\DomainControl\Db\ProjectMapper;

class TimeEntryController extends Controller {
	private $userId;
	private TimeEntryMapper $mapper;
	private ProjectMapper $projectMapper;

	public function __construct(IRequest $request,
	                            TimeEntryMapper $mapper,
	                            ProjectMapper $projectMapper,
	                            $userId) {
		parent::__construct(Application::APP_ID, $request);
		$this->mapper = $mapper;
		$this->projectMapper = $projectMapper;
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
	public function byProject(int $projectId): JSONResponse {
		try {
			// Verify project exists and user has access (owner or shared)
			$this->projectMapper->findIncludingShared($projectId, $this->userId);
			
			$entries = $this->mapper->findByProject($projectId, $this->userId);
			$totalDuration = $this->mapper->getTotalDuration($projectId, $this->userId);
			
			return new JSONResponse([
				'entries' => $entries,
				'totalDuration' => $totalDuration
			]);
		} catch (\Exception $e) {
			return new JSONResponse(['error' => $e->getMessage()], 500);
		}
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function getRunning(int $projectId): JSONResponse {
		try {
			// Verify project exists and user has access (owner or shared)
			$this->projectMapper->findIncludingShared($projectId, $this->userId);
			$running = $this->mapper->findRunning($projectId, $this->userId);
			
			return new JSONResponse($running ? $running : null);
		} catch (\Exception $e) {
			return new JSONResponse(['error' => $e->getMessage()], 500);
		}
	}

	/**
	 * @NoAdminRequired
	 */
	public function start(int $projectId): JSONResponse {
		try {
			// Verify project exists and user has access (owner or shared)
			$this->projectMapper->findIncludingShared($projectId, $this->userId);
			
			// Check if there's already a running entry
			$running = $this->mapper->findRunning($projectId, $this->userId);
			if ($running) {
				return new JSONResponse(['error' => 'Zaten çalışan bir zaman kaydı var'], 400);
			}
			
			$data = $this->getRequestData();
			
			$entry = new TimeEntry();
			$entry->setProjectId($projectId);
			$entry->setTaskId(isset($data['taskId']) && $data['taskId'] ? (int)$data['taskId'] : null);
			$entry->setDescription($data['description'] ?? '');
			// Use UTC time to avoid timezone issues
			$now = new \DateTime('now', new \DateTimeZone('UTC'));
			$entry->setStartTime($now->format('Y-m-d H:i:s'));
			$entry->setDuration(0);
			$entry->setIsRunning(true);
			$entry->setUserId($this->userId);
			$entry->setCreatedAt($now->format('Y-m-d H:i:s'));
			$entry->setUpdatedAt($now->format('Y-m-d H:i:s'));
			
			$entry = $this->mapper->insert($entry);
			return new JSONResponse($entry);
		} catch (\Exception $e) {
			return new JSONResponse(['error' => $e->getMessage()], 500);
		}
	}

	/**
	 * @NoAdminRequired
	 */
	public function stop(int $projectId): JSONResponse {
		try {
			// Verify project exists and user has access (owner or shared)
			$this->projectMapper->findIncludingShared($projectId, $this->userId);
			
			$running = $this->mapper->findRunning($projectId, $this->userId);
			if (!$running) {
				return new JSONResponse(['error' => 'Çalışan zaman kaydı bulunamadı'], 404);
			}
			
			// Use UTC time to avoid timezone issues
			$endTime = new \DateTime('now', new \DateTimeZone('UTC'));
			$startTime = new \DateTime($running->getStartTime(), new \DateTimeZone('UTC'));
			$duration = $endTime->getTimestamp() - $startTime->getTimestamp();
			
			$endTimeStr = $endTime->format('Y-m-d H:i:s');
			
			$running->setEndTime($endTimeStr);
			$running->setDuration($duration);
			$running->setIsRunning(false);
			$running->setUpdatedAt($endTimeStr);
			
			$running = $this->mapper->update($running);
			return new JSONResponse($running);
		} catch (\Exception $e) {
			return new JSONResponse(['error' => $e->getMessage()], 500);
		}
	}

	/**
	 * @NoAdminRequired
	 */
	public function update(int $id): JSONResponse {
		try {
			$entry = $this->mapper->find($id, $this->userId);
			$data = $this->getRequestData();
			
			if (isset($data['description'])) {
				$entry->setDescription($data['description']);
			}
			if (isset($data['startTime'])) {
				$entry->setStartTime($data['startTime']);
			}
			if (isset($data['endTime'])) {
				$entry->setEndTime($data['endTime']);
			}
			if (isset($data['duration'])) {
				$entry->setDuration((int)$data['duration']);
			}
			
			$entry->setUpdatedAt(date('Y-m-d H:i:s'));
			$entry = $this->mapper->update($entry);
			
			return new JSONResponse($entry);
		} catch (\Exception $e) {
			return new JSONResponse(['error' => $e->getMessage()], 500);
		}
	}

	/**
	 * @NoAdminRequired
	 */
	public function delete(int $id): JSONResponse {
		try {
			$entry = $this->mapper->find($id, $this->userId);
			$this->mapper->delete($entry);
			return new JSONResponse(['success' => true]);
		} catch (\Exception $e) {
			return new JSONResponse(['error' => $e->getMessage()], 500);
		}
	}
}

