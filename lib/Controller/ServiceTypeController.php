<?php
declare(strict_types=1);

namespace OCA\DomainControl\Controller;

use OCA\DomainControl\AppInfo\Application;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;
use OCA\DomainControl\Db\ServiceTypeMapper;
use OCA\DomainControl\Db\ServiceType;

class ServiceTypeController extends Controller {
	private $userId;
	private ServiceTypeMapper $mapper;

	public function __construct(IRequest $request,
	                            ServiceTypeMapper $mapper,
	                            $userId) {
		parent::__construct(Application::APP_ID, $request);
		$this->mapper = $mapper;
		$this->userId = $userId;
	}

	private function getRequestData(): array {
		$body = file_get_contents('php://input');
		parse_str($body, $data);
		return $data;
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function index(): JSONResponse {
		try {
			$types = $this->mapper->findAll($this->userId);
			return new JSONResponse($types);
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
			$type = $this->mapper->find($id, $this->userId);
			return new JSONResponse($type);
		} catch (\Exception $e) {
			return new JSONResponse(['error' => 'Service type not found'], 404);
		}
	}

	/**
	 * @NoAdminRequired
	 */
	public function create(): JSONResponse {
		try {
			$data = $this->getRequestData();
			
			$name = $data['name'] ?? '';
			if (empty($name)) {
				return new JSONResponse(['error' => 'Name is required'], 400);
			}
			
			$type = new ServiceType();
			$type->setName($name);
			$type->setDescription($data['description'] ?? '');
			$type->setDefaultPrice((float)($data['defaultPrice'] ?? 0));
			$type->setDefaultCurrency($data['defaultCurrency'] ?? 'USD');
			$type->setRenewalInterval($data['renewalInterval'] ?? 'monthly');
			$type->setIsPredefined(isset($data['isPredefined']) && $data['isPredefined'] === 'true' ? 1 : 0);
			$type->setUserId($this->userId);
			$now = date('Y-m-d H:i:s');
			$type->setCreatedAt($now);
			$type->setUpdatedAt($now);
			
			$type = $this->mapper->insert($type);
			return new JSONResponse($type);
		} catch (\Exception $e) {
			return new JSONResponse(['error' => $e->getMessage()], 500);
		}
	}

	/**
	 * @NoAdminRequired
	 */
	public function update(int $id): JSONResponse {
		try {
			$type = $this->mapper->find($id, $this->userId);
			$data = $this->getRequestData();
			
			if (isset($data['name']) && $data['name'] !== '') $type->setName($data['name']);
			if (isset($data['description'])) $type->setDescription($data['description']);
			if (isset($data['defaultPrice'])) $type->setDefaultPrice((float)$data['defaultPrice']);
			if (isset($data['defaultCurrency'])) $type->setDefaultCurrency($data['defaultCurrency']);
			if (isset($data['renewalInterval'])) $type->setRenewalInterval($data['renewalInterval']);
			
			$type->setUpdatedAt(date('Y-m-d H:i:s'));
			
			$type = $this->mapper->update($type);
			return new JSONResponse($type);
		} catch (\Exception $e) {
			return new JSONResponse(['error' => $e->getMessage()], 500);
		}
	}

	/**
	 * @NoAdminRequired
	 */
	public function delete(int $id): JSONResponse {
		try {
			$type = $this->mapper->find($id, $this->userId);
			$this->mapper->delete($type);
			return new JSONResponse(['success' => true]);
		} catch (\Exception $e) {
			return new JSONResponse(['error' => $e->getMessage()], 500);
		}
	}

	/**
	 * @NoAdminRequired
	 */
	public function initPredefined(): JSONResponse {
		try {
			$predefined = [
				['name' => 'Bakım', 'description' => 'Aylık/yıllık bakım hizmeti', 'renewalInterval' => 'monthly'],
				['name' => 'SEO', 'description' => 'Arama motoru optimizasyonu', 'renewalInterval' => 'monthly'],
				['name' => 'Mail Hizmeti', 'description' => 'E-posta barındırma', 'renewalInterval' => 'yearly'],
				['name' => 'SSL Sertifikası', 'description' => 'SSL sertifikası', 'renewalInterval' => 'yearly'],
				['name' => 'Yedekleme', 'description' => 'Yedekleme hizmeti', 'renewalInterval' => 'monthly'],
				['name' => 'Güvenlik', 'description' => 'Güvenlik taraması ve koruma', 'renewalInterval' => 'monthly'],
				['name' => 'Destek', 'description' => 'Teknik destek hizmeti', 'renewalInterval' => 'monthly'],
			];
			
			$created = [];
			$now = date('Y-m-d H:i:s');
			
			foreach ($predefined as $item) {
				$type = new ServiceType();
				$type->setName($item['name']);
				$type->setDescription($item['description']);
				$type->setDefaultPrice(0);
				$type->setDefaultCurrency('USD');
				$type->setRenewalInterval($item['renewalInterval']);
				$type->setIsPredefined(1);
				$type->setUserId($this->userId);
				$type->setCreatedAt($now);
				$type->setUpdatedAt($now);
				
				$created[] = $this->mapper->insert($type);
			}
			
			return new JSONResponse(['success' => true, 'created' => $created]);
		} catch (\Exception $e) {
			return new JSONResponse(['error' => $e->getMessage()], 500);
		}
	}
}

