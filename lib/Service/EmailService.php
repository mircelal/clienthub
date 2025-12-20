<?php
declare(strict_types=1);

namespace OCA\DomainControl\Service;

use OCA\DomainControl\Db\ClientMapper;
use OCA\DomainControl\Db\DomainMapper;
use OCP\Mail\IMailer;
use OCP\IUserManager;
use Psr\Log\LoggerInterface;

class EmailService
{
	private IMailer $mailer;
	private DomainMapper $domainMapper;
	private ClientMapper $clientMapper;
	private LoggerInterface $logger;
	private IUserManager $userManager;

	public function __construct(
		IMailer $mailer,
		DomainMapper $domainMapper,
		ClientMapper $clientMapper,
		LoggerInterface $logger,
		IUserManager $userManager
	) {
		$this->mailer = $mailer;
		$this->domainMapper = $domainMapper;
		$this->clientMapper = $clientMapper;
		$this->logger = $logger;
		$this->userManager = $userManager;
	}

	/**
	 * Send expiration reminder emails for domains expiring soon
	 * @param string|null $userId
	 * @param int $days Number of days ahead to check (default: 30)
	 * @return array Array of sent email addresses
	 */
	public function sendDomainExpirationReminders(?string $userId, int $days = 30): array
	{
		$expiringDomains = $this->domainMapper->findExpiringSoon($userId, $days);
		$sentEmails = [];
		$stats = [
			'total_found' => count($expiringDomains),
			'no_client' => 0,
			'no_email' => 0,
			'errors' => 0,
			'details' => []
		];

		if ($stats['total_found'] === 0) {
			return ['sent' => [], 'stats' => $stats];
		}

		// Get account owner's email
		$owner = $this->userManager->get($userId);
		$ownerEmail = $owner ? $owner->getEMailAddress() : null;

		if (!$ownerEmail) {
			$stats['errors']++;
			$stats['details'][] = "Account owner has no email address set in Nextcloud profile.";
			return ['sent' => [], 'stats' => $stats];
		}

		foreach ($expiringDomains as $domain) {
			$clientName = 'Unknown Client';
			if ($domain->getClientId()) {
				try {
					$client = $this->clientMapper->find($domain->getClientId(), $userId);
					$clientName = $client->getName();
				} catch (\Exception $e) {
					$stats['no_client']++;
				}
			} else {
				$stats['no_client']++;
			}

			try {
				if (!$domain->getExpirationDate()) {
					$stats['details'][] = "Domain '{$domain->getDomainName()}' has no expiration date set.";
					continue;
				}

				$daysLeft = $this->calculateDaysUntilExpiry($domain->getExpirationDate());

				$this->sendDomainExpirationEmail(
					$ownerEmail,
					$clientName,
					$domain->getDomainName(),
					$domain->getExpirationDate(),
					$daysLeft
				);

				if (!in_array($ownerEmail, $sentEmails)) {
					$sentEmails[] = $ownerEmail;
				}

				$this->logger->info('Domain expiration reminder sent to owner', [
					'domain' => $domain->getDomainName(),
					'owner_email' => $ownerEmail,
					'client' => $clientName
				]);
			} catch (\Exception $e) {
				$stats['errors']++;
				$stats['details'][] = "Error for domain '{$domain->getDomainName()}': " . $e->getMessage();
				$this->logger->error('Failed to send domain expiration reminder to owner', [
					'domain_id' => $domain->getId(),
					'error' => $e->getMessage()
				]);
			}
		}

		return [
			'sent' => $sentEmails,
			'stats' => $stats
		];
	}

	/**
	 * Send email notification for domain expiration to admin
	 */
	private function sendDomainExpirationEmail(
		string $toEmail,
		string $clientName,
		string $domainName,
		string $expirationDate,
		int $daysLeft
	): void {
		$message = $this->mailer->createMessage();

		$subject = "Domain Bitmə Xatırlatması (Müştəri: {$clientName}) - {$domainName}";

		$plainBody = "Hörmətli Admin,\n\n";
		$plainBody .= "Aşağıdakı domainin bitmə vaxtı yaxınlaşır:\n\n";
		$plainBody .= "Müştəri: {$clientName}\n";
		$plainBody .= "Domain: {$domainName}\n";
		$plainBody .= "Bitmə Tarixi: {$expirationDate}\n";
		$plainBody .= "Qalan Gün: {$daysLeft} gün\n\n";
		$plainBody .= "Zəhmət olmasa müştəri ilə əlaqə saxlayın.\n\n";
		$plainBody .= "Hörmətlə,\nClientHub";

		$htmlBody = "<!doctype html><html><head><meta charset='UTF-8'></head><body>";
		$htmlBody .= "<h2>Domain Yeniləmə Xatırlatması</h2>";
		$htmlBody .= "<p>Hörmətli Admin,</p>";
		$htmlBody .= "<p>Aşağıdakı domainin bitmə vaxtı yaxınlaşır:</p>";
		$htmlBody .= "<table style='border-collapse: collapse; margin: 20px 0;'>";
		$htmlBody .= "<tr><td style='padding: 8px; border: 1px solid #ddd;'><strong>Müştəri:</strong></td><td style='padding: 8px; border: 1px solid #ddd;'>{$clientName}</td></tr>";
		$htmlBody .= "<tr><td style='padding: 8px; border: 1px solid #ddd;'><strong>Domain:</strong></td><td style='padding: 8px; border: 1px solid #ddd;'>{$domainName}</td></tr>";
		$htmlBody .= "<tr><td style='padding: 8px; border: 1px solid #ddd;'><strong>Bitmə Tarihi:</strong></td><td style='padding: 8px; border: 1px solid #ddd;'>{$expirationDate}</td></tr>";
		$htmlBody .= "<tr><td style='padding: 8px; border: 1px solid #ddd;'><strong>Qalan Gün:</strong></td><td style='padding: 8px; border: 1px solid #ddd;'><span style='color: " . ($daysLeft <= 7 ? 'red' : ($daysLeft <= 15 ? 'orange' : 'green')) . ";'>{$daysLeft} gün</span></td></tr>";
		$htmlBody .= "</table>";
		$htmlBody .= "<p>Zəhmət olmasa müştəri ilə əlaqə saxlayın.</p>";
		$htmlBody .= "<p>Hörmətlə,<br>ClientHub</p>";
		$htmlBody .= "</body></html>";

		$message->setSubject($subject);
		$message->setPlainBody($plainBody);
		$message->setHtmlBody($htmlBody);
		$message->setTo([$toEmail]);

		$this->mailer->send($message);
	}

	/**
	 * Calculate days until expiration
	 */
	private function calculateDaysUntilExpiry(string $expirationDate): int
	{
		$expiry = new \DateTime($expirationDate);
		$now = new \DateTime();
		$diff = $now->diff($expiry);

		if ($expiry < $now) {
			return 0;
		}

		return (int) $diff->days;
	}
}

