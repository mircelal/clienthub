<?php

namespace OCA\DomainControl\Service;

use Exception;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;
use OCA\DomainControl\Db\Warehouse;
use OCA\DomainControl\Db\WarehouseMapper;
use OCA\DomainControl\Service\NotFoundException;
use Psr\Log\LoggerInterface;

class WarehouseService
{
    private $mapper;
    private $logger;

    public function __construct(WarehouseMapper $mapper, LoggerInterface $logger)
    {
        $this->mapper = $mapper;
        $this->logger = $logger;
    }

    public function findAll()
    {
        return $this->mapper->findAll();
    }

    public function create($name, $location, $description)
    {
        $warehouse = new Warehouse();
        $warehouse->setName($name);
        $warehouse->setLocation($location);
        $warehouse->setDescription($description);
        return $this->mapper->insert($warehouse);
    }

    public function update($id, $name, $location, $description)
    {
        try {
            if (empty($name)) {
                throw new Exception('Warehouse name cannot be empty');
            }
            
            $warehouse = $this->mapper->find($id);
            if (!$warehouse) {
                throw new NotFoundException('Warehouse not found');
            }
            
            $warehouse->setName($name);
            $warehouse->setLocation($location ?? '');
            $warehouse->setDescription($description ?? '');
            
            $updated = $this->mapper->update($warehouse);
            $this->logger->debug('WarehouseService::update success - ID: ' . $id . ', Name: ' . $name, ['app' => 'domaincontrol']);
            return $updated;
        } catch (Exception $e) {
            $this->logger->error('WarehouseService::update error: ' . $e->getMessage() . ' - ID: ' . $id . ' - Name: ' . $name, ['app' => 'domaincontrol', 'exception' => $e]);
            $this->handleException($e);
        }
    }

    public function delete($id)
    {
        try {
            $warehouse = $this->mapper->find($id);
            $this->mapper->delete($warehouse);
            return $warehouse;
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }

    private function handleException($e)
    {
        if (
            $e instanceof DoesNotExistException ||
            $e instanceof MultipleObjectsReturnedException
        ) {
            throw new NotFoundException($e->getMessage());
        } else {
            throw $e;
        }
    }
}
