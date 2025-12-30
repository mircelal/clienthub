<?php
namespace OCA\DomainControl\Controller;

use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;
use Psr\Log\LoggerInterface;
use OCA\DomainControl\Service\POSService;

class POSController extends Controller
{
    private $service;
    private LoggerInterface $logger;

    public function __construct($AppName, IRequest $request, POSService $service, LoggerInterface $logger)
    {
        parent::__construct($AppName, $request);
        $this->service = $service;
        $this->logger = $logger;
    }

    /**
     * @NoAdminRequired
     */
    public function createOrder()
    {
        try {
            $body = file_get_contents('php://input');
            parse_str($body, $data);

            $clientId = isset($data['clientId']) ? (int)$data['clientId'] : 0;
            $type = isset($data['type']) ? $data['type'] : 'sale';
            $items = isset($data['items']) ? json_decode($data['items'], true) : [];
            $notes = isset($data['notes']) ? $data['notes'] : '';

            if (!$clientId || empty($items)) {
                return new DataResponse(['error' => 'Client ID and items are required'], 400);
            }

            $order = $this->service->createOrder($clientId, $type, $items, $notes);
            return new DataResponse($order);
        } catch (\Exception $e) {
            $this->logger->error('POSController::createOrder error: ' . $e->getMessage(), ['app' => 'domaincontrol', 'exception' => $e]);
            return new DataResponse(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * @NoAdminRequired
     */
    public function createTransaction()
    {
        try {
            $body = file_get_contents('php://input');
            parse_str($body, $data);

            $inventoryId = isset($data['inventoryId']) ? (int)$data['inventoryId'] : 0;
            $clientId = isset($data['clientId']) ? (int)$data['clientId'] : 0;
            $type = isset($data['type']) ? $data['type'] : 'sale';
            $price = isset($data['price']) ? (float)$data['price'] : 0;
            $rentalDays = isset($data['rentalDays']) ? (int)$data['rentalDays'] : null;
            $notes = isset($data['notes']) ? $data['notes'] : '';

            if (!$inventoryId || !$clientId) {
                return new DataResponse(['error' => 'Inventory ID and Client ID are required'], 400);
            }

            $order = $this->service->createTransaction($inventoryId, $clientId, $type, $price, $rentalDays, $notes);
            return new DataResponse($order);
        } catch (\Exception $e) {
            $this->logger->error('POSController::createTransaction error: ' . $e->getMessage(), ['app' => 'domaincontrol', 'exception' => $e]);
            return new DataResponse(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * @NoAdminRequired
     */
    public function getRecentSales()
    {
        try {
            $sales = $this->service->getRecentSales(50);
            return new DataResponse($sales);
        } catch (\Exception $e) {
            $this->logger->error('POSController::getRecentSales error: ' . $e->getMessage(), ['app' => 'domaincontrol', 'exception' => $e]);
            return new DataResponse(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * @NoAdminRequired
     */
    public function getActiveRentals()
    {
        try {
            $rentals = $this->service->getActiveRentals();
            return new DataResponse($rentals);
        } catch (\Exception $e) {
            $this->logger->error('POSController::getActiveRentals error: ' . $e->getMessage(), ['app' => 'domaincontrol', 'exception' => $e]);
            return new DataResponse(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * @NoAdminRequired
     */
    public function getReturns()
    {
        try {
            $returns = $this->service->getReturns();
            return new DataResponse($returns);
        } catch (\Exception $e) {
            $this->logger->error('POSController::getReturns error: ' . $e->getMessage(), ['app' => 'domaincontrol', 'exception' => $e]);
            return new DataResponse(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * @NoAdminRequired
     */
    public function returnRental($id)
    {
        try {
            $movement = $this->service->returnRental($id);
            return new DataResponse($movement);
        } catch (\Exception $e) {
            $this->logger->error('POSController::returnRental error: ' . $e->getMessage(), ['app' => 'domaincontrol', 'exception' => $e]);
            return new DataResponse(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * @NoAdminRequired
     */
    public function returnSale($id)
    {
        try {
            $movement = $this->service->returnSale($id);
            return new DataResponse($movement);
        } catch (\Exception $e) {
            $this->logger->error('POSController::returnSale error: ' . $e->getMessage(), ['app' => 'domaincontrol', 'exception' => $e]);
            return new DataResponse(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * @NoAdminRequired
     */
    public function getMovementsByInventory($inventoryId)
    {
        try {
            $movements = $this->service->getMovementsByInventory($inventoryId);
            return new DataResponse($movements);
        } catch (\Exception $e) {
            $this->logger->error('POSController::getMovementsByInventory error: ' . $e->getMessage(), ['app' => 'domaincontrol', 'exception' => $e]);
            return new DataResponse(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * @NoAdminRequired
     */
    public function getOrder($id)
    {
        try {
            $order = $this->service->getOrderById($id);
            if (!$order) {
                return new DataResponse(['error' => 'Order not found'], 404);
            }
            return new DataResponse($order);
        } catch (\Exception $e) {
            $this->logger->error('POSController::getOrder error: ' . $e->getMessage(), ['app' => 'domaincontrol', 'exception' => $e]);
            return new DataResponse(['error' => $e->getMessage()], 500);
        }
    }
}

