<?php

namespace OCA\DomainControl\Service;

use Exception;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;
use OCA\DomainControl\Db\Category;
use OCA\DomainControl\Db\CategoryMapper;
use OCA\DomainControl\Service\NotFoundException;
use Psr\Log\LoggerInterface;

class CategoryService
{
    private $mapper;
    private $logger;

    public function __construct(CategoryMapper $mapper, LoggerInterface $logger)
    {
        $this->mapper = $mapper;
        $this->logger = $logger;
    }

    public function findAll()
    {
        return $this->mapper->findAll();
    }

    public function create($name, $description, $parentId = 0)
    {
        $category = new Category();
        $category->setName($name);
        $category->setDescription($description);
        $category->setParentId((int) $parentId);
        return $this->mapper->insert($category);
    }

    public function update($id, $name, $description, $parentId = 0)
    {
        try {
            if (empty($name)) {
                throw new Exception('Category name cannot be empty');
            }
            
            $category = $this->mapper->find($id);
            if (!$category) {
                throw new NotFoundException('Category not found');
            }
            
            $category->setName($name);
            $category->setDescription($description ?? '');
            $category->setParentId((int) $parentId);
            
            $updated = $this->mapper->update($category);
            $this->logger->debug('CategoryService::update success - ID: ' . $id . ', Name: ' . $name, ['app' => 'domaincontrol']);
            return $updated;
        } catch (Exception $e) {
            $this->logger->error('CategoryService::update error: ' . $e->getMessage() . ' - ID: ' . $id . ' - Name: ' . $name . ' - ParentId: ' . $parentId, ['app' => 'domaincontrol', 'exception' => $e]);
            $this->handleException($e);
        }
    }

    public function delete($id)
    {
        try {
            $category = $this->mapper->find($id);
            $this->mapper->delete($category);
            return $category;
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
