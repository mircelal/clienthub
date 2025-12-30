<?php
declare(strict_types=1);

namespace OCA\DomainControl\Service;

use OCA\DomainControl\Db\ProjectActivityMapper;
use OCA\DomainControl\Db\ProjectActivity;

class ProjectActivityService {
	private ProjectActivityMapper $mapper;

	public function __construct(ProjectActivityMapper $mapper) {
		$this->mapper = $mapper;
	}

	public function log(int $projectId, string $userId, string $activityType, ?string $description = null, array $metadata = []): void {
		try {
			$activity = new ProjectActivity();
			$activity->setProjectId($projectId);
			$activity->setUserId($userId);
			$activity->setActivityType($activityType);
			$activity->setDescription($description);
			$activity->setMetadata(json_encode($metadata));
			$activity->setCreatedAt(date('Y-m-d H:i:s'));
			
			$this->mapper->insert($activity);
		} catch (\Exception $e) {
			// Silently fail - activity logging should not break main functionality
			\OC::$server->getLogger()->warning('Failed to log project activity: ' . $e->getMessage());
		}
	}
}

