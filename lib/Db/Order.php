<?php
namespace OCA\DomainControl\Db;

use OCP\AppFramework\Db\Entity;

/**
 * @method string getOrderNumber()
 * @method void setOrderNumber(string $orderNumber)
 * @method int getClientId()
 * @method void setClientId(int $clientId)
 * @method string getType()
 * @method void setType(string $type)
 * @method string getOrderDate()
 * @method void setOrderDate(string $orderDate)
 * @method float getTotalAmount()
 * @method void setTotalAmount(float $totalAmount)
 * @method string getStatus()
 * @method void setStatus(string $status)
 * @method string getNotes()
 * @method void setNotes(string $notes)
 * @method string getCreatedAt()
 * @method void setCreatedAt(string $createdAt)
 * @method string getUpdatedAt()
 * @method void setUpdatedAt(string $updatedAt)
 */
class Order extends Entity implements \JsonSerializable
{
    protected $orderNumber;
    protected $clientId;
    protected $type;
    protected $orderDate;
    protected $totalAmount;
    protected $status;
    protected $notes;
    protected $createdAt;
    protected $updatedAt;

    public function __construct()
    {
        $this->addType('clientId', 'integer');
        $this->addType('totalAmount', 'float');
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'orderNumber' => $this->orderNumber,
            'clientId' => $this->clientId,
            'type' => $this->type,
            'orderDate' => $this->orderDate,
            'totalAmount' => $this->totalAmount ?? 0,
            'status' => $this->status ?? 'pending',
            'notes' => $this->notes ?? '',
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
        ];
    }
}


