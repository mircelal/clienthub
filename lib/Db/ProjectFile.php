<?php
declare(strict_types=1);

namespace OCA\DomainControl\Db;

use OCP\AppFramework\Db\Entity;

/**
 * @method int getProjectId()
 * @method void setProjectId(int $projectId)
 * @method string getUserId()
 * @method void setUserId(string $userId)
 * @method string getFilePath()
 * @method void setFilePath(string $filePath)
 * @method string getFileName()
 * @method void setFileName(string $fileName)
 * @method int getFileSize()
 * @method void setFileSize(int $fileSize)
 * @method string getMimeType()
 * @method void setMimeType(string $mimeType)
 * @method string getCategory()
 * @method void setCategory(string $category)
 * @method string getDescription()
 * @method void setDescription(string $description)
 * @method string getCreatedAt()
 * @method void setCreatedAt(string $createdAt)
 * @method string getUpdatedAt()
 * @method void setUpdatedAt(string $updatedAt)
 */
class ProjectFile extends Entity implements \JsonSerializable {
	protected $projectId;
	protected $userId;
	protected $filePath;
	protected $fileName;
	protected $fileSize;
	protected $mimeType;
	protected $category;
	protected $description;
	protected $createdAt;
	protected $updatedAt;

	public function __construct() {
		$this->addType('projectId', 'integer');
		$this->addType('userId', 'string');
		$this->addType('filePath', 'string');
		$this->addType('fileName', 'string');
		$this->addType('fileSize', 'integer');
		$this->addType('mimeType', 'string');
		$this->addType('category', 'string');
		$this->addType('description', 'string');
		$this->addType('createdAt', 'string');
		$this->addType('updatedAt', 'string');
	}

	public function jsonSerialize(): array {
		return [
			'id' => $this->id,
			'projectId' => $this->projectId,
			'userId' => $this->userId,
			'filePath' => $this->filePath,
			'fileName' => $this->fileName,
			'fileSize' => $this->fileSize,
			'mimeType' => $this->mimeType,
			'category' => $this->category,
			'description' => $this->description,
			'createdAt' => $this->createdAt,
			'updatedAt' => $this->updatedAt,
		];
	}
}

