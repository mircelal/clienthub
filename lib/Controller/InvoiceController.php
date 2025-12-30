<?php
declare(strict_types=1);

namespace OCA\DomainControl\Controller;

use OCA\DomainControl\AppInfo\Application;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\JSONResponse;
use OCP\AppFramework\Http\DataDownloadResponse;
use OCP\IRequest;
use OCA\DomainControl\Db\InvoiceMapper;
use OCA\DomainControl\Db\InvoiceItemMapper;
use OCA\DomainControl\Db\ClientMapper;
use OCA\DomainControl\Db\PaymentMapper;
use OCA\DomainControl\Db\TransactionMapper;
use OCA\DomainControl\Db\SettingMapper;
use OCA\DomainControl\Db\Invoice;
use OCA\DomainControl\Db\InvoiceItem;
use TCPDF;
use OCP\IL10N;

class InvoiceController extends Controller
{
	private $userId;
	private InvoiceMapper $mapper;
	private InvoiceItemMapper $itemMapper;
	private ClientMapper $clientMapper;
	private PaymentMapper $paymentMapper;
	private TransactionMapper $transactionMapper;
	private SettingMapper $settingMapper;
	private IL10N $l10n;

	public function __construct(
		IRequest $request,
		InvoiceMapper $mapper,
		InvoiceItemMapper $itemMapper,
		ClientMapper $clientMapper,
		PaymentMapper $paymentMapper,
		TransactionMapper $transactionMapper,
		SettingMapper $settingMapper,
		IL10N $l10n,
		$userId
	) {
		parent::__construct(Application::APP_ID, $request);
		$this->mapper = $mapper;
		$this->itemMapper = $itemMapper;
		$this->clientMapper = $clientMapper;
		$this->paymentMapper = $paymentMapper;
		$this->transactionMapper = $transactionMapper;
		$this->settingMapper = $settingMapper;
		$this->l10n = $l10n;
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
			// Recalculate paid amount before showing to ensure data is fresh
			$this->updatePaidAmount($id);

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
			$invoice->setTitle($data['title'] ?? '');
			$invoice->setIssueDate($data['issueDate'] ?? date('Y-m-d'));
			$invoice->setDueDate($data['dueDate'] ?? date('Y-m-d', strtotime('+30 days')));
			$invoice->setTotalAmount((float) ($data['totalAmount'] ?? 0));
			$invoice->setPaidAmount(0);
			$invoice->setCurrency($data['currency'] ?? 'USD');
			$invoice->setProjectId(isset($data['projectId']) && $data['projectId'] ? (int) $data['projectId'] : null);
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
			if (isset($data['title']))
				$invoice->setTitle($data['title']);
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
			if (isset($data['projectId']))
				$invoice->setProjectId($data['projectId'] ? (int) $data['projectId'] : null);
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

			// Delete all payments (transactions) for this invoice first
			$transactions = $this->transactionMapper->findByInvoice($id, $this->userId);
			foreach ($transactions as $transaction) {
				// Only delete income transactions (payments)
				if ($transaction->getType() === 'income') {
					$this->transactionMapper->delete($transaction);
				}
			}

			// Delete items
			$this->itemMapper->deleteByInvoice($id);

			// Delete invoice
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

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function duplicate(int $id): JSONResponse
	{
		try {
			$originalInvoice = $this->mapper->find($id, $this->userId);

			// Create new invoice with same data
			$newInvoice = new Invoice();
			$newInvoice->setClientId($originalInvoice->getClientId());
			$newInvoice->setInvoiceNumber($this->mapper->generateInvoiceNumber($this->userId));
			$newInvoice->setTitle($originalInvoice->getTitle() ? $originalInvoice->getTitle() . ' (Copy)' : '');
			$newInvoice->setIssueDate(date('Y-m-d'));
			$newInvoice->setDueDate(date('Y-m-d', strtotime('+30 days')));
			$newInvoice->setTotalAmount($originalInvoice->getTotalAmount());
			$newInvoice->setPaidAmount(0);
			$newInvoice->setCurrency($originalInvoice->getCurrency());
			$newInvoice->setStatus('draft');
			$newInvoice->setNotes($originalInvoice->getNotes());
			$newInvoice->setUserId($this->userId);
			$now = date('Y-m-d H:i:s');
			$newInvoice->setCreatedAt($now);
			$newInvoice->setUpdatedAt($now);

			$newInvoice = $this->mapper->insert($newInvoice);

			// Duplicate invoice items
			$items = $this->itemMapper->findByInvoice($id);
			foreach ($items as $item) {
				$newItem = new InvoiceItem();
				$newItem->setInvoiceId($newInvoice->getId());
				$newItem->setItemType($item->getItemType());
				$newItem->setItemId($item->getItemId());
				$newItem->setDescription($item->getDescription());
				$newItem->setQuantity($item->getQuantity());
				$newItem->setUnitPrice($item->getUnitPrice());
				$newItem->setTotalPrice($item->getTotalPrice());
				$newItem->setPeriodStart($item->getPeriodStart());
				$newItem->setPeriodEnd($item->getPeriodEnd());
				$newItem->setCreatedAt($now);
				$this->itemMapper->insert($newItem);
			}

			return new JSONResponse($newInvoice);
		} catch (\Exception $e) {
			return new JSONResponse(['error' => $e->getMessage()], 500);
		}
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function downloadPdf(int $id): DataDownloadResponse
	{
		try {
			$invoice = $this->mapper->find($id, $this->userId);
			$items = $this->itemMapper->findByInvoice($id);
			$client = $invoice->getClientId() ? $this->clientMapper->find($invoice->getClientId(), $this->userId) : null;
			$payments = $this->paymentMapper->findByInvoice($id, $this->userId);

			$html = $this->generateInvoiceHtml($invoice, $items, $client, $payments);
			$pdf = $this->htmlToPdf($html);

			$filename = 'invoice-' . $invoice->getInvoiceNumber() . '.pdf';
			return new DataDownloadResponse($pdf, $filename, 'application/pdf');
		} catch (\Exception $e) {
			// Return error as JSON if PDF generation fails
			return new JSONResponse(['error' => $e->getMessage()], 500);
		}
	}

	private function generateInvoiceHtml(Invoice $invoice, array $items, $client, array $payments): string
	{
		// Get default currency from settings
		$defaultCurrency = $this->getDefaultCurrency();
		$currency = $invoice->getCurrency() ?: $defaultCurrency;
		$currencySymbol = $this->getCurrencySymbol($currency);
		$balance = max(0, $invoice->getTotalAmount() - $invoice->getPaidAmount());

		// Get translations
		$t = function ($text, $vars = []) {
			return $this->l10n->t($text, $vars);
		};

		// Ensure currency symbol is UTF-8 encoded
		$currencySymbol = mb_convert_encoding($currencySymbol, 'UTF-8', 'UTF-8');

		$html = '<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<meta charset="UTF-8">
	<title>' . htmlspecialchars($t('Invoice'), ENT_QUOTES, 'UTF-8') . ' ' . htmlspecialchars($invoice->getInvoiceNumber(), ENT_QUOTES, 'UTF-8') . '</title>
	<style>
		body { font-family: Arial, sans-serif; margin: 0; padding: 20px; color: #333; }
		.header { margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 20px; }
		.company-info { float: right; text-align: right; }
		.invoice-info { margin-top: 20px; }
		.client-info { margin: 30px 0; }
		.client-info h3 { margin-top: 0; }
		table { width: 100%; border-collapse: collapse; margin: 20px 0; }
		table th { background-color: #f5f5f5; padding: 12px; text-align: left; border-bottom: 2px solid #333; }
		table td { padding: 10px 12px; border-bottom: 1px solid #ddd; }
		table td.text-right { text-align: right; }
		table tfoot td { font-weight: bold; border-top: 2px solid #333; padding-top: 15px; }
		.summary { margin-top: 30px; }
		.summary-row { display: flex; justify-content: space-between; padding: 8px 0; }
		.summary-row.total { font-size: 18px; font-weight: bold; border-top: 2px solid #333; padding-top: 15px; margin-top: 10px; }
		.notes { margin-top: 30px; padding: 15px; background-color: #f9f9f9; border-left: 4px solid #333; }
		.payments-section { margin-top: 30px; }
		.payment-row { padding: 8px 0; border-bottom: 1px solid #eee; }
	</style>
</head>
<body>
	<div class="header">
		<div class="company-info">
			<h1>' . htmlspecialchars($t('Invoice'), ENT_QUOTES, 'UTF-8') . '</h1>
			<p><strong>' . htmlspecialchars($t('Invoice #'), ENT_QUOTES, 'UTF-8') . ':</strong> ' . htmlspecialchars($invoice->getInvoiceNumber(), ENT_QUOTES, 'UTF-8') . '</p>
			<p><strong>' . htmlspecialchars($t('Issue Date'), ENT_QUOTES, 'UTF-8') . ':</strong> ' . htmlspecialchars($invoice->getIssueDate(), ENT_QUOTES, 'UTF-8') . '</p>
			<p><strong>' . htmlspecialchars($t('Due Date'), ENT_QUOTES, 'UTF-8') . ':</strong> ' . htmlspecialchars($invoice->getDueDate(), ENT_QUOTES, 'UTF-8') . '</p>
			<p><strong>' . htmlspecialchars($t('Status'), ENT_QUOTES, 'UTF-8') . ':</strong> ' . htmlspecialchars($this->getStatusText($invoice->getStatus()), ENT_QUOTES, 'UTF-8') . '</p>
		</div>
		<div style="clear: both;"></div>
	</div>';

		if ($client) {
			$html .= '<div class="client-info">
		<h3>' . htmlspecialchars($t('Bill To'), ENT_QUOTES, 'UTF-8') . ':</h3>
		<p><strong>' . htmlspecialchars($client->getName(), ENT_QUOTES, 'UTF-8') . '</strong></p>';
			if ($client->getEmail()) {
				$html .= '<p>' . htmlspecialchars($t('Email'), ENT_QUOTES, 'UTF-8') . ': ' . htmlspecialchars($client->getEmail(), ENT_QUOTES, 'UTF-8') . '</p>';
			}
			if ($client->getPhone()) {
				$html .= '<p>' . htmlspecialchars($t('Phone'), ENT_QUOTES, 'UTF-8') . ': ' . htmlspecialchars($client->getPhone(), ENT_QUOTES, 'UTF-8') . '</p>';
			}
			$html .= '</div>';
		}

		if ($invoice->getTitle()) {
			$html .= '<div style="margin: 20px 0;"><h2>' . htmlspecialchars($invoice->getTitle(), ENT_QUOTES, 'UTF-8') . '</h2></div>';
		}

		$html .= '<table>
		<thead>
			<tr>
				<th>' . htmlspecialchars($t('Description'), ENT_QUOTES, 'UTF-8') . '</th>
				<th class="text-right">' . htmlspecialchars($t('Qty'), ENT_QUOTES, 'UTF-8') . '</th>
				<th class="text-right">' . htmlspecialchars($t('Unit Price'), ENT_QUOTES, 'UTF-8') . '</th>
				<th class="text-right">' . htmlspecialchars($t('Total'), ENT_QUOTES, 'UTF-8') . '</th>
			</tr>
		</thead>
		<tbody>';

		foreach ($items as $item) {
			$description = htmlspecialchars($item->getDescription() ?: '-', ENT_QUOTES, 'UTF-8');
			$html .= '<tr>
				<td>' . $description . '</td>
				<td class="text-right">' . htmlspecialchars((string) ($item->getQuantity() ?: 1), ENT_QUOTES, 'UTF-8') . '</td>
				<td class="text-right">' . htmlspecialchars($currencySymbol, ENT_QUOTES, 'UTF-8') . number_format($item->getUnitPrice() ?: 0, 2) . '</td>
				<td class="text-right">' . htmlspecialchars($currencySymbol, ENT_QUOTES, 'UTF-8') . number_format($item->getTotalPrice() ?: 0, 2) . '</td>
			</tr>';
		}

		$html .= '</tbody>
		<tfoot>
			<tr>
				<td colspan="3" class="text-right"><strong>' . htmlspecialchars($t('Total Amount'), ENT_QUOTES, 'UTF-8') . ':</strong></td>
				<td class="text-right"><strong>' . htmlspecialchars($currencySymbol, ENT_QUOTES, 'UTF-8') . number_format($invoice->getTotalAmount(), 2) . '</strong></td>
			</tr>';

		if ($invoice->getPaidAmount() > 0) {
			$html .= '<tr>
				<td colspan="3" class="text-right"><strong>' . htmlspecialchars($t('Paid Amount'), ENT_QUOTES, 'UTF-8') . ':</strong></td>
				<td class="text-right"><strong>' . htmlspecialchars($currencySymbol, ENT_QUOTES, 'UTF-8') . number_format($invoice->getPaidAmount(), 2) . '</strong></td>
			</tr>';
		}

		if ($balance > 0) {
			$html .= '<tr>
				<td colspan="3" class="text-right"><strong>' . htmlspecialchars($t('Balance Due'), ENT_QUOTES, 'UTF-8') . ':</strong></td>
				<td class="text-right"><strong>' . htmlspecialchars($currencySymbol, ENT_QUOTES, 'UTF-8') . number_format($balance, 2) . '</strong></td>
			</tr>';
		}

		$html .= '</tfoot>
	</table>';

		if (count($payments) > 0) {
			$html .= '<div class="payments-section">
		<h3>' . htmlspecialchars($t('Payments'), ENT_QUOTES, 'UTF-8') . '</h3>';
			foreach ($payments as $payment) {
				$html .= '<div class="payment-row">
			<span><strong>' . htmlspecialchars($payment->getPaymentDate(), ENT_QUOTES, 'UTF-8') . '</strong> - ' . htmlspecialchars($payment->getPaymentMethod() ?: $t('N/A'), ENT_QUOTES, 'UTF-8') . '</span>
			<span style="float: right;"><strong>' . htmlspecialchars($currencySymbol, ENT_QUOTES, 'UTF-8') . number_format($payment->getAmount(), 2) . '</strong></span>
		</div>';
			}
			$html .= '</div>';
		}

		if ($invoice->getNotes()) {
			$html .= '<div class="notes">
		<h4>' . htmlspecialchars($t('Notes'), ENT_QUOTES, 'UTF-8') . ':</h4>
		<p>' . nl2br(htmlspecialchars($invoice->getNotes(), ENT_QUOTES, 'UTF-8')) . '</p>
	</div>';
		}

		$html .= '</body>
</html>';

		return $html;
	}

	private function getDefaultCurrency(): string
	{
		try {
			$settings = $this->settingMapper->findAll($this->userId);
			foreach ($settings as $setting) {
				if ($setting->getSettingKey() === 'default_currency') {
					return $setting->getSettingValue() ?: 'USD';
				}
			}
		} catch (\Exception $e) {
			// Fallback to USD if settings not available
		}
		return 'USD';
	}

	private function getStatusText(string $status): string
	{
		$statusMap = [
			'draft' => $this->l10n->t('Draft'),
			'sent' => $this->l10n->t('Sent'),
			'paid' => $this->l10n->t('Paid'),
			'overdue' => $this->l10n->t('Overdue'),
		];
		return $statusMap[$status] ?? ucfirst($status);
	}

	private function getCurrencySymbol(string $currency): string
	{
		try {
			// Get currencies from settings
			$settings = $this->settingMapper->findAll($this->userId);
			foreach ($settings as $setting) {
				if ($setting->getSettingKey() === 'currencies') {
					$currenciesJson = $setting->getSettingValue();
					if ($currenciesJson) {
						$currencies = json_decode($currenciesJson, true);
						if (is_array($currencies)) {
							foreach ($currencies as $curr) {
								if (isset($curr['code']) && $curr['code'] === $currency && isset($curr['symbol'])) {
									return $curr['symbol'];
								}
							}
						}
					}
				}
			}
		} catch (\Exception $e) {
			// Fallback to default symbols
		}

		// Fallback to default symbols if settings not available
		$symbols = [
			'USD' => '$',
			'EUR' => '€',
			'TRY' => '₺',
			'AZN' => '₼',
			'GBP' => '£',
			'RUB' => '₽',
		];
		return $symbols[$currency] ?? $currency . ' ';
	}

	private function htmlToPdf(string $html): string
	{
		// Try to load TCPDF
		$vendorPath = __DIR__ . '/../../vendor/autoload.php';
		if (file_exists($vendorPath)) {
			require_once $vendorPath;
		}

		// Try TCPDF first (if available via composer)
		if (class_exists('TCPDF')) {
			return $this->generatePdfWithTcpdf($html);
		}

		// Try wkhtmltopdf (if available on server)
		$wkhtmltopdf = shell_exec('which wkhtmltopdf 2>/dev/null || where wkhtmltopdf 2>nul');
		if ($wkhtmltopdf) {
			$tempHtml = tempnam(sys_get_temp_dir(), 'invoice_') . '.html';
			$tempPdf = tempnam(sys_get_temp_dir(), 'invoice_') . '.pdf';
			file_put_contents($tempHtml, $html);

			$command = escapeshellarg(trim($wkhtmltopdf)) . ' --quiet --print-media-type --page-size A4 --margin-top 10mm --margin-bottom 10mm --margin-left 10mm --margin-right 10mm ' .
				escapeshellarg($tempHtml) . ' ' . escapeshellarg($tempPdf) . ' 2>&1';
			exec($command, $output, $returnCode);

			if ($returnCode === 0 && file_exists($tempPdf)) {
				$pdfContent = file_get_contents($tempPdf);
				@unlink($tempHtml);
				@unlink($tempPdf);
				return $pdfContent;
			}
		}

		// Fallback: Return HTML (browser can print to PDF)
		// This is not ideal but better than broken PDF
		return $html;
	}

	private function generatePdfWithTcpdf(string $html): string
	{
		// Load TCPDF if not already loaded
		if (!class_exists('TCPDF')) {
			$vendorPath = __DIR__ . '/../../vendor/autoload.php';
			if (file_exists($vendorPath)) {
				require_once $vendorPath;
			}
		}

		// Create new PDF document with UTF-8 encoding
		$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

		// Set document information
		$pdf->SetCreator('DomainControl');
		$pdf->SetAuthor('DomainControl');
		$pdf->SetTitle('Invoice');
		$pdf->SetSubject('Invoice Document');

		// Remove default header/footer
		$pdf->setPrintHeader(false);
		$pdf->setPrintFooter(false);

		// Set margins
		$pdf->SetMargins(15, 15, 15);
		$pdf->SetAutoPageBreak(TRUE, 15);

		// Set UTF-8 compatible font (dejavusans supports Turkish characters)
		$pdf->SetFont('dejavusans', '', 10);

		// Add a page
		$pdf->AddPage();

		// Ensure HTML is UTF-8 encoded
		$html = mb_convert_encoding($html, 'UTF-8', 'UTF-8');

		// Convert HTML to PDF
		// Keep style tags but simplify them for TCPDF
		$html = preg_replace('/<style[^>]*>(.*?)<\/style>/is', '<style>$1</style>', $html);

		// Write HTML content with UTF-8 support
		$pdf->writeHTML($html, true, false, true, false, '');

		// Close and output PDF document
		return $pdf->Output('', 'S'); // Return as string
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function sendEmail(int $id): JSONResponse
	{
		try {
			$invoice = $this->mapper->find($id, $this->userId);
			$data = $this->getRequestData();
			$recipientEmail = $data['email'] ?? '';

			// TODO: Implement email sending
			// For now, return success
			return new JSONResponse([
				'success' => true,
				'message' => 'Email sending not yet implemented',
				'invoice' => $invoice,
				'recipient' => $recipientEmail
			]);
		} catch (\Exception $e) {
			return new JSONResponse(['error' => $e->getMessage()], 500);
		}
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function exportReport(): JSONResponse
	{
		try {
			$invoices = $this->mapper->findAll($this->userId);
			// TODO: Implement CSV/Excel export
			// For now, return invoice data
			return new JSONResponse([
				'success' => true,
				'message' => 'Export not yet implemented',
				'invoices' => $invoices,
				'count' => count($invoices)
			]);
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

		// Also update paid amount just in case
		$this->updatePaidAmount($invoiceId);
	}

	private function updatePaidAmount(int $invoiceId): void
	{
		try {
			// Get all income transactions for this invoice
			$transactions = $this->transactionMapper->findByInvoice($invoiceId, $this->userId);
			$totalPaid = 0;
			foreach ($transactions as $t) {
				if ($t->getType() === 'income') {
					$totalPaid += $t->getAmount();
				}
			}

			$invoice = $this->mapper->find($invoiceId, $this->userId);

			// Only update if changed
			if ($invoice->getPaidAmount() !== (float) $totalPaid) {
				$invoice->setPaidAmount($totalPaid);

				// Auto-update status based on payments
				if ($totalPaid >= $invoice->getTotalAmount() && $invoice->getTotalAmount() > 0) {
					$invoice->setStatus('paid');
				} elseif ($totalPaid > 0 && $invoice->getStatus() === 'draft') {
					$invoice->setStatus('sent');
				}

				$invoice->setUpdatedAt(date('Y-m-d H:i:s'));
				$this->mapper->update($invoice);
			}
		} catch (\Exception $e) {
			// Ignore errors in recalculation
		}
	}
}


