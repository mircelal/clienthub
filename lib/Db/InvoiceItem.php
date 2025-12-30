<?php
declare(strict_types=1);

namespace OCA\DomainControl\Db;

use OCP\AppFramework\Db\Entity;

/**
 * @method int getInvoiceId()
 * @method void setInvoiceId(int $invoiceId)
 * @method string getItemType()
 * @method void setItemType(string $itemType)
 * @method int getItemId()
 * @method void setItemId(int $itemId)
 * @method string getDescription()
 * @method void setDescription(string $description)
 * @method int getQuantity()
 * @method void setQuantity(int $quantity)
 * @method float getUnitPrice()
 * @method void setUnitPrice(float $unitPrice)
 * @method float getTotalPrice()
 * @method void setTotalPrice(float $totalPrice)
 * @method string getCurrency()
 * @method void setCurrency(string $currency)
 * @method string getPeriodStart()
 * @method void setPeriodStart(string $periodStart)
 * @method string getPeriodEnd()
 * @method void setPeriodEnd(string $periodEnd)
 * @method float getDiscount()
 * @method void setDiscount(float $discount)
 * @method string getDiscountType()
 * @method void setDiscountType(string $discountType)
 * @method string getCreatedAt()
 * @method void setCreatedAt(string $createdAt)
 */
class InvoiceItem extends Entity implements \JsonSerializable {
	protected $invoiceId;
	protected $itemType;
	protected $itemId;
	protected $description;
	protected $quantity;
	protected $unitPrice;
	protected $totalPrice;
	protected $currency;
	protected $periodStart;
	protected $periodEnd;
	protected $discount;
	protected $discountType;
	protected $createdAt;

	public function __construct() {
		$this->addType('invoiceId', 'integer');
		$this->addType('itemType', 'string');
		$this->addType('itemId', 'integer');
		$this->addType('description', 'string');
		$this->addType('quantity', 'integer');
		$this->addType('unitPrice', 'float');
		$this->addType('totalPrice', 'float');
		$this->addType('currency', 'string');
		$this->addType('periodStart', 'string');
		$this->addType('periodEnd', 'string');
		$this->addType('discount', 'float');
		$this->addType('discountType', 'string');
		$this->addType('createdAt', 'string');
	}

	public function jsonSerialize(): array {
		return [
			'id' => $this->id,
			'invoiceId' => $this->invoiceId,
			'itemType' => $this->itemType,
			'itemId' => $this->itemId,
			'description' => $this->description,
			'quantity' => $this->quantity,
			'unitPrice' => $this->unitPrice,
			'totalPrice' => $this->totalPrice,
			'currency' => $this->currency,
			'periodStart' => $this->periodStart,
			'periodEnd' => $this->periodEnd,
			'discount' => $this->discount,
			'discountType' => $this->discountType,
			'createdAt' => $this->createdAt,
		];
	}
}

