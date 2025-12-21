<?php
declare(strict_types=1);

namespace OCA\DomainControl\Controller;

use OCA\DomainControl\AppInfo\Application;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;
use OCA\DomainControl\Db\InvoiceMapper;
use OCA\DomainControl\Db\InvoiceItemMapper;
use OCA\DomainControl\Db\Invoice;
use OCA\DomainControl\Db\InvoiceItem;

class InvoiceController extends Controller
{
	private $userId;
	private InvoiceMapper $mapper;
	private InvoiceItemMapper $itemMapper;

	public function __construct(
		IRequest $request,
		InvoiceMapper $mapper,
		InvoiceItemMapper $itemMapper,
		$userId
	) {
		parent::__construct(Application::APP_ID, $request);
		$this->mapper = $mapper;
		$this->itemMapper = $itemMapper;
		$this->userId = $userId;
	}

	private function getRequestData(): array
	{
		$body = file_get_contents('php://input');
		parse_str($body, $data);
		return $data;
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function index(): JSONResponse
	{
		try {
			$invoices = $this->mapper->findAll($this->userId);
			return new JSONResponse($invoices);
		} catch (\Exception $e) {
			return new JSONResponse(['error' => $e->getMessage()], 500);
		}
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function show(int $id): JSONResponse
	{
		try {
			$invoice = $this->mapper->find($id, $this->userId);
			$items = $this->itemMapper->findByInvoice($id);
			$result = $invoice->jsonSerialize();
			$result['items'] = $items;
			return new JSONResponse($result);
		} catch (\Exception $e) {
			return new JSONResponse(['error' => 'Invoice not found'], 404);
		}
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function byClient(int $clientId): JSONResponse
	{
		try {
			$invoices = $this->mapper->findByClient($clientId, $this->userId);
			return new JSONResponse($invoices);
		} catch (\Exception $e) {
			return new JSONResponse(['error' => $e->getMessage()], 500);
		}
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function overdue(): JSONResponse
	{
		try {
			$invoices = $this->mapper->findOverdue($this->userId);
			return new JSONResponse($invoices);
		} catch (\Exception $e) {
			return new JSONResponse(['error' => $e->getMessage()], 500);
		}
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function upcoming(): JSONResponse
	{
		try {
			$invoices = $this->mapper->findUpcomingDue($this->userId, 30);
			return new JSONResponse($invoices);
		} catch (\Exception $e) {
			return new JSONResponse(['error' => $e->getMessage()], 500);
		}
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function unpaid(): JSONResponse
	{
		try {
			$invoices = $this->mapper->findUnpaid($this->userId);
			return new JSONResponse($invoices);
		} catch (\Exception $e) {
			return new JSONResponse(['error' => $e->getMessage()], 500);
		}
	}

	/**
	 * @NoAdminRequired
	 */
	public function create(): JSONResponse
	{
		try {
			$data = $this->getRequestData();

			$clientId = (int) ($data['clientId'] ?? 0);
			if ($clientId === 0) {
				return new JSONResponse(['error' => 'Client is required'], 400);
			}

			$invoice = new Invoice();
			$invoice->setClientId($clientId);
			// Handle invoice number: generate if empty OR if it matches userId (browser autofill protection)
			$inputInvoiceNum = $data['invoiceNumber'] ?? '';
			$invoiceNumber = ($inputInvoiceNum === '' || $inputInvoiceNum === $this->userId)
				? $this->mapper->generateInvoiceNumber($this->userId)
				: $inputInvoiceNum;
			$invoice->setInvoiceNumber($invoiceNumber);
			$invoice->setIssueDate($data['issueDate'] ?? date('Y-m-d'));
			$invoice->setDueDate($data['dueDate'] ?? date('Y-m-d', strtotime('+30 days')));
			$invoice->setTotalAmount((float) ($data['totalAmount'] ?? 0));
			$invoice->setPaidAmount(0);
			$invoice->setCurrency($data['currency'] ?? 'USD');
			$invoice->setStatus($data['status'] ?? 'draft');
			$invoice->setNotes($data['notes'] ?? '');
			$invoice->setUserId($this->userId);
			$now = date('Y-m-d H:i:s');
			$invoice->setCreatedAt($now);
			$invoice->setUpdatedAt($now);

			$invoice = $this->mapper->insert($invoice);

			// Add invoice items if provided
			if (!empty($data['items'])) {
				$items = json_decode($data['items'], true);
				if (is_array($items)) {
					foreach ($items as $itemData) {
						$item = new InvoiceItem();
						$item->setInvoiceId($invoice->getId());
						$item->setItemType($itemData['itemType'] ?? 'service');
						$item->setItemId((int) ($itemData['itemId'] ?? 0));
						$item->setDescription($itemData['description'] ?? '');
						$item->setQuantity((int) ($itemData['quantity'] ?? 1));
						$item->setUnitPrice((float) ($itemData['unitPrice'] ?? 0));
						$item->setTotalPrice((float) ($itemData['totalPrice'] ?? 0));
						$item->setPeriodStart($itemData['periodStart'] ?? '');
						$item->setPeriodEnd($itemData['periodEnd'] ?? '');
						$item->setCreatedAt($now);
						$this->itemMapper->insert($item);
					}
				}
			}

			return new JSONResponse($invoice);
		} catch (\Exception $e) {
			return new JSONResponse(['error' => $e->getMessage()], 500);
		}
	}

	/**
	 * @NoAdminRequired
	 */
	public function update(int $id): JSONResponse
	{
		try {
			$invoice = $this->mapper->find($id, $this->userId);
			$data = $this->getRequestData();

			if (isset($data['clientId']))
				$invoice->setClientId((int) $data['clientId']);
			if (isset($data['invoiceNumber']))
				$invoice->setInvoiceNumber($data['invoiceNumber']);
			if (isset($data['issueDate']))
				$invoice->setIssueDate($data['issueDate']);
			if (isset($data['dueDate']))
				$invoice->setDueDate($data['dueDate']);
			if (isset($data['totalAmount']))
				$invoice->setTotalAmount((float) $data['totalAmount']);
			if (isset($data['currency']))
				$invoice->setCurrency($data['currency']);
			if (isset($data['status']))
				$invoice->setStatus($data['status']);
			if (isset($data['notes']))
				$invoice->setNotes($data['notes']);

			$invoice->setUpdatedAt(date('Y-m-d H:i:s'));

			$invoice = $this->mapper->update($invoice);
			return new JSONResponse($invoice);
		} catch (\Exception $e) {
			return new JSONResponse(['error' => $e->getMessage()], 500);
		}
	}

	/**
	 * @NoAdminRequired
	 */
	public function delete(int $id): JSONResponse
	{
		try {
			$invoice = $this->mapper->find($id, $this->userId);
			// Delete items first
			$this->itemMapper->deleteByInvoice($id);
			$this->mapper->delete($invoice);
			return new JSONResponse(['success' => true]);
		} catch (\Exception $e) {
			return new JSONResponse(['error' => $e->getMessage()], 500);
		}
	}

	/**
	 * @NoAdminRequired
	 */
	public function addItem(int $id): JSONResponse
	{
		try {
			$invoice = $this->mapper->find($id, $this->userId);
			$data = $this->getRequestData();

			$item = new InvoiceItem();
			$item->setInvoiceId($id);
			$item->setItemType($data['itemType'] ?? 'service');
			$item->setItemId((int) ($data['itemId'] ?? 0));
			$item->setDescription($data['description'] ?? '');
			$item->setQuantity((int) ($data['quantity'] ?? 1));
			$item->setUnitPrice((float) ($data['unitPrice'] ?? 0));
			$item->setTotalPrice((float) ($data['totalPrice'] ?? 0));
			$item->setPeriodStart($data['periodStart'] ?? '');
			$item->setPeriodEnd($data['periodEnd'] ?? '');
			$item->setCreatedAt(date('Y-m-d H:i:s'));

			$item = $this->itemMapper->insert($item);

			// Update invoice total
			$this->recalculateTotal($id);

			return new JSONResponse($item);
		} catch (\Exception $e) {
			return new JSONResponse(['error' => $e->getMessage()], 500);
		}
	}

	/**
	 * @NoAdminRequired
	 */
	public function removeItem(int $id, int $itemId): JSONResponse
	{
		try {
			$invoice = $this->mapper->find($id, $this->userId);
			$item = $this->itemMapper->find($itemId);

			if ($item->getInvoiceId() !== $id) {
				return new JSONResponse(['error' => 'Item does not belong to this invoice'], 400);
			}

			$this->itemMapper->delete($item);

			// Update invoice total
			$this->recalculateTotal($id);

			return new JSONResponse(['success' => true]);
		} catch (\Exception $e) {
			return new JSONResponse(['error' => $e->getMessage()], 500);
		}
	}

	private function recalculateTotal(int $invoiceId): void
	{
		$items = $this->itemMapper->findByInvoice($invoiceId);
		$total = 0;
		foreach ($items as $item) {
			$total += $item->getTotalPrice();
		}

		$invoice = $this->mapper->find($invoiceId, $this->userId);
		$invoice->setTotalAmount($total);
		$invoice->setUpdatedAt(date('Y-m-d H:i:s'));
		$this->mapper->update($invoice);
	}
}


