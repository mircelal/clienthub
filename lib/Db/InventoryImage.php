<?php

namespace OCA\DomainControl\Db;

use OCP\AppFramework\Db\Entity;

/**
 * @method int getInventoryId()
 * @method void setInventoryId(int $inventoryId)
 * @method string getFilePath()
 * @method void setFilePath(string $filePath)
 * @method int getFileId()
 * @method void setFileId(int $fileId)
 * @method int getIsPrimary()
 * @method void setIsPrimary(int $isPrimary)
 * @method int getSortOrder()
 * @method void setSortOrder(int $sortOrder)
 * @method string getCreatedAt()
 * @method void setCreatedAt(string $createdAt)
 */
class InventoryImage extends Entity implements \JsonSerializable
{
    protected $inventoryId;
    protected $filePath;
    protected $fileId;
    protected $isPrimary;
    protected $sortOrder;
    protected $createdAt;

    public function __construct()
    {
        $this->addType('inventoryId', 'integer');
        $this->addType('filePath', 'string');
        $this->addType('fileId', 'integer');
        $this->addType('isPrimary', 'integer');
        $this->addType('sortOrder', 'integer');
        $this->addType('createdAt', 'string');
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'inventoryId' => $this->inventoryId,
            'filePath' => $this->filePath,
            'fileId' => $this->fileId,
            'isPrimary' => (bool) $this->isPrimary,
            'sortOrder' => $this->sortOrder,
            'createdAt' => $this->createdAt,
        ];
    }
}


