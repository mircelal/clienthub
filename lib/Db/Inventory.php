<?php

namespace OCA\DomainControl\Db;

use OCP\AppFramework\Db\Entity;

class Inventory extends Entity implements \JsonSerializable
{
    protected $name;
    protected $sku;
    protected $categoryId;
    protected $warehouseId;
    protected $status;
    protected $serialNumber;
    protected $purchasePrice;
    protected $salePrice;
    protected $rentalPrice;
    protected $purchasedAt;
    protected $description;
    protected $imagePath;
    protected $refClientId;
    protected $quantity;
    protected $minQuantity;

    public function __construct()
    {
        $this->addType('purchasePrice', 'float');
        $this->addType('salePrice', 'float');
        $this->addType('rentalPrice', 'float');
        $this->addType('categoryId', 'integer');
        $this->addType('warehouseId', 'integer');
        $this->addType('refClientId', 'integer');
        $this->addType('quantity', 'integer');
        $this->addType('minQuantity', 'integer');
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'sku' => $this->sku,
            'categoryId' => $this->categoryId,
            'warehouseId' => $this->warehouseId,
            'status' => $this->status,
            'serialNumber' => $this->serialNumber,
            'purchasePrice' => $this->purchasePrice,
            'salePrice' => $this->salePrice,
            'rentalPrice' => $this->rentalPrice,
            'purchasedAt' => $this->purchasedAt,
            'description' => $this->description,
            'imagePath' => $this->imagePath,
            'refClientId' => $this->refClientId,
            'quantity' => $this->quantity ?? 0,
            'minQuantity' => $this->minQuantity ?? 0,
        ];
    }
}
