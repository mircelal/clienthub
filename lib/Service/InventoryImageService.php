<?php

namespace OCA\DomainControl\Service;

use Exception;
use OCA\DomainControl\Db\InventoryImage;
use OCA\DomainControl\Db\InventoryImageMapper;
use OCP\Files\IRootFolder;
use OCP\Files\NotFoundException as FileNotFoundException;

class InventoryImageService
{
    private $mapper;
    private $rootFolder;
    private $userId;

    public function __construct(InventoryImageMapper $mapper, IRootFolder $rootFolder)
    {
        $this->mapper = $mapper;
        $this->rootFolder = $rootFolder;
        $user = \OC::$server->getUserSession()->getUser();
        if ($user) {
            $this->userId = $user->getUID();
        } else {
            throw new Exception('User not authenticated');
        }
    }

    public function findByInventoryId($inventoryId)
    {
        return $this->mapper->findByInventoryId($inventoryId);
    }

    public function uploadImage($inventoryId, $file)
    {
        if (!$file) {
            throw new Exception('No file provided');
        }

        if (!$this->userId) {
            throw new Exception('User not authenticated');
        }

        try {
            $userFolder = $this->rootFolder->getUserFolder($this->userId);

            // Ensure Inventory folder exists
            if (!$userFolder->nodeExists('Inventory')) {
                $userFolder->newFolder('Inventory');
            }
            $invFolder = $userFolder->get('Inventory');

            // Ensure Images folder exists
            if (!$invFolder->nodeExists('Images')) {
                $invFolder->newFolder('Images');
            }
            $imagesFolder = $invFolder->get('Images');

            // Create item-specific folder
            $itemFolderName = 'item_' . $inventoryId;
            if (!$imagesFolder->nodeExists($itemFolderName)) {
                $imagesFolder->newFolder($itemFolderName);
            }
            $itemFolder = $imagesFolder->get($itemFolderName);

            // Generate unique filename
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = uniqid('img_') . '.' . $extension;

            // Check if file exists, add number suffix
            $counter = 1;
            $finalFileName = $filename;
            $pathInfo = pathinfo($filename);
            while ($itemFolder->nodeExists($finalFileName)) {
                $ext = isset($pathInfo['extension']) ? '.' . $pathInfo['extension'] : '';
                $finalFileName = $pathInfo['filename'] . '_' . $counter . $ext;
                $counter++;
            }

            // Save file
            $newFile = $itemFolder->newFile($finalFileName, file_get_contents($file['tmp_name']));
            $relativePath = $userFolder->getRelativePath($newFile->getPath());

            // Create image record
            $image = new InventoryImage();
            $image->setInventoryId((int) $inventoryId);
            $image->setFilePath($relativePath);
            $image->setFileId($newFile->getId());
            $image->setIsPrimary(0); // Will be set to 1 if it's the first image
            $image->setSortOrder(0);
            $image->setCreatedAt(date('Y-m-d H:i:s'));

            // If this is the first image for this item, set it as primary
            $existingImages = $this->mapper->findByInventoryId($inventoryId);
            if (empty($existingImages)) {
                $image->setIsPrimary(1);
            } else {
                // Set sort order to be after the last image
                $maxSortOrder = 0;
                foreach ($existingImages as $existing) {
                    if ($existing->getSortOrder() > $maxSortOrder) {
                        $maxSortOrder = $existing->getSortOrder();
                    }
                }
                $image->setSortOrder($maxSortOrder + 1);
            }

            return $this->mapper->insert($image);
        } catch (Exception $e) {
            \OCP\Util::writeLog('domaincontrol', 'InventoryImageService::uploadImage error: ' . $e->getMessage() . ' - InventoryId: ' . $inventoryId, \OCP\Util::ERROR);
            throw new Exception('Failed to upload image: ' . $e->getMessage());
        }
    }

    public function setPrimary($imageId)
    {
        try {
            $image = $this->mapper->find($imageId);
            $inventoryId = $image->getInventoryId();

            // Unset all other primary images for this inventory item
            $allImages = $this->mapper->findByInventoryId($inventoryId);
            foreach ($allImages as $img) {
                if ($img->getId() !== $imageId && $img->getIsPrimary()) {
                    $img->setIsPrimary(0);
                    $this->mapper->update($img);
                }
            }

            // Set this image as primary
            $image->setIsPrimary(1);
            return $this->mapper->update($image);
        } catch (Exception $e) {
            \OCP\Util::writeLog('domaincontrol', 'InventoryImageService::setPrimary error: ' . $e->getMessage() . ' - ImageId: ' . $imageId, \OCP\Util::ERROR);
            throw new Exception('Failed to set primary image: ' . $e->getMessage());
        }
    }

    public function updateOrder($inventoryId, $order)
    {
        try {
            $images = $this->mapper->findByInventoryId($inventoryId);
            foreach ($order as $index => $imageId) {
                foreach ($images as $image) {
                    if ($image->getId() == $imageId) {
                        $image->setSortOrder($index);
                        $this->mapper->update($image);
                        break;
                    }
                }
            }
            return true;
        } catch (Exception $e) {
            throw new Exception('Failed to update order: ' . $e->getMessage());
        }
    }

    public function delete($imageId)
    {
        try {
            $image = $this->mapper->find($imageId);
            
            // Delete file from Nextcloud
            try {
                $userFolder = $this->rootFolder->getUserFolder($this->userId);
                $file = $userFolder->get($image->getFilePath());
                $file->delete();
            } catch (FileNotFoundException $e) {
                // File already deleted, continue
            }

            // Delete image record
            $this->mapper->delete($image);

            // If this was the primary image, set the first remaining image as primary
            if ($image->getIsPrimary()) {
                $remainingImages = $this->mapper->findByInventoryId($image->getInventoryId());
                if (!empty($remainingImages)) {
                    $firstImage = $remainingImages[0];
                    $firstImage->setIsPrimary(1);
                    $this->mapper->update($firstImage);
                }
            }

            return true;
        } catch (Exception $e) {
            throw new Exception('Failed to delete image: ' . $e->getMessage());
        }
    }
}


