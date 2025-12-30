<?php

namespace OCA\DomainControl\Service;

use Exception;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;
use OCA\DomainControl\Db\Inventory;
use OCA\DomainControl\Db\InventoryMapper;
use OCA\DomainControl\Service\NotFoundException;

use OCP\Files\IRootFolder;
use OCP\Files\NotFoundException as FileNotFoundException;

class InventoryService
{
    private $mapper;
    private $rootFolder;
    private $userId;

    public function __construct(InventoryMapper $mapper, IRootFolder $rootFolder)
    {
        $this->mapper = $mapper;
        $this->rootFolder = $rootFolder;
        // Basic way to get current user ID in service, better passed from controller but this works for now
        $this->userId = \OC::$server->getUserSession()->getUser()->getUID();
    }

    public function findAll()
    {
        return $this->mapper->findAll();
    }

    public function findByCategory($categoryId)
    {
        return $this->mapper->findByCategory($categoryId);
    }

    public function findByWarehouse($warehouseId)
    {
        return $this->mapper->findByWarehouse($warehouseId);
    }

    public function find($id)
    {
        try {
            return $this->mapper->find($id);
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }

    public function uploadImage($file)
    {
        if (!$file)
            return null;

        try {
            $userFolder = $this->rootFolder->getUserFolder($this->userId);

            // Ensure Inventory folder exists
            if (!$userFolder->nodeExists('Inventory')) {
                $userFolder->newFolder('Inventory');
            }
            $invFolder = $userFolder->get('Inventory');

            // Ensure Images folder exists
            if (!$invFolder->nodeExists('Images')) {
                $invFolder->newFolder('Images');
            }
            $imagesFolder = $invFolder->get('Images');

            // Generate unique filename
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = uniqid('img_') . '.' . $extension;

            // Save file
            $newFile = $imagesFolder->newFile($filename);
            $newFile->putContent(file_get_contents($file['tmp_name']));

            // Return path relative to user root, e.g., "Inventory/Images/img_123.jpg"
            // Or better: Use Nextcloud's generateUrl for preview?
            // For now, let's store the raw path and frontend can request preview/download.
            return 'Inventory/Images/' . $filename;

        } catch (Exception $e) {
            // Log error
            return null;
        }
    }

    public function create($name, $sku, $categoryId, $warehouseId, $status, $serialNumber, $purchasePrice, $salePrice, $rentalPrice, $description, $imageFile = null, $quantity = 0, $minQuantity = 0)
    {
        $imagePath = '';
        if ($imageFile) {
            $imagePath = $this->uploadImage($imageFile);
        }

        $item = new Inventory();
        $item->setName($name);
        $item->setSku($sku);
        $item->setCategoryId((int) $categoryId);
        $item->setWarehouseId((int) $warehouseId);
        $item->setStatus($status);
        $item->setSerialNumber($serialNumber);
        $item->setPurchasePrice((float) $purchasePrice);
        $item->setSalePrice((float) $salePrice);
        $item->setRentalPrice((float) $rentalPrice);
        $item->setDescription($description);
        $item->setImagePath($imagePath); // Store local NC path
        $item->setPurchasedAt(date('Y-m-d'));
        $item->setQuantity((int) $quantity);
        $item->setMinQuantity((int) $minQuantity);

        return $this->mapper->insert($item);
    }

    public function update($id, $name, $sku, $categoryId, $warehouseId, $status, $serialNumber, $purchasePrice, $salePrice, $rentalPrice, $description, $imageFile = null, $quantity = 0, $minQuantity = 0)
    {
        try {
            $item = $this->mapper->find($id);
            $item->setName($name);
            $item->setSku($sku);
            $item->setCategoryId((int) $categoryId);
            $item->setWarehouseId((int) $warehouseId);
            $item->setStatus($status);
            $item->setSerialNumber($serialNumber);
            $item->setPurchasePrice((float) $purchasePrice);
            $item->setSalePrice((float) $salePrice);
            $item->setRentalPrice((float) $rentalPrice);
            $item->setDescription($description);
            $item->setQuantity((int) $quantity);
            $item->setMinQuantity((int) $minQuantity);

            if ($imageFile) {
                $item->setImagePath($this->uploadImage($imageFile));
            } elseif ($imageFile === false) {
                // Explicit delete logic could go here
            }

            return $this->mapper->update($item);
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }

    public function delete($id)
    {
        try {
            $item = $this->mapper->find($id);
            // Optionally delete image file here
            $this->mapper->delete($item);
            return $item;
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }

    private function handleException($e)
    {
        if (
            $e instanceof DoesNotExistException ||
            $e instanceof MultipleObjectsReturnedException
        ) {
            throw new NotFoundException($e->getMessage());
        } else {
            throw $e;
        }
    }
}
