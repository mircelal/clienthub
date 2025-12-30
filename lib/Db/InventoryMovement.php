<?php
namespace OCA\DomainControl\Db;

use OCP\AppFramework\Db\Entity;

/**
 * @method int getInventoryId()
 * @method void setInventoryId(int $inventoryId)
 * @method int getClientId()
 * @method void setClientId(int $clientId)
 * @method int getProjectId()
 * @method void setProjectId(int $projectId)
 * @method int getOrderId()
 * @method void setOrderId(int $orderId)
 * @method string getType()
 * @method void setType(string $type)
 * @method string getDateOut()
 * @method void setDateOut(string $dateOut)
 * @method string getDateDue()
 * @method void setDateDue(string $dateDue)
 * @method string getDateReturned()
 * @method void setDateReturned(string $dateReturned)
 * @method float getPrice()
 * @method void setPrice(float $price)
 * @method string getNotes()
 * @method void setNotes(string $notes)
 */
class InventoryMovement extends Entity implements \JsonSerializable
{
    protected $inventoryId;
    protected $clientId;
    protected $projectId;
    protected $orderId;
    protected $type;
    protected $dateOut;
    protected $dateDue;
    protected $dateReturned;
    protected $price;
    protected $notes;

    public function __construct()
    {
        $this->addType('inventoryId', 'integer');
        $this->addType('clientId', 'integer');
        $this->addType('projectId', 'integer');
        $this->addType('orderId', 'integer');
        $this->addType('price', 'float');
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'inventoryId' => $this->inventoryId,
            'clientId' => $this->clientId ?? 0,
            'projectId' => $this->projectId ?? 0,
            'orderId' => $this->orderId ?? null,
            'type' => $this->type,
            'dateOut' => $this->dateOut,
            'dateDue' => $this->dateDue ?? null,
            'dateReturned' => $this->dateReturned ?? null,
            'price' => $this->price ?? 0,
            'notes' => $this->notes ?? '',
        ];
    }
}

