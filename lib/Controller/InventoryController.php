<?php

namespace OCA\DomainControl\Controller;

use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;
use OCA\DomainControl\Service\InventoryService;

class InventoryController extends Controller
{
    private $service;

    public function __construct($AppName, IRequest $request, InventoryService $service)
    {
        parent::__construct($AppName, $request);
        $this->service = $service;
    }

    /**
     * @NoAdminRequired
     */
    public function index()
    {
        return new DataResponse($this->service->findAll());
    }

    /**
     * @NoAdminRequired
     */
    public function show($id)
    {
        return new DataResponse($this->service->find($id));
    }

    /**
     * @NoAdminRequired
     */
    public function byCategory($categoryId)
    {
        return new DataResponse($this->service->findByCategory($categoryId));
    }

    /**
     * @NoAdminRequired
     */
    public function byWarehouse($warehouseId)
    {
        return new DataResponse($this->service->findByWarehouse($warehouseId));
    }

    /**
     * @NoAdminRequired
     * 
     * @param string $name
     * @param string $sku
     * @param int $categoryId
     * @param int $warehouseId
     * @param string $status
     * @param string $serialNumber
     * @param float $purchasePrice
     * @param float $salePrice
     * @param float $rentalPrice
     * @param string $description
     * @param string $imagePath
     * @param int $quantity
     * @param int $minQuantity
     */
    public function create($name, $sku, $categoryId = 0, $warehouseId = 0, $status = 'available', $serialNumber = '', $purchasePrice = 0, $salePrice = 0, $rentalPrice = 0, $description = '', $imagePath = '', $quantity = 0, $minQuantity = 0)
    {
        return new DataResponse($this->service->create(
            $name,
            $sku,
            $categoryId,
            $warehouseId,
            $status,
            $serialNumber,
            $purchasePrice,
            $salePrice,
            $rentalPrice,
            $description,
            $imagePath,
            $quantity,
            $minQuantity
        ));
    }

    /**
     * @NoAdminRequired
     * 
     * @param int $id
     */
    public function update($id)
    {
        // Read request body
        $body = file_get_contents('php://input');
        parse_str($body, $data);
        
        // Extract parameters with defaults
        $name = isset($data['name']) ? $data['name'] : '';
        $sku = isset($data['sku']) ? $data['sku'] : '';
        $categoryId = isset($data['categoryId']) ? (int)$data['categoryId'] : 0;
        $warehouseId = isset($data['warehouseId']) ? (int)$data['warehouseId'] : 0;
        $status = isset($data['status']) ? $data['status'] : 'available';
        $serialNumber = isset($data['serialNumber']) ? $data['serialNumber'] : '';
        $purchasePrice = isset($data['purchasePrice']) ? (float)$data['purchasePrice'] : 0;
        $salePrice = isset($data['salePrice']) ? (float)$data['salePrice'] : 0;
        $rentalPrice = isset($data['rentalPrice']) ? (float)$data['rentalPrice'] : 0;
        $description = isset($data['description']) ? $data['description'] : '';
        $quantity = isset($data['quantity']) ? (int)$data['quantity'] : 0;
        $minQuantity = isset($data['minQuantity']) ? (int)$data['minQuantity'] : 0;
        
        return new DataResponse($this->service->update(
            $id,
            $name,
            $sku,
            $categoryId,
            $warehouseId,
            $status,
            $serialNumber,
            $purchasePrice,
            $salePrice,
            $rentalPrice,
            $description,
            null, // imageFile is null - images are managed separately via InventoryImageController
            $quantity,
            $minQuantity
        ));
    }

    /**
     * @NoAdminRequired
     */
    public function destroy($id)
    {
        return new DataResponse($this->service->delete($id));
    }
}
