<?php
declare(strict_types=1);

namespace OCA\DomainControl\Controller;

use OCA\DomainControl\AppInfo\Application;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;
use OCA\DomainControl\Db\TransactionCategoryMapper;
use OCA\DomainControl\Db\TransactionCategory;

class TransactionCategoryController extends Controller
{
	private $userId;
	private TransactionCategoryMapper $mapper;

	public function __construct(IRequest $request,
	                            TransactionCategoryMapper $mapper,
	                            $userId)
	{
		parent::__construct(Application::APP_ID, $request);
		$this->mapper = $mapper;
		$this->userId = $userId;
	}

	private function getRequestData(): array
	{
		$body = file_get_contents('php://input');
		parse_str($body, $data);
		return $data;
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function index(): JSONResponse
	{
		try {
			$categories = $this->mapper->findAll($this->userId);
			return new JSONResponse($categories);
		} catch (\Exception $e) {
			return new JSONResponse(['error' => $e->getMessage()], 500);
		}
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function byType(string $type): JSONResponse
	{
		try {
			$categories = $this->mapper->findByType($type, $this->userId);
			return new JSONResponse($categories);
		} catch (\Exception $e) {
			return new JSONResponse(['error' => $e->getMessage()], 500);
		}
	}

	/**
	 * @NoAdminRequired
	 */
	public function create(): JSONResponse
	{
		try {
			$data = $this->getRequestData();

			$name = $data['name'] ?? '';
			if (empty($name)) {
				return new JSONResponse(['error' => 'Name is required'], 400);
			}

			$type = $data['type'] ?? '';
			if (!in_array($type, ['income', 'expense'])) {
				return new JSONResponse(['error' => 'Type must be income or expense'], 400);
			}

			$category = new TransactionCategory();
			$category->setName($name);
			$category->setType($type);
			$category->setIcon($data['icon'] ?? null);
			$category->setColor($data['color'] ?? null);
			$category->setUserId($this->userId);
			$category->setIsPredefined(false);
			$now = date('Y-m-d H:i:s');
			$category->setCreatedAt($now);
			$category->setUpdatedAt($now);

			$category = $this->mapper->insert($category);
			return new JSONResponse($category);
		} catch (\Exception $e) {
			return new JSONResponse(['error' => $e->getMessage()], 500);
		}
	}

	/**
	 * @NoAdminRequired
	 */
	public function update(int $id): JSONResponse
	{
		try {
			$category = $this->mapper->find($id, $this->userId);
			$data = $this->getRequestData();

			if (isset($data['name']) && $data['name'] !== '') {
				$category->setName($data['name']);
			}
			if (isset($data['icon'])) {
				$category->setIcon($data['icon']);
			}
			if (isset($data['color'])) {
				$category->setColor($data['color']);
			}

			$category->setUpdatedAt(date('Y-m-d H:i:s'));

			$category = $this->mapper->update($category);
			return new JSONResponse($category);
		} catch (\Exception $e) {
			return new JSONResponse(['error' => $e->getMessage()], 500);
		}
	}

	/**
	 * @NoAdminRequired
	 */
	public function delete(int $id): JSONResponse
	{
		try {
			$category = $this->mapper->find($id, $this->userId);
			
			// Don't allow deleting predefined categories
			if ($category->getIsPredefined()) {
				return new JSONResponse(['error' => 'Cannot delete predefined category'], 400);
			}

			$this->mapper->delete($category);
			return new JSONResponse(['success' => true]);
		} catch (\Exception $e) {
			return new JSONResponse(['error' => $e->getMessage()], 500);
		}
	}
}

