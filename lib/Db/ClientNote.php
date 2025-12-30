<?php
declare(strict_types=1);

namespace OCA\DomainControl\Db;

use OCP\AppFramework\Db\Entity;

/**
 * @method int getClientId()
 * @method void setClientId(int $clientId)
 * @method string getUserId()
 * @method void setUserId(string $userId)
 * @method string|null getTitle()
 * @method void setTitle(string|null $title)
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
	protected $title = null;
	protected $content;
	protected $createdAt;
	protected $updatedAt;

	public function __construct() {
		$this->addType('clientId', 'integer');
		$this->addType('userId', 'string');
		// title is nullable, so we don't use addType for it
		$this->addType('content', 'string');
		$this->addType('createdAt', 'string');
		$this->addType('updatedAt', 'string');
	}

	public function jsonSerialize(): array {
		return [
			'id' => $this->id,
			'clientId' => $this->clientId,
			'userId' => $this->userId,
			'title' => $this->title,
			'content' => $this->content,
			'date' => $this->createdAt, // Frontend compatibility
			'createdAt' => $this->createdAt,
			'updatedAt' => $this->updatedAt,
		];
	}
}

