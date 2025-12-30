<?php
declare(strict_types=1);

namespace OCA\DomainControl\Controller;

use OCA\DomainControl\AppInfo\Application;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\JSONResponse;
use OCP\AppFramework\Http\DataDownloadResponse;
use OCP\IRequest;
use OCP\Files\IRootFolder;
use OCP\Files\Folder;
use OCP\Files\File;
use OCP\Files\NotFoundException;
use OCP\Files\NotPermittedException;
use OCA\DomainControl\Db\ClientMapper;

class ClientFileController extends Controller {
	private $userId;
	private IRootFolder $rootFolder;
	private ClientMapper $clientMapper;

	public function __construct(
		IRequest $request,
		IRootFolder $rootFolder,
		ClientMapper $clientMapper,
		$userId
	) {
		parent::__construct(Application::APP_ID, $request);
		$this->rootFolder = $rootFolder;
		$this->clientMapper = $clientMapper;
		$this->userId = $userId;
	}

	/**
	 * Get or create base clients folder
	 */
	private function getClientsFolder(): Folder {
		$userFolder = $this->rootFolder->getUserFolder($this->userId);
		
		try {
			$clientsFolder = $userFolder->get('clients');
			if ($clientsFolder instanceof Folder) {
				return $clientsFolder;
			}
		} catch (NotFoundException $e) {
			// Folder doesn't exist, create it
		}
		
		return $userFolder->newFolder('clients');
	}

	/**
	 * Get or create client folder
	 */
	private function getClientFolder(int $clientId): Folder {
		$client = $this->clientMapper->find($clientId, $this->userId);
		$clientsFolder = $this->getClientsFolder();
		
		// Sanitize client name for folder name
		$safeName = $this->sanitizeFolderName($client->getName());
		
		try {
			$clientFolder = $clientsFolder->get($safeName);
			if ($clientFolder instanceof Folder) {
				return $clientFolder;
			}
		} catch (NotFoundException $e) {
			// Folder doesn't exist, create it
		}
		
		return $clientsFolder->newFolder($safeName);
	}

	/**
	 * Sanitize folder name
	 */
	private function sanitizeFolderName(string $name): string {
		// Remove special characters, keep only alphanumeric, spaces, hyphens, underscores
		$name = preg_replace('/[^a-zA-Z0-9\s_-]/', '', $name);
		// Replace spaces with underscores
		$name = str_replace(' ', '_', $name);
		// Remove multiple underscores
		$name = preg_replace('/_+/', '_', $name);
		// Trim underscores from start and end
		$name = trim($name, '_');
		return $name ?: 'client_' . time();
	}

	/**
	 * @NoAdminRequired
	 */
	public function index(int $clientId): JSONResponse {
		try {
			$clientFolder = $this->getClientFolder($clientId);
			$userFolder = $this->rootFolder->getUserFolder($this->userId);
			
			$files = [];
			foreach ($clientFolder->getDirectoryListing() as $node) {
				if ($node instanceof File) {
					$relativePath = $userFolder->getRelativePath($node->getPath());
					$files[] = [
						'id' => $node->getId(),
						'name' => $node->getName(),
						'size' => $node->getSize(),
						'mimeType' => $node->getMimeType(),
						'path' => $relativePath,
						'mtime' => $node->getMTime(),
						'etag' => $node->getEtag(),
					];
				}
			}
			
			// Sort by name
			usort($files, function($a, $b) {
				return strcmp($a['name'], $b['name']);
			});
			
			return new JSONResponse($files);
		} catch (\Exception $e) {
			return new JSONResponse(['error' => $e->getMessage()], 500);
		}
	}

	/**
	 * @NoAdminRequired
	 */
	public function upload(int $clientId): JSONResponse {
		try {
			$clientFolder = $this->getClientFolder($clientId);
			
			if (!isset($_FILES['file'])) {
				return new JSONResponse(['error' => 'No file uploaded'], 400);
			}
			
			$uploadedFile = $_FILES['file'];
			
			if ($uploadedFile['error'] !== UPLOAD_ERR_OK) {
				return new JSONResponse(['error' => 'File upload failed'], 400);
			}
			
			$fileName = $this->sanitizeFileName($uploadedFile['name']);
			
			// Check if file exists, add number suffix
			$counter = 1;
			$finalFileName = $fileName;
			$pathInfo = pathinfo($fileName);
			while ($clientFolder->nodeExists($finalFileName)) {
				$extension = isset($pathInfo['extension']) ? '.' . $pathInfo['extension'] : '';
				$finalFileName = $pathInfo['filename'] . '_' . $counter . $extension;
				$counter++;
			}
			
			$file = $clientFolder->newFile($finalFileName, file_get_contents($uploadedFile['tmp_name']));
			$userFolder = $this->rootFolder->getUserFolder($this->userId);
			$relativePath = $userFolder->getRelativePath($file->getPath());
			
			return new JSONResponse([
				'id' => $file->getId(),
				'name' => $finalFileName,
				'size' => $file->getSize(),
				'mimeType' => $file->getMimeType(),
				'path' => $relativePath,
				'mtime' => $file->getMTime(),
				'etag' => $file->getEtag(),
			]);
		} catch (\Exception $e) {
			return new JSONResponse(['error' => $e->getMessage()], 500);
		}
	}

	/**
	 * @NoAdminRequired
	 */
	public function download(int $clientId, string $fileName): DataDownloadResponse {
		try {
			$clientFolder = $this->getClientFolder($clientId);
			
			if (!$clientFolder->nodeExists($fileName)) {
				return new DataDownloadResponse('', 404);
			}
			
			$file = $clientFolder->get($fileName);
			
			if (!($file instanceof File)) {
				return new DataDownloadResponse('', 404);
			}
			
			return new DataDownloadResponse($file->getContent(), $fileName, $file->getMimeType());
		} catch (\Exception $e) {
			return new DataDownloadResponse('', 500);
		}
	}

	/**
	 * @NoAdminRequired
	 */
	public function delete(int $clientId, string $fileName): JSONResponse {
		try {
			$clientFolder = $this->getClientFolder($clientId);
			
			if (!$clientFolder->nodeExists($fileName)) {
				return new JSONResponse(['error' => 'File not found'], 404);
			}
			
			$file = $clientFolder->get($fileName);
			
			if (!($file instanceof File)) {
				return new JSONResponse(['error' => 'Not a file'], 400);
			}
			
			$file->delete();
			
			return new JSONResponse(['success' => true]);
		} catch (\Exception $e) {
			return new JSONResponse(['error' => $e->getMessage()], 500);
		}
	}

	/**
	 * Sanitize file name
	 */
	private function sanitizeFileName(string $fileName): string {
		// Remove path traversal attempts
		$fileName = basename($fileName);
		// Remove special characters except dots, hyphens, underscores
		$fileName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $fileName);
		return $fileName;
	}
}


