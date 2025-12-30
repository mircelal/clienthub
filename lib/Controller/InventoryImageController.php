<?php

namespace OCA\DomainControl\Controller;

use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;
use OCA\DomainControl\Service\InventoryImageService;

class InventoryImageController extends Controller
{
    private $service;

    public function __construct($AppName, IRequest $request, InventoryImageService $service)
    {
        parent::__construct($AppName, $request);
        $this->service = $service;
    }

    /**
     * @NoAdminRequired
     */
    public function index($inventoryId)
    {
        return new DataResponse($this->service->findByInventoryId($inventoryId));
    }

    /**
     * @NoAdminRequired
     */
    public function upload($inventoryId)
    {
        try {
            if (!isset($_FILES['file'])) {
                \OCP\Util::writeLog('domaincontrol', 'InventoryImageController::upload - No file uploaded', \OCP\Util::ERROR);
                return new JSONResponse(['error' => 'No file uploaded'], 400);
            }

            $uploadedFile = $_FILES['file'];
            
            if ($uploadedFile['error'] !== UPLOAD_ERR_OK) {
                \OCP\Util::writeLog('domaincontrol', 'InventoryImageController::upload - File upload error: ' . $uploadedFile['error'], \OCP\Util::ERROR);
                return new JSONResponse(['error' => 'File upload failed'], 400);
            }

            $image = $this->service->uploadImage($inventoryId, $uploadedFile);
            return new DataResponse($image);
        } catch (\Exception $e) {
            \OCP\Util::writeLog('domaincontrol', 'InventoryImageController::upload error: ' . $e->getMessage() . ' - Trace: ' . $e->getTraceAsString(), \OCP\Util::ERROR);
            return new JSONResponse(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * @NoAdminRequired
     */
    public function setPrimary($id)
    {
        try {
            $image = $this->service->setPrimary($id);
            return new DataResponse($image);
        } catch (\Exception $e) {
            \OCP\Util::writeLog('domaincontrol', 'InventoryImageController::setPrimary error: ' . $e->getMessage() . ' - ID: ' . $id . ' - Trace: ' . $e->getTraceAsString(), \OCP\Util::ERROR);
            return new JSONResponse(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * @NoAdminRequired
     */
    public function updateOrder($inventoryId)
    {
        try {
            $data = $this->request->getParams();
            $order = isset($data['order']) ? json_decode($data['order'], true) : [];
            $this->service->updateOrder($inventoryId, $order);
            return new DataResponse(['success' => true]);
        } catch (\Exception $e) {
            return new JSONResponse(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * @NoAdminRequired
     */
    public function destroy($id)
    {
        try {
            $this->service->delete($id);
            return new DataResponse(['success' => true]);
        } catch (\Exception $e) {
            return new JSONResponse(['error' => $e->getMessage()], 500);
        }
    }
}


