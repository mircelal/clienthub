<?php
declare(strict_types=1);

namespace OCA\DomainControl\Controller;

use OCA\DomainControl\AppInfo\Application;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;
use OCA\DomainControl\Db\HostingPackageMapper;
use OCA\DomainControl\Db\HostingPackage;

class HostingPackageController extends Controller {
	private $userId;
	private HostingPackageMapper $mapper;

	public function __construct(IRequest $request,
	                            HostingPackageMapper $mapper,
	                            $userId) {
		parent::__construct(Application::APP_ID, $request);
		$this->mapper = $mapper;
		$this->userId = $userId;
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function index(): JSONResponse {
		try {
			$packages = $this->mapper->findAll($this->userId);
			return new JSONResponse($packages);
		} catch (\Exception $e) {
			return new JSONResponse(['error' => $e->getMessage()], 500);
		}
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function active(): JSONResponse {
		try {
			$packages = $this->mapper->findActive($this->userId);
			return new JSONResponse($packages);
		} catch (\Exception $e) {
			return new JSONResponse(['error' => $e->getMessage()], 500);
		}
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function show(int $id): JSONResponse {
		try {
			$package = $this->mapper->find($id, $this->userId);
			return new JSONResponse($package);
		} catch (\Exception $e) {
			return new JSONResponse(['error' => 'Package not found'], 404);
		}
	}

	/**
	 * @NoAdminRequired
	 */
	public function create(): JSONResponse {
		try {
			$body = file_get_contents('php://input');
			parse_str($body, $data);
			
			$name = $data['name'] ?? '';
			if (empty($name)) {
				return new JSONResponse(['error' => 'Package name is required'], 400);
			}
			
			$package = new HostingPackage();
			$package->setName($name);
			$package->setProvider($data['provider'] ?? '');
			$package->setPriceMonthly((float)($data['priceMonthly'] ?? 0));
			$package->setPriceYearly((float)($data['priceYearly'] ?? 0));
			$package->setCurrency($data['currency'] ?? 'USD');
			$package->setDiskSpaceGb((int)($data['diskSpaceGb'] ?? 0));
			$package->setTrafficGb((int)($data['trafficGb'] ?? 0));
			$package->setBandwidthUnlimited((bool)($data['bandwidthUnlimited'] ?? false));
			$package->setDomainsAllowed((int)($data['domainsAllowed'] ?? 1));
			$package->setDatabasesAllowed((int)($data['databasesAllowed'] ?? 0));
			$package->setEmailAccounts((int)($data['emailAccounts'] ?? 0));
			$package->setFtpAccounts((int)($data['ftpAccounts'] ?? 0));
			$package->setSslIncluded((bool)($data['sslIncluded'] ?? false));
			$package->setBackupIncluded((bool)($data['backupIncluded'] ?? false));
			$package->setDescription($data['description'] ?? '');
			$package->setIsActive((bool)($data['isActive'] ?? true));
			$package->setUserId($this->userId);
			$now = date('Y-m-d H:i:s');
			$package->setCreatedAt($now);
			$package->setUpdatedAt($now);
			
			$package = $this->mapper->insert($package);
			return new JSONResponse($package);
		} catch (\Exception $e) {
			return new JSONResponse(['error' => $e->getMessage()], 500);
		}
	}

	/**
	 * @NoAdminRequired
	 */
	public function update(int $id): JSONResponse {
		try {
			$package = $this->mapper->find($id, $this->userId);
			
			$body = file_get_contents('php://input');
			parse_str($body, $data);
			
			if (isset($data['name']) && $data['name'] !== '') $package->setName($data['name']);
			if (isset($data['provider'])) $package->setProvider($data['provider']);
			if (isset($data['priceMonthly'])) $package->setPriceMonthly((float)$data['priceMonthly']);
			if (isset($data['priceYearly'])) $package->setPriceYearly((float)$data['priceYearly']);
			if (isset($data['currency'])) $package->setCurrency($data['currency']);
			if (isset($data['diskSpaceGb'])) $package->setDiskSpaceGb((int)$data['diskSpaceGb']);
			if (isset($data['trafficGb'])) $package->setTrafficGb((int)$data['trafficGb']);
			if (isset($data['bandwidthUnlimited'])) $package->setBandwidthUnlimited((bool)$data['bandwidthUnlimited']);
			if (isset($data['domainsAllowed'])) $package->setDomainsAllowed((int)$data['domainsAllowed']);
			if (isset($data['databasesAllowed'])) $package->setDatabasesAllowed((int)$data['databasesAllowed']);
			if (isset($data['emailAccounts'])) $package->setEmailAccounts((int)$data['emailAccounts']);
			if (isset($data['ftpAccounts'])) $package->setFtpAccounts((int)$data['ftpAccounts']);
			if (isset($data['sslIncluded'])) $package->setSslIncluded((bool)$data['sslIncluded']);
			if (isset($data['backupIncluded'])) $package->setBackupIncluded((bool)$data['backupIncluded']);
			if (isset($data['description'])) $package->setDescription($data['description']);
			if (isset($data['isActive'])) $package->setIsActive((bool)$data['isActive']);
			
			$now = date('Y-m-d H:i:s');
			$package->setUpdatedAt($now);
			
			$package = $this->mapper->update($package);
			return new JSONResponse($package);
		} catch (\Exception $e) {
			return new JSONResponse(['error' => $e->getMessage()], 500);
		}
	}

	/**
	 * @NoAdminRequired
	 */
	public function delete(int $id): JSONResponse {
		try {
			$package = $this->mapper->find($id, $this->userId);
			$this->mapper->delete($package);
			return new JSONResponse(['success' => true]);
		} catch (\Exception $e) {
			return new JSONResponse(['error' => $e->getMessage()], 500);
		}
	}
}

