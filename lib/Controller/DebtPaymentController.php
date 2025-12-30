<?php
declare(strict_types=1);

namespace OCA\DomainControl\Controller;

use OCA\DomainControl\AppInfo\Application;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;
use OCA\DomainControl\Db\DebtPaymentMapper;
use OCA\DomainControl\Db\DebtMapper;

class DebtPaymentController extends Controller
{
	private $userId;
	private DebtPaymentMapper $mapper;
	private DebtMapper $debtMapper;

	public function __construct(IRequest $request,
	                            DebtPaymentMapper $mapper,
	                            DebtMapper $debtMapper,
	                            $userId)
	{
		parent::__construct(Application::APP_ID, $request);
		$this->mapper = $mapper;
		$this->debtMapper = $debtMapper;
		$this->userId = $userId;
	}

	/**
	 * @NoAdminRequired
	 */
	public function delete(int $id): JSONResponse
	{
		try {
			$payment = $this->mapper->find($id, $this->userId);
			$debtId = $payment->getDebtId();

			$this->mapper->delete($payment);

			// Update debt paid amount
			$debt = $this->debtMapper->find($debtId, $this->userId);
			$totalPaid = $this->mapper->getTotalPaid($debtId, $this->userId);
			$debt->setPaidAmount($totalPaid);

			// Update status
			if ($debt->getTotalAmount() <= $totalPaid) {
				$debt->setStatus('paid');
			} else {
				$debt->setStatus('active');
			}

			$debt->setUpdatedAt(date('Y-m-d H:i:s'));
			$this->debtMapper->update($debt);

			return new JSONResponse(['success' => true]);
		} catch (\Exception $e) {
			return new JSONResponse(['error' => $e->getMessage()], 500);
		}
	}
}

