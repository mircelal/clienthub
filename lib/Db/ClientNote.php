<?php
declare(strict_types=1);

namespace OCA\DomainControl\Db;

use OCP\AppFramework\Db\Entity;

/**
 * @method int getClientId()
 * @method void setClientId(int $clientId)
 * @method string getUserId()
 * @method void setUserId(string $userId)
 * @method string getContent()
 * @method void setContent(string $content)
 * @method string getCreatedAt()
 * @method void setCreatedAt(string $createdAt)
 * @method string getUpdatedAt()
 * @method void setUpdatedAt(string $updatedAt)
 */
class ClientNote extends Entity implements \JsonSerializable {
	protected $clientId;
	protected $userId;
	protected $content;
	protected $createdAt;
	protected $updatedAt;

	public function __construct() {
		$this->addType('clientId', 'integer');
		$this->addType('userId', 'string');
		$this->addType('content', 'string');
		$this->addType('createdAt', 'string');
		$this->addType('updatedAt', 'string');
	}

	public function jsonSerialize(): array {
		return [
			'id' => $this->id,
			'clientId' => $this->clientId,
			'userId' => $this->userId,
			'content' => $this->content,
			'date' => $this->createdAt, // Frontend compatibility
			'createdAt' => $this->createdAt,
			'updatedAt' => $this->updatedAt,
		];
	}
}

