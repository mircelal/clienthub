<?php
declare(strict_types=1);

namespace OCA\DomainControl\Controller;

use OCA\DomainControl\AppInfo\Application;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;
use OCA\DomainControl\Db\DebtMapper;
use OCA\DomainControl\Db\DebtPaymentMapper;
use OCA\DomainControl\Db\Debt;
use OCA\DomainControl\Db\DebtPayment;
use OCA\DomainControl\Service\CalendarService;

class DebtController extends Controller
{
	private $userId;
	private DebtMapper $mapper;
	private DebtPaymentMapper $paymentMapper;
	private CalendarService $calendarService;

	public function __construct(IRequest $request,
	                            DebtMapper $mapper,
	                            DebtPaymentMapper $paymentMapper,
	                            CalendarService $calendarService,
	                            $userId)
	{
		parent::__construct(Application::APP_ID, $request);
		$this->mapper = $mapper;
		$this->paymentMapper = $paymentMapper;
		$this->calendarService = $calendarService;
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
			$debts = $this->mapper->findAll($this->userId);
			return new JSONResponse($debts);
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
			$debt = $this->mapper->find($id, $this->userId);
			$payments = $this->paymentMapper->findByDebt($id, $this->userId);
			$result = $debt->jsonSerialize();
			$result['payments'] = $payments;
			return new JSONResponse($result);
		} catch (\Exception $e) {
			return new JSONResponse(['error' => 'Debt not found'], 404);
		}
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function byType(string $type): JSONResponse
	{
		try {
			$debts = $this->mapper->findByType($type, $this->userId);
			return new JSONResponse($debts);
		} catch (\Exception $e) {
			return new JSONResponse(['error' => $e->getMessage()], 500);
		}
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function upcomingPayments(): JSONResponse
	{
		try {
			$days = (int)($this->request->getParam('days', 30));
			$debts = $this->mapper->findUpcomingPayments($days, $this->userId);
			return new JSONResponse($debts);
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
			$debts = $this->mapper->findOverdue($this->userId);
			return new JSONResponse($debts);
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
			if (!in_array($type, ['debt', 'credit'])) {
				return new JSONResponse(['error' => 'Type must be debt or credit'], 400);
			}

			$debtType = $data['debtType'] ?? '';
			if (!in_array($debtType, ['credit_card', 'loan', 'physical', 'other'])) {
				return new JSONResponse(['error' => 'Invalid debt type'], 400);
			}

			$totalAmount = (float)($data['totalAmount'] ?? 0);
			if ($totalAmount <= 0) {
				return new JSONResponse(['error' => 'Total amount must be greater than 0'], 400);
			}

			$debt = new Debt();
			$debt->setType($type);
			$debt->setDebtType($debtType);
			$debt->setCreditorDebtorName($data['creditorDebtorName'] ?? null);
			$debt->setClientId(isset($data['clientId']) && $data['clientId'] ? (int)$data['clientId'] : null);
			$debt->setTotalAmount($totalAmount);
			$debt->setPaidAmount((float)($data['paidAmount'] ?? 0));
			$debt->setCurrency($data['currency'] ?? 'USD');
			$debt->setInterestRate(isset($data['interestRate']) && $data['interestRate'] ? (float)$data['interestRate'] : null);
			$debt->setStartDate(!empty($data['startDate']) ? $data['startDate'] : date('Y-m-d'));
			$debt->setDueDate(!empty($data['dueDate']) ? $data['dueDate'] : null);
			$debt->setNextPaymentDate(!empty($data['nextPaymentDate']) ? $data['nextPaymentDate'] : null);
			$debt->setPaymentFrequency(!empty($data['paymentFrequency']) ? $data['paymentFrequency'] : null);
			$debt->setPaymentAmount(isset($data['paymentAmount']) && $data['paymentAmount'] ? (float)$data['paymentAmount'] : null);
			$debt->setDescription($data['description'] ?? '');
			$debt->setStatus($data['status'] ?? 'active');
			$debt->setNotes($data['notes'] ?? '');
			$debt->setUserId($this->userId);
			$now = date('Y-m-d H:i:s');
			$debt->setCreatedAt($now);
			$debt->setUpdatedAt($now);

			$debt = $this->mapper->insert($debt);
			
			// Create calendar event if next payment date is set
			if ($debt->getNextPaymentDate() && $debt->getStatus() === 'active') {
				$title = ($debt->getType() === 'debt' ? 'Borç' : 'Alacak') . ' Ödemesi: ' . ($debt->getCreditorDebtorName() ?: 'Ödeme');
				$description = 'Tutar: ' . $debt->getTotalAmount() . ' ' . $debt->getCurrency();
				if ($debt->getPaymentAmount()) {
					$description .= ' (Taksit: ' . $debt->getPaymentAmount() . ' ' . $debt->getCurrency() . ')';
				}
				$this->calendarService->addDebtEvent($this->userId, $title, $debt->getNextPaymentDate(), $description);
			}
			
			return new JSONResponse($debt);
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
			$debt = $this->mapper->find($id, $this->userId);
			$data = $this->getRequestData();

			if (isset($data['type']) && in_array($data['type'], ['debt', 'credit'])) {
				$debt->setType($data['type']);
			}
			if (isset($data['debtType']) && in_array($data['debtType'], ['credit_card', 'loan', 'physical', 'other'])) {
				$debt->setDebtType($data['debtType']);
			}
			if (isset($data['creditorDebtorName'])) {
				$debt->setCreditorDebtorName($data['creditorDebtorName']);
			}
			if (isset($data['clientId'])) {
				$debt->setClientId($data['clientId'] ? (int)$data['clientId'] : null);
			}
			if (isset($data['totalAmount']) && $data['totalAmount'] > 0) {
				$debt->setTotalAmount((float)$data['totalAmount']);
			}
			if (isset($data['paidAmount'])) {
				$debt->setPaidAmount((float)$data['paidAmount']);
			}
			if (isset($data['currency'])) {
				$debt->setCurrency($data['currency']);
			}
			if (isset($data['interestRate'])) {
				$debt->setInterestRate($data['interestRate'] ? (float)$data['interestRate'] : null);
			}
			if (isset($data['startDate'])) {
				$debt->setStartDate(!empty($data['startDate']) ? $data['startDate'] : date('Y-m-d'));
			}
			if (isset($data['dueDate'])) {
				$debt->setDueDate(!empty($data['dueDate']) ? $data['dueDate'] : null);
			}
			if (isset($data['nextPaymentDate'])) {
				$debt->setNextPaymentDate(!empty($data['nextPaymentDate']) ? $data['nextPaymentDate'] : null);
			}
			if (isset($data['paymentFrequency'])) {
				$debt->setPaymentFrequency($data['paymentFrequency']);
			}
			if (isset($data['paymentAmount'])) {
				$debt->setPaymentAmount($data['paymentAmount'] ? (float)$data['paymentAmount'] : null);
			}
			if (isset($data['description'])) {
				$debt->setDescription($data['description']);
			}
			if (isset($data['status']) && in_array($data['status'], ['active', 'paid', 'overdue', 'cancelled'])) {
				$debt->setStatus($data['status']);
			}
			if (isset($data['notes'])) {
				$debt->setNotes($data['notes']);
			}

			$debt->setUpdatedAt(date('Y-m-d H:i:s'));

			$debt = $this->mapper->update($debt);
			
			// Update calendar event if next payment date changed
			if (isset($data['nextPaymentDate']) && $debt->getNextPaymentDate() && $debt->getStatus() === 'active') {
				$title = ($debt->getType() === 'debt' ? 'Borç' : 'Alacak') . ' Ödemesi: ' . ($debt->getCreditorDebtorName() ?: 'Ödeme');
				$description = 'Tutar: ' . $debt->getTotalAmount() . ' ' . $debt->getCurrency();
				if ($debt->getPaymentAmount()) {
					$description .= ' (Taksit: ' . $debt->getPaymentAmount() . ' ' . $debt->getCurrency() . ')';
				}
				$this->calendarService->addDebtEvent($this->userId, $title, $debt->getNextPaymentDate(), $description);
			}
			
			return new JSONResponse($debt);
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
			$debt = $this->mapper->find($id, $this->userId);
			$this->mapper->delete($debt);
			return new JSONResponse(['success' => true]);
		} catch (\Exception $e) {
			return new JSONResponse(['error' => $e->getMessage()], 500);
		}
	}

	/**
	 * @NoAdminRequired
	 */
	public function addPayment(int $id): JSONResponse
	{
		try {
			$debt = $this->mapper->find($id, $this->userId);
			$data = $this->getRequestData();

			$amount = (float)($data['amount'] ?? 0);
			if ($amount <= 0) {
				return new JSONResponse(['error' => 'Amount must be greater than 0'], 400);
			}

			$payment = new DebtPayment();
			$payment->setDebtId($id);
			$payment->setAmount($amount);
			$payment->setPaymentDate($data['paymentDate'] ?? date('Y-m-d'));
			$payment->setPaymentMethod($data['paymentMethod'] ?? '');
			$payment->setReference($data['reference'] ?? '');
			$payment->setNotes($data['notes'] ?? '');
			$payment->setUserId($this->userId);
			$now = date('Y-m-d H:i:s');
			$payment->setCreatedAt($now);
			$payment->setUpdatedAt($now);

			$payment = $this->paymentMapper->insert($payment);

			// Update debt paid amount
			$totalPaid = $this->paymentMapper->getTotalPaid($id, $this->userId);
			$debt->setPaidAmount($totalPaid);

			// Update status if fully paid
			if ($debt->getTotalAmount() <= $totalPaid) {
				$debt->setStatus('paid');
			} elseif ($debt->getStatus() === 'paid') {
				$debt->setStatus('active');
			}

			// Update next payment date if payment frequency is set
			if ($debt->getPaymentFrequency() && $debt->getNextPaymentDate()) {
				$nextDate = new \DateTime($debt->getNextPaymentDate());
				switch ($debt->getPaymentFrequency()) {
					case 'monthly':
						$nextDate->modify('+1 month');
						break;
					case 'weekly':
						$nextDate->modify('+1 week');
						break;
					case 'daily':
						$nextDate->modify('+1 day');
						break;
				}
				$debt->setNextPaymentDate($nextDate->format('Y-m-d'));
				
				// Create calendar event for next payment
				$title = ($debt->getType() === 'debt' ? 'Borç' : 'Alacak') . ' Ödemesi: ' . ($debt->getCreditorDebtorName() ?: 'Ödeme');
				$description = 'Tutar: ' . $debt->getTotalAmount() . ' ' . $debt->getCurrency();
				if ($debt->getPaymentAmount()) {
					$description .= ' (Taksit: ' . $debt->getPaymentAmount() . ' ' . $debt->getCurrency() . ')';
				}
				$this->calendarService->addDebtEvent($this->userId, $title, $debt->getNextPaymentDate(), $description);
			}

			$debt->setUpdatedAt($now);
			$this->mapper->update($debt);

			return new JSONResponse($payment);
		} catch (\Exception $e) {
			return new JSONResponse(['error' => $e->getMessage()], 500);
		}
	}
}

