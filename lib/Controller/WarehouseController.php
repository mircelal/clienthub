<?php

namespace OCA\DomainControl\Controller;

use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;
use Psr\Log\LoggerInterface;
use OCA\DomainControl\Service\WarehouseService;

class WarehouseController extends Controller
{
    private $service;
    private $logger;

    public function __construct($AppName, IRequest $request, WarehouseService $service, LoggerInterface $logger)
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
     * @param string $location
     * @param string $description
     */
    public function create($name, $location = '', $description = '')
    {
        return new DataResponse($this->service->create($name, $location, $description));
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
            $location = isset($data['location']) ? trim($data['location']) : '';
            $description = isset($data['description']) ? trim($data['description']) : '';
            
            if (empty($name)) {
                return new DataResponse(['error' => 'Name is required'], \OCP\AppFramework\Http::STATUS_BAD_REQUEST);
            }
            
            return new DataResponse($this->service->update($id, $name, $location, $description));
        } catch (\Exception $e) {
            $this->logger->error('WarehouseController::update error: ' . $e->getMessage(), ['app' => 'domaincontrol', 'exception' => $e]);
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
