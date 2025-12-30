<?php
namespace OCA\DomainControl\Service;

use Exception;
use OCA\DomainControl\Db\InventoryMovement;
use OCA\DomainControl\Db\InventoryMovementMapper;
use OCA\DomainControl\Db\InventoryMapper;
use OCA\DomainControl\Db\ClientMapper;
use OCA\DomainControl\Db\Order;
use OCA\DomainControl\Db\OrderMapper;
use Psr\Log\LoggerInterface;

class POSService
{
    private $movementMapper;
    private $inventoryMapper;
    private $clientMapper;
    private $orderMapper;
    private LoggerInterface $logger;

    public function __construct(
        InventoryMovementMapper $movementMapper,
        InventoryMapper $inventoryMapper,
        ClientMapper $clientMapper,
        OrderMapper $orderMapper,
        LoggerInterface $logger
    ) {
        $this->movementMapper = $movementMapper;
        $this->inventoryMapper = $inventoryMapper;
        $this->clientMapper = $clientMapper;
        $this->orderMapper = $orderMapper;
        $this->logger = $logger;
    }

    public function createTransaction($inventoryId, $clientId, $type, $price, $rentalDays = null, $notes = '')
    {
        try {
            // Verify inventory item exists
            $inventory = $this->inventoryMapper->find($inventoryId);
            if (!$inventory) {
                throw new Exception('Inventory item not found');
            }

            // Verify client exists
            $client = $this->clientMapper->find($clientId, \OC::$server->getUserSession()->getUser()->getUID());
            if (!$client) {
                throw new Exception('Client not found');
            }

            // Create movement record
            $movement = new InventoryMovement();
            $movement->setInventoryId($inventoryId);
            $movement->setClientId($clientId);
            // Handle both 'rental' and 'rent' types
            $movementType = ($type === 'rental' || $type === 'rent') ? 'rent' : 'sale';
            $movement->setType($movementType);
            $movement->setDateOut(date('Y-m-d'));
            $movement->setPrice($price);
            $movement->setNotes($notes);

            if (($type === 'rental' || $type === 'rent') && $rentalDays) {
                $dueDate = date('Y-m-d', strtotime("+{$rentalDays} days"));
                $movement->setDateDue($dueDate);
            }

            $saved = $this->movementMapper->insert($movement);

            // Update inventory status and quantity
            if ($movementType === 'sale') {
                $inventory->setStatus('sold');
                $currentQuantity = $inventory->getQuantity() ?? 0;
                $inventory->setQuantity(max(0, $currentQuantity - 1));
            } else {
                $inventory->setStatus('rented');
                $currentQuantity = $inventory->getQuantity() ?? 0;
                $inventory->setQuantity(max(0, $currentQuantity - 1));
            }
            $this->inventoryMapper->update($inventory);

            $this->logger->debug('POSService::createTransaction success - MovementId: ' . $saved->getId(), ['app' => 'domaincontrol']);
            return $saved;
        } catch (Exception $e) {
            $this->logger->error('POSService::createTransaction error: ' . $e->getMessage(), ['app' => 'domaincontrol', 'exception' => $e]);
            throw $e;
        }
    }

    public function returnRental($movementId)
    {
        try {
            $movement = $this->movementMapper->find($movementId);
            if (!$movement) {
                throw new Exception('Movement not found');
            }

            if ($movement->getType() !== 'rent') {
                throw new Exception('This is not a rental');
            }

            if ($movement->getDateReturned()) {
                throw new Exception('This rental has already been returned');
            }

            $movement->setDateReturned(date('Y-m-d'));
            $this->movementMapper->update($movement);

            // Update inventory status and quantity
            $inventory = $this->inventoryMapper->find($movement->getInventoryId());
            if ($inventory) {
                $inventory->setStatus('available');
                $currentQuantity = $inventory->getQuantity() ?? 0;
                $inventory->setQuantity($currentQuantity + 1);
                $this->inventoryMapper->update($inventory);
            }

            $this->logger->debug('POSService::returnRental success - MovementId: ' . $movementId, ['app' => 'domaincontrol']);
            return $movement;
        } catch (Exception $e) {
            $this->logger->error('POSService::returnRental error: ' . $e->getMessage(), ['app' => 'domaincontrol', 'exception' => $e]);
            throw $e;
        }
    }

    public function returnSale($movementId)
    {
        try {
            $movement = $this->movementMapper->find($movementId);
            if (!$movement) {
                throw new Exception('Movement not found');
            }

            if ($movement->getType() !== 'sale') {
                throw new Exception('This is not a sale');
            }

            if ($movement->getDateReturned()) {
                throw new Exception('This sale has already been returned');
            }

            $movement->setDateReturned(date('Y-m-d'));
            $this->movementMapper->update($movement);

            // Update inventory status and quantity
            $inventory = $this->inventoryMapper->find($movement->getInventoryId());
            if ($inventory) {
                $inventory->setStatus('available');
                $currentQuantity = $inventory->getQuantity() ?? 0;
                $inventory->setQuantity($currentQuantity + 1);
                $this->inventoryMapper->update($inventory);
            }

            $this->logger->debug('POSService::returnSale success - MovementId: ' . $movementId, ['app' => 'domaincontrol']);
            return $movement;
        } catch (Exception $e) {
            $this->logger->error('POSService::returnSale error: ' . $e->getMessage(), ['app' => 'domaincontrol', 'exception' => $e]);
            throw $e;
        }
    }

    public function getRecentSales($limit = 50)
    {
        $movements = $this->movementMapper->findRecentSales($limit);
        return $this->enrichMovements($movements);
    }

    public function getActiveRentals()
    {
        $movements = $this->movementMapper->findActiveRentals();
        return $this->enrichMovements($movements);
    }

    public function getReturns()
    {
        $movements = $this->movementMapper->findReturns();
        return $this->enrichMovements($movements);
    }

    public function getMovementsByInventory($inventoryId)
    {
        $movements = $this->movementMapper->findByInventoryId($inventoryId);
        return $this->enrichMovements($movements);
    }

    private function enrichMovements($movements)
    {
        $userId = \OC::$server->getUserSession()->getUser()->getUID();
        $enriched = [];

        foreach ($movements as $movement) {
            $item = $this->inventoryMapper->find($movement->getInventoryId());
            $client = $this->clientMapper->find($movement->getClientId(), $userId);

            $enriched[] = [
                'id' => $movement->getId(),
                'itemName' => $item ? $item->getName() : 'Unknown',
                'itemSku' => $item ? $item->getSku() : '',
                'customerName' => $client ? $client->getName() : 'Unknown',
                'customerId' => $movement->getClientId(),
                'orderId' => $movement->getOrderId(),
                'type' => $movement->getType(),
                'dateOut' => $movement->getDateOut(),
                'dateDue' => $movement->getDateDue(),
                'dateReturned' => $movement->getDateReturned(),
                'price' => $movement->getPrice(),
                'notes' => $movement->getNotes(),
            ];
        }

        return $enriched;
    }

    private function enrichOrders($orders)
    {
        $userId = \OC::$server->getUserSession()->getUser()->getUID();
        $enriched = [];

        foreach ($orders as $order) {
            $client = $this->clientMapper->find($order->getClientId(), $userId);
            
            // Get movements for this order
            $movements = $this->movementMapper->findByOrderId($order->getId());
            $items = [];
            $hasOverdue = false;
            $allReturned = true;

            foreach ($movements as $movement) {
                $item = $this->inventoryMapper->find($movement->getInventoryId());
                $items[] = [
                    'id' => $movement->getId(),
                    'inventoryId' => $movement->getInventoryId(),
                    'itemName' => $item ? $item->getName() : 'Unknown',
                    'itemSku' => $item ? $item->getSku() : '',
                    'type' => $movement->getType(),
                    'dateOut' => $movement->getDateOut(),
                    'dateDue' => $movement->getDateDue(),
                    'dateReturned' => $movement->getDateReturned(),
                    'price' => $movement->getPrice(),
                    'notes' => $movement->getNotes(),
                ];

                // Check if overdue
                if ($movement->getDateDue() && !$movement->getDateReturned()) {
                    $dueDate = new \DateTime($movement->getDateDue());
                    $today = new \DateTime();
                    if ($dueDate < $today) {
                        $hasOverdue = true;
                    }
                }

                if (!$movement->getDateReturned()) {
                    $allReturned = false;
                }
            }

            $enriched[] = [
                'id' => $order->getId(),
                'orderNumber' => $order->getOrderNumber(),
                'customerName' => $client ? $client->getName() : 'Unknown',
                'customerId' => $order->getClientId(),
                'type' => $order->getType(),
                'orderDate' => $order->getOrderDate(),
                'totalAmount' => $order->getTotalAmount(),
                'status' => $order->getStatus(),
                'notes' => $order->getNotes(),
                'itemCount' => count($items),
                'items' => $items,
                'hasOverdue' => $hasOverdue,
                'allReturned' => $allReturned,
            ];
        }

        return $enriched;
    }

    private function enrichOrder($order)
    {
        $userId = \OC::$server->getUserSession()->getUser()->getUID();
        $client = $this->clientMapper->find($order->getClientId(), $userId);
        
        // Get movements for this order
        $movements = $this->movementMapper->findByOrderId($order->getId());
        $items = [];
        $hasOverdue = false;
        $allReturned = true;

        foreach ($movements as $movement) {
            $item = $this->inventoryMapper->find($movement->getInventoryId());
            $items[] = [
                'id' => $movement->getId(),
                'inventoryId' => $movement->getInventoryId(),
                'itemName' => $item ? $item->getName() : 'Unknown',
                'itemSku' => $item ? $item->getSku() : '',
                'type' => $movement->getType(),
                'dateOut' => $movement->getDateOut(),
                'dateDue' => $movement->getDateDue(),
                'dateReturned' => $movement->getDateReturned(),
                'price' => $movement->getPrice(),
                'notes' => $movement->getNotes(),
            ];

            // Check if overdue
            if ($movement->getDateDue() && !$movement->getDateReturned()) {
                $dueDate = new \DateTime($movement->getDateDue());
                $today = new \DateTime();
                if ($dueDate < $today) {
                    $hasOverdue = true;
                }
            }

            if (!$movement->getDateReturned()) {
                $allReturned = false;
            }
        }

        return [
            'id' => $order->getId(),
            'orderNumber' => $order->getOrderNumber(),
            'customerName' => $client ? $client->getName() : 'Unknown',
            'customerId' => $order->getClientId(),
            'type' => $order->getType(),
            'orderDate' => $order->getOrderDate(),
            'totalAmount' => $order->getTotalAmount(),
            'status' => $order->getStatus(),
            'notes' => $order->getNotes(),
            'itemCount' => count($items),
            'items' => $items,
            'hasOverdue' => $hasOverdue,
            'allReturned' => $allReturned,
        ];
    }
}

