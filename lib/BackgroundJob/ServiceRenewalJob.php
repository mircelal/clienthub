<?php
declare(strict_types=1);

namespace OCA\DomainControl\BackgroundJob;

use OCA\DomainControl\Db\ServiceMapper;
use OCA\DomainControl\Db\InvoiceMapper;
use OCA\DomainControl\Db\InvoiceItemMapper;
use OCA\DomainControl\Db\Invoice;
use OCA\DomainControl\Db\InvoiceItem;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\BackgroundJob\TimedJob;
use OCP\IL10N;
use Psr\Log\LoggerInterface;

class ServiceRenewalJob extends TimedJob {
	private ServiceMapper $serviceMapper;
	private InvoiceMapper $invoiceMapper;
	private InvoiceItemMapper $invoiceItemMapper;
	private IL10N $l10n;
	private LoggerInterface $logger;

	public function __construct(
		ITimeFactory $time,
		ServiceMapper $serviceMapper,
		InvoiceMapper $invoiceMapper,
		InvoiceItemMapper $invoiceItemMapper,
		IL10N $l10n,
		LoggerInterface $logger
	) {
		parent::__construct($time);
		$this->serviceMapper = $serviceMapper;
		$this->invoiceMapper = $invoiceMapper;
		$this->invoiceItemMapper = $invoiceItemMapper;
		$this->l10n = $l10n;
		$this->logger = $logger;
		
		// Run daily
		$this->setInterval(24 * 60 * 60);
	}

	protected function run($argument): void {
		$this->logger->info('ServiceRenewalJob: Starting service renewal check');
		
		try {
			// Find all expired active services
			$expiredServices = $this->serviceMapper->findAllActiveForRenewal();
			
			if (empty($expiredServices)) {
				$this->logger->info('ServiceRenewalJob: No expired services found');
				return;
			}
			
			$this->logger->info('ServiceRenewalJob: Found ' . count($expiredServices) . ' expired services');
			
			$processedCount = 0;
			$errorCount = 0;
			
			foreach ($expiredServices as $service) {
				try {
					// Check if service has price and client
					if ($service->getPrice() <= 0 || $service->getClientId() <= 0) {
						$this->logger->warning('ServiceRenewalJob: Skipping service ' . $service->getId() . ' - no price or client');
						continue;
					}
					
					// Create invoice for expired service
					$invoice = new Invoice();
					$invoice->setClientId($service->getClientId());
					$invoice->setInvoiceNumber($this->invoiceMapper->generateInvoiceNumber($service->getUserId()));
					$invoice->setTitle($this->l10n->t('Service Renewal Invoice: %s', [$service->getName()]));
					$invoice->setIssueDate(date('Y-m-d'));
					$invoice->setDueDate(date('Y-m-d', strtotime('+30 days')));
					$invoice->setTotalAmount($service->getPrice());
					$invoice->setPaidAmount(0);
					$invoice->setCurrency($service->getCurrency());
					$invoice->setStatus('draft');
					$invoice->setNotes($this->l10n->t('Auto-generated renewal invoice for service: %s (Expired: %s)', [
						$service->getName(),
						$service->getExpirationDate()
					]));
					$invoice->setUserId($service->getUserId());
					$now = date('Y-m-d H:i:s');
					$invoice->setCreatedAt($now);
					$invoice->setUpdatedAt($now);
					
					$invoice = $this->invoiceMapper->insert($invoice);
					
					// Add service as invoice item
					$item = new InvoiceItem();
					$item->setInvoiceId($invoice->getId());
					$item->setItemType('service');
					$item->setItemId($service->getId());
					$item->setDescription($service->getName());
					$item->setQuantity(1);
					$item->setUnitPrice($service->getPrice());
					$item->setTotalPrice($service->getPrice());
					$item->setCurrency($service->getCurrency());
					if ($service->getStartDate()) {
						$item->setPeriodStart($service->getStartDate());
					}
					if ($service->getExpirationDate()) {
						$item->setPeriodEnd($service->getExpirationDate());
					}
					$item->setCreatedAt($now);
					$this->invoiceItemMapper->insert($item);
					
					// Update invoice total
					$invoice->setTotalAmount($service->getPrice());
					$this->invoiceMapper->update($invoice);
					
					// Renew service expiration date based on renewal interval
					$newExpirationDate = $this->calculateNewExpirationDate(
						$service->getExpirationDate(),
						$service->getRenewalInterval()
					);
					
					if ($newExpirationDate) {
						$service->setExpirationDate($newExpirationDate);
						$service->setStartDate(date('Y-m-d')); // Update start date to today
						$service->setUpdatedAt($now);
						$this->serviceMapper->update($service);
						
						$this->logger->info('ServiceRenewalJob: Created invoice and renewed service ' . $service->getId() . 
							' until ' . $newExpirationDate);
					} else {
						$this->logger->warning('ServiceRenewalJob: Could not calculate new expiration date for service ' . $service->getId());
					}
					
					$processedCount++;
					
				} catch (\Exception $e) {
					$errorCount++;
					$this->logger->error('ServiceRenewalJob: Error processing service ' . $service->getId() . ': ' . $e->getMessage(), [
						'exception' => $e
					]);
				}
			}
			
			$this->logger->info('ServiceRenewalJob: Completed. Processed: ' . $processedCount . ', Errors: ' . $errorCount);
			
		} catch (\Exception $e) {
			$this->logger->error('ServiceRenewalJob: Fatal error: ' . $e->getMessage(), [
				'exception' => $e
			]);
		}
	}

	/**
	 * Calculate new expiration date based on renewal interval
	 */
	private function calculateNewExpirationDate(string $currentExpirationDate, string $renewalInterval): ?string {
		if (empty($currentExpirationDate)) {
			$baseDate = date('Y-m-d');
		} else {
			$baseDate = $currentExpirationDate;
		}
		
		switch ($renewalInterval) {
			case 'monthly':
				return date('Y-m-d', strtotime($baseDate . ' +1 month'));
			case 'quarterly':
				return date('Y-m-d', strtotime($baseDate . ' +3 months'));
			case 'yearly':
				return date('Y-m-d', strtotime($baseDate . ' +1 year'));
			default:
				return null;
		}
	}
}


