<?php
declare(strict_types=1);

namespace OCA\DomainControl\Controller;

use OCA\DomainControl\AppInfo\Application;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;
use OCA\DomainControl\Db\TransactionMapper;
use OCA\DomainControl\Db\Transaction;

class TransactionController extends Controller
{
	private $userId;
	private TransactionMapper $mapper;

	public function __construct(IRequest $request,
	                            TransactionMapper $mapper,
	                            $userId)
	{
		parent::__construct(Application::APP_ID, $request);
		$this->mapper = $mapper;
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
			$transactions = $this->mapper->findAll($this->userId);
			return new JSONResponse($transactions);
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
			$transaction = $this->mapper->find($id, $this->userId);
			return new JSONResponse($transaction);
		} catch (\Exception $e) {
			return new JSONResponse(['error' => 'Transaction not found'], 404);
		}
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function byType(string $type): JSONResponse
	{
		try {
			$transactions = $this->mapper->findByType($type, $this->userId);
			return new JSONResponse($transactions);
		} catch (\Exception $e) {
			return new JSONResponse(['error' => $e->getMessage()], 500);
		}
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function byCategory(int $categoryId): JSONResponse
	{
		try {
			$transactions = $this->mapper->findByCategory($categoryId, $this->userId);
			return new JSONResponse($transactions);
		} catch (\Exception $e) {
			return new JSONResponse(['error' => $e->getMessage()], 500);
		}
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function byProject(int $projectId): JSONResponse
	{
		try {
			$transactions = $this->mapper->findByProject($projectId, $this->userId);
			return new JSONResponse($transactions);
		} catch (\Exception $e) {
			return new JSONResponse(['error' => $e->getMessage()], 500);
		}
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function byClient(int $clientId): JSONResponse
	{
		try {
			$transactions = $this->mapper->findByClient($clientId, $this->userId);
			return new JSONResponse($transactions);
		} catch (\Exception $e) {
			return new JSONResponse(['error' => $e->getMessage()], 500);
		}
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function monthlySummary(): JSONResponse
	{
		try {
			$yearMonth = $this->request->getParam('yearMonth', date('Y-m'));
			$summary = $this->mapper->getMonthlySummary($yearMonth, $this->userId);
			return new JSONResponse($summary);
		} catch (\Throwable $e) {
			// Log the error for debugging
			try {
				\OC::$server->get(\Psr\Log\LoggerInterface::class)->error('Monthly summary error: ' . $e->getMessage(), [
					'exception' => $e,
					'trace' => $e->getTraceAsString()
				]);
			} catch (\Throwable $logError) {
				// Ignore logging errors
			}
			// Return empty summary if table doesn't exist yet
			return new JSONResponse([
				'totalIncome' => 0,
				'totalExpense' => 0,
				'net' => 0
			]);
		}
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function summaryByCategory(): JSONResponse
	{
		try {
			$yearMonth = $this->request->getParam('yearMonth', date('Y-m'));
			$type = $this->request->getParam('type', 'expense');
			$summary = $this->mapper->getSummaryByCategory($yearMonth, $type, $this->userId);
			return new JSONResponse($summary);
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

			$type = $data['type'] ?? '';
			if (!in_array($type, ['income', 'expense'])) {
				return new JSONResponse(['error' => 'Type must be income or expense'], 400);
			}

			$amount = (float)($data['amount'] ?? 0);
			if ($amount <= 0) {
				return new JSONResponse(['error' => 'Amount must be greater than 0'], 400);
			}

			$transaction = new Transaction();
			$transaction->setType($type);
			$transaction->setCategoryId(isset($data['categoryId']) && $data['categoryId'] ? (int)$data['categoryId'] : null);
			$transaction->setProjectId(isset($data['projectId']) && $data['projectId'] ? (int)$data['projectId'] : null);
			$transaction->setClientId(isset($data['clientId']) && $data['clientId'] ? (int)$data['clientId'] : null);
			$transaction->setAmount($amount);
			$transaction->setCurrency($data['currency'] ?? 'USD');
			$transaction->setTransactionDate($data['transactionDate'] ?? date('Y-m-d'));
			$transaction->setDescription($data['description'] ?? '');
			$transaction->setPaymentMethod($data['paymentMethod'] ?? '');
			$transaction->setReference($data['reference'] ?? '');
			$transaction->setNotes($data['notes'] ?? '');
			$transaction->setUserId($this->userId);
			$now = date('Y-m-d H:i:s');
			$transaction->setCreatedAt($now);
			$transaction->setUpdatedAt($now);

			$transaction = $this->mapper->insert($transaction);
			return new JSONResponse($transaction);
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
			$transaction = $this->mapper->find($id, $this->userId);
			$data = $this->getRequestData();

			if (isset($data['type']) && in_array($data['type'], ['income', 'expense'])) {
				$transaction->setType($data['type']);
			}
			if (isset($data['categoryId'])) {
				$transaction->setCategoryId($data['categoryId'] ? (int)$data['categoryId'] : null);
			}
			if (isset($data['projectId'])) {
				$transaction->setProjectId($data['projectId'] ? (int)$data['projectId'] : null);
			}
			if (isset($data['clientId'])) {
				$transaction->setClientId($data['clientId'] ? (int)$data['clientId'] : null);
			}
			if (isset($data['amount']) && $data['amount'] > 0) {
				$transaction->setAmount((float)$data['amount']);
			}
			if (isset($data['currency'])) {
				$transaction->setCurrency($data['currency']);
			}
			if (isset($data['transactionDate'])) {
				$transaction->setTransactionDate($data['transactionDate']);
			}
			if (isset($data['description'])) {
				$transaction->setDescription($data['description']);
			}
			if (isset($data['paymentMethod'])) {
				$transaction->setPaymentMethod($data['paymentMethod']);
			}
			if (isset($data['reference'])) {
				$transaction->setReference($data['reference']);
			}
			if (isset($data['notes'])) {
				$transaction->setNotes($data['notes']);
			}

			$transaction->setUpdatedAt(date('Y-m-d H:i:s'));

			$transaction = $this->mapper->update($transaction);
			return new JSONResponse($transaction);
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
			$transaction = $this->mapper->find($id, $this->userId);
			$this->mapper->delete($transaction);
			return new JSONResponse(['success' => true]);
		} catch (\Exception $e) {
			return new JSONResponse(['error' => $e->getMessage()], 500);
		}
	}
}

