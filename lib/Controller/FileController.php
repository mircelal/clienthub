<?php
declare(strict_types=1);

namespace OCA\DomainControl\Controller;

use OCA\DomainControl\AppInfo\Application;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;
use OCP\Files\IRootFolder;
use OCP\Files\Folder;
use OCP\Files\File;
use OCP\Files\NotPermittedException;
use OCP\Files\NotFoundException;
use OCA\DomainControl\Db\WebsiteMapper;
use OCA\DomainControl\Db\ClientMapper;

class FileController extends Controller {
	private $userId;
	private IRootFolder $rootFolder;
	private WebsiteMapper $websiteMapper;
	private ClientMapper $clientMapper;

	public function __construct(IRequest $request,
	                            IRootFolder $rootFolder,
	                            WebsiteMapper $websiteMapper,
	                            ClientMapper $clientMapper,
	                            $userId) {
		parent::__construct(Application::APP_ID, $request);
		$this->rootFolder = $rootFolder;
		$this->websiteMapper = $websiteMapper;
		$this->clientMapper = $clientMapper;
		$this->userId = $userId;
	}

	/**
	 * Get or create base clients folder
	 */
	private function getClientsFolder(): Folder {
		$userFolder = $this->rootFolder->getUserFolder($this->userId);
		
		try {
			$clientsFolder = $userFolder->get('Clients');
			if ($clientsFolder instanceof Folder) {
				return $clientsFolder;
			}
		} catch (NotFoundException $e) {
			// Folder doesn't exist, create it
		}
		
		return $userFolder->newFolder('Clients');
	}

	/**
	 * Get or create client folder
	 */
	private function getClientFolder(string $clientName): Folder {
		$clientsFolder = $this->getClientsFolder();
		$safeName = $this->sanitizeFolderName($clientName);
		
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
	 * Get or create website folder for a client
	 */
	private function getWebsiteFolder(string $clientName, string $websiteName): Folder {
		$clientFolder = $this->getClientFolder($clientName);
		$safeWebsiteName = $this->sanitizeFolderName($websiteName);
		
		// Create websites folder if not exists
		try {
			$websitesFolder = $clientFolder->get('websites');
			if (!($websitesFolder instanceof Folder)) {
				$websitesFolder = $clientFolder->newFolder('websites');
			}
		} catch (NotFoundException $e) {
			$websitesFolder = $clientFolder->newFolder('websites');
		}
		
		// Create website folder
		try {
			$websiteFolder = $websitesFolder->get($safeWebsiteName);
			if ($websiteFolder instanceof Folder) {
				return $websiteFolder;
			}
		} catch (NotFoundException $e) {
			// Folder doesn't exist, create it
		}
		
		return $websitesFolder->newFolder($safeWebsiteName);
	}

	/**
	 * Sanitize folder/file name
	 */
	private function sanitizeFolderName(string $name): string {
		// Remove special characters, keep only alphanumeric, spaces, hyphens, underscores
		$name = preg_replace('/[^a-zA-Z0-9\s\-_]/', '', $name);
		// Replace spaces with underscores
		$name = str_replace(' ', '_', $name);
		// Limit length
		return substr($name, 0, 100);
	}

	/**
	 * Sanitize file name while preserving extension
	 */
	private function sanitizeFileName(string $name): string {
		$pathInfo = pathinfo($name);
		$extension = isset($pathInfo['extension']) ? '.' . $pathInfo['extension'] : '';
		$filename = $pathInfo['filename'];
		
		// Remove special characters from filename only, keep extension
		$filename = preg_replace('/[^a-zA-Z0-9\s\-_]/', '', $filename);
		// Replace spaces with underscores
		$filename = str_replace(' ', '_', $filename);
		// Limit length (leave room for extension)
		$maxLength = 200 - strlen($extension);
		$filename = substr($filename, 0, $maxLength);
		
		return $filename . $extension;
	}

	/**
	 * @NoAdminRequired
	 */
	public function uploadWebsiteFile(int $websiteId): JSONResponse {
		try {
			$website = $this->websiteMapper->find($websiteId, $this->userId);
			$client = $this->clientMapper->find($website->getClientId(), $this->userId);
			
			$websiteFolder = $this->getWebsiteFolder($client->getName(), $website->getName());
			
			// Handle multiple files
			$uploadedFiles = [];
			
			// PHP receives file[] as $_FILES['file'] with array structure
			if (!isset($_FILES['file'])) {
				return new JSONResponse(['error' => 'No file uploaded'], 400);
			}
			
			$files = $_FILES['file'];
			
			// Handle single or multiple files
			$fileNames = is_array($files['name']) ? $files['name'] : [$files['name']];
			$tmpNames = is_array($files['tmp_name']) ? $files['tmp_name'] : [$files['tmp_name']];
			$errors = is_array($files['error']) ? $files['error'] : [$files['error']];
			
			foreach ($fileNames as $index => $originalName) {
				if ($errors[$index] !== UPLOAD_ERR_OK) {
					continue;
				}
				
				$fileName = $this->sanitizeFileName($originalName);
				
				// Check if file exists, add number suffix
				$counter = 1;
				$finalFileName = $fileName;
				$pathInfo = pathinfo($fileName);
				while ($websiteFolder->nodeExists($finalFileName)) {
					$extension = isset($pathInfo['extension']) ? '.' . $pathInfo['extension'] : '';
					$finalFileName = $pathInfo['filename'] . '_' . $counter . $extension;
					$counter++;
				}
				
				$newFile = $websiteFolder->newFile($finalFileName, file_get_contents($tmpNames[$index]));
				$userFolder = $this->rootFolder->getUserFolder($this->userId);
				$relativePath = $userFolder->getRelativePath($newFile->getPath());
				
				$uploadedFiles[] = [
					'name' => $finalFileName,
					'size' => $newFile->getSize(),
					'path' => $relativePath,
					'mimeType' => $newFile->getMimeType(),
					'fileId' => $newFile->getId()
				];
			}
			
			if (empty($uploadedFiles)) {
				return new JSONResponse(['error' => 'No files were uploaded'], 400);
			}
			
			return new JSONResponse([
				'success' => true,
				'files' => $uploadedFiles
			]);
		} catch (\Exception $e) {
			return new JSONResponse(['error' => $e->getMessage()], 500);
		}
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function listWebsiteFiles(int $websiteId): JSONResponse {
		try {
			$website = $this->websiteMapper->find($websiteId, $this->userId);
			$client = $this->clientMapper->find($website->getClientId(), $this->userId);
			
			$websiteFolder = $this->getWebsiteFolder($client->getName(), $website->getName());
			
			$files = [];
			$userFolder = $this->rootFolder->getUserFolder($this->userId);
			foreach ($websiteFolder->getDirectoryListing() as $node) {
				if ($node instanceof File) {
					$relativePath = $userFolder->getRelativePath($node->getPath());
					$files[] = [
						'name' => $node->getName(),
						'size' => $node->getSize(),
						'mimeType' => $node->getMimeType(),
						'mtime' => $node->getMTime(),
						'path' => $relativePath,
						'fileId' => $node->getId()
					];
				}
			}
			
			return new JSONResponse($files);
		} catch (\Exception $e) {
			return new JSONResponse(['error' => $e->getMessage()], 500);
		}
	}

	/**
	 * @NoAdminRequired
	 */
	public function deleteWebsiteFile(int $websiteId, string $fileName): JSONResponse {
		try {
			$website = $this->websiteMapper->find($websiteId, $this->userId);
			$client = $this->clientMapper->find($website->getClientId(), $this->userId);
			
			$websiteFolder = $this->getWebsiteFolder($client->getName(), $website->getName());
			$file = $websiteFolder->get($fileName);
			
			if ($file instanceof File) {
				$file->delete();
				return new JSONResponse(['success' => true]);
			}
			
			return new JSONResponse(['error' => 'File not found'], 404);
		} catch (\Exception $e) {
			return new JSONResponse(['error' => $e->getMessage()], 500);
		}
	}

}

