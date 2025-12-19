<?php
declare(strict_types=1);

namespace OCA\DomainControl\Controller;

use OCA\DomainControl\AppInfo\Application;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;
use OCA\DomainControl\Db\InvoiceItemMapper;
use OCA\DomainControl\Db\InvoiceMapper;
use OCA\DomainControl\Db\InvoiceItem;

class InvoiceItemController extends Controller {
	private $userId;
	private InvoiceItemMapper $mapper;
	private InvoiceMapper $invoiceMapper;

	public function __construct(IRequest $request,
	                            InvoiceItemMapper $mapper,
	                            InvoiceMapper $invoiceMapper,
	                            $userId) {
		parent::__construct(Application::APP_ID, $request);
		$this->mapper = $mapper;
		$this->invoiceMapper = $invoiceMapper;
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
			// Get all invoice items - for now return empty, use byInvoice instead
			return new JSONResponse([]);
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
			// Verify invoice belongs to user
			$this->invoiceMapper->find($invoiceId, $this->userId);
			$items = $this->mapper->findByInvoice($invoiceId);
			return new JSONResponse($items);
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
			
			$invoiceId = (int)($data['invoiceId'] ?? 0);
			if ($invoiceId === 0) {
				return new JSONResponse(['error' => 'Invoice ID is required'], 400);
			}
			
			// Verify invoice belongs to user
			$invoice = $this->invoiceMapper->find($invoiceId, $this->userId);
			
			$item = new InvoiceItem();
			$item->setInvoiceId($invoiceId);
			$item->setDescription($data['description'] ?? '');
			$item->setItemType($data['itemType'] ?? 'manual');
			$item->setItemId((int)($data['itemId'] ?? 0));
			$item->setQuantity((int)($data['quantity'] ?? 1));
			$item->setUnitPrice((float)($data['unitPrice'] ?? 0));
			$item->setCurrency($data['currency'] ?? $invoice->getCurrency());
			$item->setPeriodStart($data['periodStart'] ?? '');
			$item->setPeriodEnd($data['periodEnd'] ?? '');
			$item->setDiscount((float)($data['discount'] ?? 0));
			$item->setDiscountType($data['discountType'] ?? 'fixed');
			
			// Calculate total price
			$quantity = $item->getQuantity();
			$unitPrice = $item->getUnitPrice();
			$total = $quantity * $unitPrice;
			
			// Apply discount
			if ($item->getDiscount() > 0) {
				if ($item->getDiscountType() === 'percent') {
					$total = $total - ($total * ($item->getDiscount() / 100));
				} else {
					$total = $total - $item->getDiscount();
				}
			}
			$item->setTotalPrice(max(0, $total));
			
			$now = date('Y-m-d H:i:s');
			$item->setCreatedAt($now);
			
			$item = $this->mapper->insert($item);
			
			// Recalculate invoice total
			$this->recalculateInvoiceTotal($invoiceId);
			
			return new JSONResponse($item);
		} catch (\Exception $e) {
			return new JSONResponse(['error' => $e->getMessage()], 500);
		}
	}

	/**
	 * @NoAdminRequired
	 */
	public function update(int $id): JSONResponse {
		try {
			$item = $this->mapper->find($id);
			
			// Verify invoice belongs to user
			$this->invoiceMapper->find($item->getInvoiceId(), $this->userId);
			
			$data = $this->getRequestData();
			
			if (isset($data['description'])) $item->setDescription($data['description']);
			if (isset($data['itemType'])) $item->setItemType($data['itemType']);
			if (isset($data['itemId'])) $item->setItemId((int)$data['itemId']);
			if (isset($data['quantity'])) $item->setQuantity((int)$data['quantity']);
			if (isset($data['unitPrice'])) $item->setUnitPrice((float)$data['unitPrice']);
			if (isset($data['currency'])) $item->setCurrency($data['currency']);
			if (isset($data['periodStart'])) $item->setPeriodStart($data['periodStart']);
			if (isset($data['periodEnd'])) $item->setPeriodEnd($data['periodEnd']);
			if (isset($data['discount'])) $item->setDiscount((float)$data['discount']);
			if (isset($data['discountType'])) $item->setDiscountType($data['discountType']);
			
			// Recalculate total price
			$quantity = $item->getQuantity();
			$unitPrice = $item->getUnitPrice();
			$total = $quantity * $unitPrice;
			
			if ($item->getDiscount() > 0) {
				if ($item->getDiscountType() === 'percent') {
					$total = $total - ($total * ($item->getDiscount() / 100));
				} else {
					$total = $total - $item->getDiscount();
				}
			}
			$item->setTotalPrice(max(0, $total));
			
			$item = $this->mapper->update($item);
			
			// Recalculate invoice total
			$this->recalculateInvoiceTotal($item->getInvoiceId());
			
			return new JSONResponse($item);
		} catch (\Exception $e) {
			return new JSONResponse(['error' => $e->getMessage()], 500);
		}
	}

	/**
	 * @NoAdminRequired
	 */
	public function delete(int $id): JSONResponse {
		try {
			$item = $this->mapper->find($id);
			$invoiceId = $item->getInvoiceId();
			
			// Verify invoice belongs to user
			$this->invoiceMapper->find($invoiceId, $this->userId);
			
			$this->mapper->delete($item);
			
			// Recalculate invoice total
			$this->recalculateInvoiceTotal($invoiceId);
			
			return new JSONResponse(['success' => true]);
		} catch (\Exception $e) {
			return new JSONResponse(['error' => $e->getMessage()], 500);
		}
	}

	private function recalculateInvoiceTotal(int $invoiceId): void {
		try {
			$items = $this->mapper->findByInvoice($invoiceId);
			$total = 0;
			foreach ($items as $item) {
				$total += $item->getTotalPrice();
			}
			
			$invoice = $this->invoiceMapper->find($invoiceId, $this->userId);
			$invoice->setTotalAmount($total);
			$invoice->setUpdatedAt(date('Y-m-d H:i:s'));
			$this->invoiceMapper->update($invoice);
		} catch (\Exception $e) {
			// Silently fail
		}
	}
}


