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
use OCA\DomainControl\Db\TransactionMapper;
use OCA\DomainControl\Db\Payment;
use OCA\DomainControl\Db\Transaction;

class PaymentController extends Controller {
	private $userId;
	private PaymentMapper $mapper;
	private InvoiceMapper $invoiceMapper;
	private InvoiceItemMapper $invoiceItemMapper;
	private DomainMapper $domainMapper;
	private HostingMapper $hostingMapper;
	private ServiceMapper $serviceMapper;
	private TransactionMapper $transactionMapper;

	public function __construct(IRequest $request,
	                            PaymentMapper $mapper,
	                            InvoiceMapper $invoiceMapper,
	                            InvoiceItemMapper $invoiceItemMapper,
	                            DomainMapper $domainMapper,
	                            HostingMapper $hostingMapper,
	                            ServiceMapper $serviceMapper,
	                            TransactionMapper $transactionMapper,
	                            $userId) {
		parent::__construct(Application::APP_ID, $request);
		$this->mapper = $mapper;
		$this->invoiceMapper = $invoiceMapper;
		$this->invoiceItemMapper = $invoiceItemMapper;
		$this->domainMapper = $domainMapper;
		$this->hostingMapper = $hostingMapper;
		$this->serviceMapper = $serviceMapper;
		$this->transactionMapper = $transactionMapper;
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
			// Get all income transactions (payments)
			$transactions = $this->transactionMapper->findByType('income', $this->userId);
			// Convert to payment-compatible format
			$payments = array_map(function($t) {
				return [
					'id' => $t->getId(),
					'invoiceId' => $t->getInvoiceId(),
					'clientId' => $t->getClientId(),
					'amount' => $t->getAmount(),
					'currency' => $t->getCurrency(),
					'paymentDate' => $t->getTransactionDate(),
					'paymentMethod' => $t->getPaymentMethod(),
					'reference' => $t->getReference(),
					'notes' => $t->getNotes(),
					'userId' => $t->getUserId(),
					'createdAt' => $t->getCreatedAt(),
					'updatedAt' => $t->getUpdatedAt(),
				];
			}, $transactions);
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
			$transaction = $this->transactionMapper->find($id, $this->userId);
			if ($transaction->getType() !== 'income') {
				return new JSONResponse(['error' => 'Payment not found'], 404);
			}
			// Convert to payment-compatible format
			return new JSONResponse([
				'id' => $transaction->getId(),
				'invoiceId' => $transaction->getInvoiceId(),
				'clientId' => $transaction->getClientId(),
				'amount' => $transaction->getAmount(),
				'currency' => $transaction->getCurrency(),
				'paymentDate' => $transaction->getTransactionDate(),
				'paymentMethod' => $transaction->getPaymentMethod(),
				'reference' => $transaction->getReference(),
				'notes' => $transaction->getNotes(),
				'userId' => $transaction->getUserId(),
				'createdAt' => $transaction->getCreatedAt(),
				'updatedAt' => $transaction->getUpdatedAt(),
			]);
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
			// Get all transactions for client, filter income type
			$transactions = $this->transactionMapper->findByClient($clientId, $this->userId);
			$incomeTransactions = array_filter($transactions, function($t) {
				return $t->getType() === 'income';
			});
			// Convert to payment-compatible format
			$payments = array_map(function($t) {
				return [
					'id' => $t->getId(),
					'invoiceId' => $t->getInvoiceId(),
					'clientId' => $t->getClientId(),
					'amount' => $t->getAmount(),
					'currency' => $t->getCurrency(),
					'paymentDate' => $t->getTransactionDate(),
					'paymentMethod' => $t->getPaymentMethod(),
					'reference' => $t->getReference(),
					'notes' => $t->getNotes(),
					'userId' => $t->getUserId(),
					'createdAt' => $t->getCreatedAt(),
					'updatedAt' => $t->getUpdatedAt(),
				];
			}, $incomeTransactions);
			return new JSONResponse(array_values($payments));
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
			// Get all transactions for invoice (should all be income type)
			$transactions = $this->transactionMapper->findByInvoice($invoiceId, $this->userId);
			// Filter only income type transactions (payments)
			$incomeTransactions = array_filter($transactions, function($t) {
				return $t->getType() === 'income';
			});
			// Convert to payment-compatible format
			$payments = array_map(function($t) {
				return [
					'id' => $t->getId(),
					'invoiceId' => $t->getInvoiceId(),
					'clientId' => $t->getClientId(),
					'amount' => $t->getAmount(),
					'currency' => $t->getCurrency(),
					'paymentDate' => $t->getTransactionDate(),
					'paymentMethod' => $t->getPaymentMethod(),
					'reference' => $t->getReference(),
					'notes' => $t->getNotes(),
					'userId' => $t->getUserId(),
					'createdAt' => $t->getCreatedAt(),
					'updatedAt' => $t->getUpdatedAt(),
				];
			}, $incomeTransactions);
			return new JSONResponse(array_values($payments));
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
			// Calculate total income transactions for current month
			$now = new \DateTime();
			$yearMonth = $now->format('Y-m');
			$summary = $this->transactionMapper->getMonthlySummary($yearMonth, $this->userId);
			return new JSONResponse(['total' => $summary['totalIncome'] ?? 0]);
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
			
			$invoiceId = (int)($data['invoiceId'] ?? 0) ?: null;
			$clientId = (int)($data['clientId'] ?? 0);
			$projectId = null;
			
			// Get invoice and project info if linked
			if ($invoiceId) {
				try {
					$invoice = $this->invoiceMapper->find($invoiceId, $this->userId);
					// Check invoice items for project link
					$items = $this->invoiceItemMapper->findByInvoice($invoiceId);
					foreach ($items as $item) {
						if ($item->getItemType() === 'project') {
							$projectId = $item->getItemId();
							break;
						}
					}
				} catch (\Exception $e) {
					// Invoice not found or error, continue without projectId
				}
			}
			
			// Create transaction directly (no payment entity)
			$transaction = new Transaction();
			$transaction->setType('income');
			$transaction->setInvoiceId($invoiceId);
			$transaction->setClientId($clientId);
			$transaction->setProjectId($projectId);
			$transaction->setAmount($amount);
			$transaction->setCurrency($data['currency'] ?? 'USD');
			$transaction->setTransactionDate($data['paymentDate'] ?? date('Y-m-d'));
			$transaction->setPaymentMethod($data['paymentMethod'] ?? '');
			$transaction->setReference($data['reference'] ?? '');
			
			// Build description
			if ($invoiceId) {
				$description = 'Fatura Ödemesi';
				if (!empty($data['reference'])) {
					$description .= ' - ' . $data['reference'];
				}
				$transaction->setDescription($description);
				
				// Build notes with invoice ID
				$notes = 'Fatura ödemesi';
				if (!empty($data['notes'])) {
					$notes .= ': ' . $data['notes'];
				}
				$notes .= ' [INVOICE_ID:' . $invoiceId . ']';
				$transaction->setNotes($notes);
			} else {
				$transaction->setDescription($data['notes'] ?? 'Ödeme');
				$transaction->setNotes($data['notes'] ?? '');
			}
			
			$transaction->setUserId($this->userId);
			$now = date('Y-m-d H:i:s');
			$transaction->setCreatedAt($now);
			$transaction->setUpdatedAt($now);
			$transaction = $this->transactionMapper->insert($transaction);
			
			// Update invoice if linked
			if ($invoiceId) {
				$this->updateInvoicePayment($invoiceId);
				
				// Extend services if invoice is fully paid
				$invoice = $this->invoiceMapper->find($invoiceId, $this->userId);
				if ($invoice->getStatus() === 'paid') {
					$this->extendLinkedServices($invoiceId);
				}
			}
			
			// Return transaction in payment-compatible format for backward compatibility
			return new JSONResponse([
				'id' => $transaction->getId(),
				'invoiceId' => $transaction->getInvoiceId(),
				'clientId' => $transaction->getClientId(),
				'amount' => $transaction->getAmount(),
				'currency' => $transaction->getCurrency(),
				'paymentDate' => $transaction->getTransactionDate(),
				'paymentMethod' => $transaction->getPaymentMethod(),
				'reference' => $transaction->getReference(),
				'notes' => $transaction->getNotes(),
				'userId' => $transaction->getUserId(),
				'createdAt' => $transaction->getCreatedAt(),
				'updatedAt' => $transaction->getUpdatedAt(),
			]);
		} catch (\Exception $e) {
			return new JSONResponse(['error' => $e->getMessage()], 500);
		}
	}

	/**
	 * @NoAdminRequired
	 */
	public function update(int $id): JSONResponse {
		try {
			// Find transaction by invoice_id (for backward compatibility, try to find by payment id)
			// First try to find as transaction with invoice_id matching payment pattern
			$transaction = null;
			try {
				$transaction = $this->transactionMapper->find($id, $this->userId);
				// Check if it's an income transaction (payment)
				if ($transaction->getType() !== 'income') {
					throw new \Exception('Transaction is not an income type');
				}
			} catch (\Exception $e) {
				// Try to find by invoice_id pattern in notes
				$allTransactions = $this->transactionMapper->findByType('income', $this->userId);
				foreach ($allTransactions as $t) {
					if ($t->getNotes() && strpos($t->getNotes(), '[INVOICE_ID:') !== false) {
						// This might be the payment, but we can't match by ID
						// For now, return error - update should use transaction endpoint
						throw new \Exception('Payment update should use transaction endpoint');
					}
				}
				throw new \Exception('Payment not found');
			}
			
			$oldInvoiceId = $transaction->getInvoiceId();
			$data = $this->getRequestData();
			
			if (isset($data['invoiceId'])) $transaction->setInvoiceId((int)$data['invoiceId'] ?: null);
			if (isset($data['clientId'])) $transaction->setClientId((int)$data['clientId']);
			if (isset($data['amount'])) $transaction->setAmount((float)$data['amount']);
			if (isset($data['currency'])) $transaction->setCurrency($data['currency']);
			if (isset($data['paymentDate'])) $transaction->setTransactionDate($data['paymentDate']);
			if (isset($data['paymentMethod'])) $transaction->setPaymentMethod($data['paymentMethod']);
			if (isset($data['reference'])) $transaction->setReference($data['reference']);
			if (isset($data['notes'])) {
				// Update notes, preserve [INVOICE_ID:] if invoice exists
				$invoiceId = $transaction->getInvoiceId();
				if ($invoiceId) {
					$notes = $data['notes'];
					if (strpos($notes, '[INVOICE_ID:') === false) {
						$notes .= ' [INVOICE_ID:' . $invoiceId . ']';
					}
					$transaction->setNotes($notes);
				} else {
					$transaction->setNotes($data['notes']);
				}
			}
			
			$transaction->setUpdatedAt(date('Y-m-d H:i:s'));
			
			$transaction = $this->transactionMapper->update($transaction);
			
			// Update old invoice if changed
			if ($oldInvoiceId && $oldInvoiceId !== $transaction->getInvoiceId()) {
				$this->updateInvoicePayment($oldInvoiceId);
			}
			
			// Update new invoice
			if ($transaction->getInvoiceId()) {
				$this->updateInvoicePayment($transaction->getInvoiceId());
			}
			
			// Return in payment-compatible format
			return new JSONResponse([
				'id' => $transaction->getId(),
				'invoiceId' => $transaction->getInvoiceId(),
				'clientId' => $transaction->getClientId(),
				'amount' => $transaction->getAmount(),
				'currency' => $transaction->getCurrency(),
				'paymentDate' => $transaction->getTransactionDate(),
				'paymentMethod' => $transaction->getPaymentMethod(),
				'reference' => $transaction->getReference(),
				'notes' => $transaction->getNotes(),
				'userId' => $transaction->getUserId(),
				'createdAt' => $transaction->getCreatedAt(),
				'updatedAt' => $transaction->getUpdatedAt(),
			]);
		} catch (\Exception $e) {
			return new JSONResponse(['error' => $e->getMessage()], 500);
		}
	}

	/**
	 * @NoAdminRequired
	 */
	public function delete(int $id): JSONResponse {
		try {
			// Find transaction (payment)
			$transaction = $this->transactionMapper->find($id, $this->userId);
			
			// Check if it's an income transaction (payment)
			if ($transaction->getType() !== 'income') {
				return new JSONResponse(['error' => 'Transaction is not a payment'], 400);
			}
			
			$invoiceId = $transaction->getInvoiceId();
			
			$this->transactionMapper->delete($transaction);
			
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
		// Get all transactions (income type) for this invoice
		$transactions = $this->transactionMapper->findByInvoice($invoiceId, $this->userId);
		$totalPaid = 0;
		foreach ($transactions as $t) {
			if ($t->getType() === 'income') {
				$totalPaid += $t->getAmount();
			}
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


