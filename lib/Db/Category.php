<?php

namespace OCA\DomainControl\Db;

use OCP\AppFramework\Db\Entity;

/**
 * @method string getName()
 * @method void setName(string $name)
 * @method int getParentId()
 * @method void setParentId(int $parentId)
 * @method string getDescription()
 * @method void setDescription(string $description)
 */
class Category extends Entity implements \JsonSerializable
{
    protected $name;
    protected $parentId;
    protected $description;

    public function __construct()
    {
        $this->addType('name', 'string');
        $this->addType('parentId', 'integer');
        $this->addType('description', 'string');
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'parentId' => $this->parentId ?? 0,
            'description' => $this->description ?? '',
        ];
    }
}
