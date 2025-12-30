<?php
declare(strict_types=1);

namespace OCA\DomainControl\AppInfo;

use OCP\AppFramework\App;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\AppFramework\Bootstrap\IRegistrationContext;
use OCP\IDBConnection;
use OCP\Files\IRootFolder;
use Psr\Container\ContainerInterface;
use OCA\DomainControl\Db\InventoryMapper;
use OCA\DomainControl\Service\InventoryService;
use OCA\DomainControl\Db\InventoryImageMapper;
use OCA\DomainControl\Service\InventoryImageService;
use OCA\DomainControl\Db\CategoryMapper;
use OCA\DomainControl\Service\CategoryService;
use OCA\DomainControl\Db\WarehouseMapper;
use OCA\DomainControl\Service\WarehouseService;
use OCA\DomainControl\Db\InventoryMovementMapper;
use OCA\DomainControl\Service\POSService;
use OCA\DomainControl\Db\ClientMapper;
use OCA\DomainControl\Db\OrderMapper;

class Application extends App implements IBootstrap
{
	public const APP_ID = 'domaincontrol';

	public function __construct(array $urlParams = [])
	{
		parent::__construct(self::APP_ID, $urlParams);
	}

	public function register(IRegistrationContext $context): void
	{
		// Inventory
		$context->registerService(InventoryMapper::class, function (ContainerInterface $c) {
			return new InventoryMapper(
				$c->get(IDBConnection::class)
			);
		});
		$context->registerService(InventoryService::class, function (ContainerInterface $c) {
			return new InventoryService(
				$c->get(InventoryMapper::class),
				$c->get(IRootFolder::class)
			);
		});

		// Inventory Images
		$context->registerService(InventoryImageMapper::class, function (ContainerInterface $c) {
			return new InventoryImageMapper(
				$c->get(IDBConnection::class)
			);
		});
		$context->registerService(InventoryImageService::class, function (ContainerInterface $c) {
			return new InventoryImageService(
				$c->get(InventoryImageMapper::class),
				$c->get(IRootFolder::class)
			);
		});

		// Categories
		$context->registerService(CategoryMapper::class, function (ContainerInterface $c) {
			return new CategoryMapper(
				$c->get(IDBConnection::class)
			);
		});
		$context->registerService(CategoryService::class, function (ContainerInterface $c) {
			return new CategoryService(
				$c->get(CategoryMapper::class),
				$c->get(\Psr\Log\LoggerInterface::class)
			);
		});

		// Warehouses
		$context->registerService(WarehouseMapper::class, function (ContainerInterface $c) {
			return new WarehouseMapper(
				$c->get(IDBConnection::class)
			);
		});
		$context->registerService(WarehouseService::class, function (ContainerInterface $c) {
			return new WarehouseService(
				$c->get(WarehouseMapper::class),
				$c->get(\Psr\Log\LoggerInterface::class)
			);
		});

		// POS (Point of Sale)
		$context->registerService(InventoryMovementMapper::class, function (ContainerInterface $c) {
			return new InventoryMovementMapper(
				$c->get(IDBConnection::class)
			);
		});
		$context->registerService(OrderMapper::class, function (ContainerInterface $c) {
			return new OrderMapper(
				$c->get(IDBConnection::class)
			);
		});
		$context->registerService(POSService::class, function (ContainerInterface $c) {
			return new POSService(
				$c->get(InventoryMovementMapper::class),
				$c->get(InventoryMapper::class),
				$c->get(ClientMapper::class),
				$c->get(OrderMapper::class),
				$c->get(\Psr\Log\LoggerInterface::class)
			);
		});
	}

	public function boot(IBootContext $context): void
	{
		// Boot logic if needed
	}
}

