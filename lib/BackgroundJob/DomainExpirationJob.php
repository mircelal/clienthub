<?php
declare(strict_types=1);

namespace OCA\DomainControl\BackgroundJob;

use OCA\DomainControl\Service\EmailService;
use OCA\DomainControl\Db\DomainMapper;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\BackgroundJob\TimedJob;
use OCP\IDBConnection;
use Psr\Log\LoggerInterface;

class DomainExpirationJob extends TimedJob {
	private EmailService $emailService;
	private DomainMapper $domainMapper;
	private IDBConnection $db;
	private LoggerInterface $logger;

	public function __construct(
		ITimeFactory $time,
		EmailService $emailService,
		DomainMapper $domainMapper,
		IDBConnection $db,
		LoggerInterface $logger
	) {
		parent::__construct($time);
		$this->emailService = $emailService;
		$this->domainMapper = $domainMapper;
		$this->db = $db;
		$this->logger = $logger;
		
		// Run daily
		$this->setInterval(24 * 60 * 60);
	}

	protected function run($argument): void {
		$this->logger->info('DomainExpirationJob: Starting domain expiration email check');
		
		try {
			// Get all unique user IDs from domains table
			$qb = $this->db->getQueryBuilder();
			$qb->selectDistinct('user_id')
				->from('domaincontrol_domains')
				->where($qb->expr()->isNotNull('user_id'));
			
			$result = $qb->executeQuery();
			$userIds = [];
			while ($row = $result->fetch()) {
				if ($row['user_id']) {
					$userIds[] = $row['user_id'];
				}
			}
			$result->closeCursor();
			
			$totalProcessed = 0;
			$totalErrors = 0;
			
			// Process each user's domains
			foreach ($userIds as $userId) {
				try {
					$result = $this->emailService->sendAutomaticDomainExpirationReminders($userId);
					$totalProcessed += $result['stats']['total_found'];
					$totalErrors += $result['stats']['errors'];
					
					$this->logger->info('DomainExpirationJob: Processed user domains', [
						'user_id' => $userId,
						'domains_found' => $result['stats']['total_found'],
						'emails_sent' => count($result['sent']),
						'errors' => $result['stats']['errors']
					]);
				} catch (\Exception $e) {
					$totalErrors++;
					$this->logger->error('DomainExpirationJob: Error processing user domains', [
						'user_id' => $userId,
						'error' => $e->getMessage(),
						'exception' => $e
					]);
				}
			}
			
			$this->logger->info('DomainExpirationJob: Completed', [
				'total_processed' => $totalProcessed,
				'total_errors' => $totalErrors,
				'users_processed' => count($userIds)
			]);
			
		} catch (\Exception $e) {
			$this->logger->error('DomainExpirationJob: Fatal error', [
				'error' => $e->getMessage(),
				'exception' => $e
			]);
		}
	}
}

