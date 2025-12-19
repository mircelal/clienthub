<?php
declare(strict_types=1);

namespace OCA\DomainControl\Controller;

use OCA\DomainControl\AppInfo\Application;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;
use OCA\DomainControl\Db\PaymentMapper;
use OCA\DomainControl\Db\InvoiceMapper;
use OCA\DomainControl\Db\InvoiceItemMapper;
use OCA\DomainControl\Db\DomainMapper;
use OCA\DomainControl\Db\HostingMapper;
use OCA\DomainControl\Db\ServiceMapper;
use OCA\DomainControl\Db\Payment;

class PaymentController extends Controller {
	private $userId;
	private PaymentMapper $mapper;
	private InvoiceMapper $invoiceMapper;
	private InvoiceItemMapper $invoiceItemMapper;
	private DomainMapper $domainMapper;
	private HostingMapper $hostingMapper;
	private ServiceMapper $serviceMapper;

	public function __construct(IRequest $request,
	                            PaymentMapper $mapper,
	                            InvoiceMapper $invoiceMapper,
	                            InvoiceItemMapper $invoiceItemMapper,
	                            DomainMapper $domainMapper,
	                            HostingMapper $hostingMapper,
	                            ServiceMapper $serviceMapper,
	                            $userId) {
		parent::__construct(Application::APP_ID, $request);
		$this->mapper = $mapper;
		$this->invoiceMapper = $invoiceMapper;
		$this->invoiceItemMapper = $invoiceItemMapper;
		$this->domainMapper = $domainMapper;
		$this->hostingMapper = $hostingMapper;
		$this->serviceMapper = $serviceMapper;
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
	public function index(): JSONResponse {
		try {
			$payments = $this->mapper->findAll($this->userId);
			return new JSONResponse($payments);
		} catch (\Exception $e) {
			return new JSONResponse(['error' => $e->getMessage()], 500);
		}
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function show(int $id): JSONResponse {
		try {
			$payment = $this->mapper->find($id, $this->userId);
			return new JSONResponse($payment);
		} catch (\Exception $e) {
			return new JSONResponse(['error' => 'Payment not found'], 404);
		}
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function byClient(int $clientId): JSONResponse {
		try {
			$payments = $this->mapper->findByClient($clientId, $this->userId);
			return new JSONResponse($payments);
		} catch (\Exception $e) {
			return new JSONResponse(['error' => $e->getMessage()], 500);
		}
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function byInvoice(int $invoiceId): JSONResponse {
		try {
			$payments = $this->mapper->findByInvoice($invoiceId, $this->userId);
			return new JSONResponse($payments);
		} catch (\Exception $e) {
			return new JSONResponse(['error' => $e->getMessage()], 500);
		}
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function monthlyTotal(): JSONResponse {
		try {
			$total = $this->mapper->getTotalThisMonth($this->userId);
			return new JSONResponse(['total' => $total]);
		} catch (\Exception $e) {
			return new JSONResponse(['error' => $e->getMessage()], 500);
		}
	}

	/**
	 * @NoAdminRequired
	 */
	public function create(): JSONResponse {
		try {
			$data = $this->getRequestData();
			
			$amount = (float)($data['amount'] ?? 0);
			if ($amount <= 0) {
				return new JSONResponse(['error' => 'Amount is required'], 400);
			}
			
			$payment = new Payment();
			$payment->setInvoiceId((int)($data['invoiceId'] ?? 0) ?: null);
			$payment->setClientId((int)($data['clientId'] ?? 0));
			$payment->setAmount($amount);
			$payment->setCurrency($data['currency'] ?? 'USD');
			$payment->setPaymentDate($data['paymentDate'] ?? date('Y-m-d'));
			$payment->setPaymentMethod($data['paymentMethod'] ?? '');
			$payment->setReference($data['reference'] ?? '');
			$payment->setNotes($data['notes'] ?? '');
			$payment->setUserId($this->userId);
			$now = date('Y-m-d H:i:s');
			$payment->setCreatedAt($now);
			$payment->setUpdatedAt($now);
			
			$payment = $this->mapper->insert($payment);
			
			// Update invoice if linked
			$invoiceId = $payment->getInvoiceId();
			if ($invoiceId) {
				$this->updateInvoicePayment($invoiceId);
				
				// Extend services if invoice is fully paid
				$invoice = $this->invoiceMapper->find($invoiceId, $this->userId);
				if ($invoice->getStatus() === 'paid') {
					$this->extendLinkedServices($invoiceId);
				}
			}
			
			return new JSONResponse($payment);
		} catch (\Exception $e) {
			return new JSONResponse(['error' => $e->getMessage()], 500);
		}
	}

	/**
	 * @NoAdminRequired
	 */
	public function update(int $id): JSONResponse {
		try {
			$payment = $this->mapper->find($id, $this->userId);
			$oldInvoiceId = $payment->getInvoiceId();
			$data = $this->getRequestData();
			
			if (isset($data['invoiceId'])) $payment->setInvoiceId((int)$data['invoiceId'] ?: null);
			if (isset($data['clientId'])) $payment->setClientId((int)$data['clientId']);
			if (isset($data['amount'])) $payment->setAmount((float)$data['amount']);
			if (isset($data['currency'])) $payment->setCurrency($data['currency']);
			if (isset($data['paymentDate'])) $payment->setPaymentDate($data['paymentDate']);
			if (isset($data['paymentMethod'])) $payment->setPaymentMethod($data['paymentMethod']);
			if (isset($data['reference'])) $payment->setReference($data['reference']);
			if (isset($data['notes'])) $payment->setNotes($data['notes']);
			
			$payment->setUpdatedAt(date('Y-m-d H:i:s'));
			
			$payment = $this->mapper->update($payment);
			
			// Update old invoice if changed
			if ($oldInvoiceId && $oldInvoiceId !== $payment->getInvoiceId()) {
				$this->updateInvoicePayment($oldInvoiceId);
			}
			
			// Update new invoice
			if ($payment->getInvoiceId()) {
				$this->updateInvoicePayment($payment->getInvoiceId());
			}
			
			return new JSONResponse($payment);
		} catch (\Exception $e) {
			return new JSONResponse(['error' => $e->getMessage()], 500);
		}
	}

	/**
	 * @NoAdminRequired
	 */
	public function delete(int $id): JSONResponse {
		try {
			$payment = $this->mapper->find($id, $this->userId);
			$invoiceId = $payment->getInvoiceId();
			
			$this->mapper->delete($payment);
			
			// Update invoice if linked
			if ($invoiceId) {
				$this->updateInvoicePayment($invoiceId);
			}
			
			return new JSONResponse(['success' => true]);
		} catch (\Exception $e) {
			return new JSONResponse(['error' => $e->getMessage()], 500);
		}
	}

	private function updateInvoicePayment(int $invoiceId): void {
		$payments = $this->mapper->findByInvoice($invoiceId, $this->userId);
		$totalPaid = 0;
		foreach ($payments as $p) {
			$totalPaid += $p->getAmount();
		}
		
		$invoice = $this->invoiceMapper->find($invoiceId, $this->userId);
		$invoice->setPaidAmount($totalPaid);
		
		// Update status based on payment
		if ($totalPaid >= $invoice->getTotalAmount()) {
			$invoice->setStatus('paid');
		} elseif ($totalPaid > 0) {
			$invoice->setStatus('sent'); // Partial payment
		}
		
		$invoice->setUpdatedAt(date('Y-m-d H:i:s'));
		$this->invoiceMapper->update($invoice);
	}

	private function extendLinkedServices(int $invoiceId): void {
		$items = $this->invoiceItemMapper->findByInvoice($invoiceId);
		
		foreach ($items as $item) {
			$type = $item->getItemType();
			$itemId = $item->getItemId();
			$periodEnd = $item->getPeriodEnd();
			
			if (empty($periodEnd) || empty($itemId)) continue;
			
			try {
				switch ($type) {
					case 'domain':
						$domain = $this->domainMapper->find($itemId, $this->userId);
						$domain->setExpirationDate($periodEnd);
						$domain->setUpdatedAt(date('Y-m-d H:i:s'));
						$this->domainMapper->update($domain);
						break;
						
					case 'hosting':
						$hosting = $this->hostingMapper->find($itemId, $this->userId);
						$hosting->setExpirationDate($periodEnd);
						$hosting->setUpdatedAt(date('Y-m-d H:i:s'));
						$this->hostingMapper->update($hosting);
						break;
						
					case 'service':
						$service = $this->serviceMapper->find($itemId, $this->userId);
						$service->setExpirationDate($periodEnd);
						$service->setUpdatedAt(date('Y-m-d H:i:s'));
						$this->serviceMapper->update($service);
						break;
				}
			} catch (\Exception $e) {
				// Item may have been deleted, skip
				continue;
			}
		}
	}
}


