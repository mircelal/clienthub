<?php

namespace OCA\DomainControl\Db;

use OCP\AppFramework\Db\Entity;

/**
 * @method string getName()
 * @method void setName(string $name)
 * @method string getLocation()
 * @method void setLocation(string $location)
 * @method string getDescription()
 * @method void setDescription(string $description)
 */
class Warehouse extends Entity implements \JsonSerializable
{
    protected $name;
    protected $location;
    protected $description;

    public function __construct()
    {
        $this->addType('name', 'string');
        $this->addType('location', 'string');
        $this->addType('description', 'string');
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'location' => $this->location ?? '',
            'description' => $this->description ?? '',
        ];
    }
}
