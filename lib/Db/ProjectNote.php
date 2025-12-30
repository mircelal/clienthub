<?php
declare(strict_types=1);

namespace OCA\DomainControl\Db;

use OCP\AppFramework\Db\Entity;

/**
 * @method int getProjectId()
 * @method void setProjectId(int $projectId)
 * @method string getUserId()
 * @method void setUserId(string $userId)
 * @method string getCategory()
 * @method void setCategory(string $category)
 * @method string getTitle()
 * @method void setTitle(string $title)
 * @method string getContent()
 * @method void setContent(string $content)
 * @method string getStatus()
 * @method void setStatus(string $status)
 * @method string getCreatedAt()
 * @method void setCreatedAt(string $createdAt)
 * @method string getUpdatedAt()
 * @method void setUpdatedAt(string $updatedAt)
 */
class ProjectNote extends Entity implements \JsonSerializable {
	protected $projectId;
	protected $userId;
	protected $category;
	protected $title;
	protected $content;
	protected $status;
	protected $createdAt;
	protected $updatedAt;

	public function __construct() {
		$this->addType('projectId', 'integer');
		$this->addType('userId', 'string');
		$this->addType('category', 'string');
		$this->addType('title', 'string');
		$this->addType('content', 'string');
		$this->addType('status', 'string');
		$this->addType('createdAt', 'string');
		$this->addType('updatedAt', 'string');
	}

	public function jsonSerialize(): array {
		return [
			'id' => $this->id,
			'projectId' => $this->projectId,
			'userId' => $this->userId,
			'category' => $this->category,
			'title' => $this->title,
			'content' => $this->content,
			'status' => $this->status,
			'createdAt' => $this->createdAt,
			'updatedAt' => $this->updatedAt,
		];
	}
}

