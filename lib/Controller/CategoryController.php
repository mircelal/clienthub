<?php

namespace OCA\DomainControl\Controller;

use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;
use Psr\Log\LoggerInterface;
use OCA\DomainControl\Service\CategoryService;

class CategoryController extends Controller
{
    private $service;
    private $logger;

    public function __construct($AppName, IRequest $request, CategoryService $service, LoggerInterface $logger)
    {
        parent::__construct($AppName, $request);
        $this->service = $service;
        $this->logger = $logger;
    }

    /**
     * @NoAdminRequired
     */
    public function index()
    {
        return new DataResponse($this->service->findAll());
    }

    /**
     * @NoAdminRequired
     * 
     * @param string $name
     * @param string $description
     * @param int $parentId
     */
    public function create($name, $description = '', $parentId = 0)
    {
        return new DataResponse($this->service->create($name, $description, $parentId));
    }

    /**
     * @NoAdminRequired
     * 
     * @param int $id
     */
    public function update($id)
    {
        try {
            // Read request body
            $body = file_get_contents('php://input');
            parse_str($body, $data);
            
            // Extract parameters with defaults
            $name = isset($data['name']) ? trim($data['name']) : '';
            $description = isset($data['description']) ? trim($data['description']) : '';
            $parentId = isset($data['parentId']) && $data['parentId'] !== '' && $data['parentId'] !== null ? (int)$data['parentId'] : 0;
            
            if (empty($name)) {
                return new DataResponse(['error' => 'Name is required'], \OCP\AppFramework\Http::STATUS_BAD_REQUEST);
            }
            
            return new DataResponse($this->service->update($id, $name, $description, $parentId));
        } catch (\Exception $e) {
            $this->logger->error('CategoryController::update error: ' . $e->getMessage(), ['app' => 'domaincontrol', 'exception' => $e]);
            return new DataResponse(['error' => $e->getMessage()], \OCP\AppFramework\Http::STATUS_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @NoAdminRequired
     */
    public function destroy($id)
    {
        return new DataResponse($this->service->delete($id));
    }
}
