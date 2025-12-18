<?php
declare(strict_types=1);

namespace OCA\DomainControl\AppInfo;

use OCP\AppFramework\App;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\AppFramework\Bootstrap\IRegistrationContext;

class Application extends App implements IBootstrap {
	public const APP_ID = 'domaincontrol';

	public function __construct(array $urlParams = []) {
		parent::__construct(self::APP_ID, $urlParams);
	}

	public function register(IRegistrationContext $context): void {
		// Controllers and services are auto-registered via dependency injection
	}

	public function boot(IBootContext $context): void {
		// Boot logic if needed
	}
}

