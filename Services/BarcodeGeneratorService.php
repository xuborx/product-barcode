<?php

namespace CGI\ProductBarcode\Services;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Picqer\Barcode\BarcodeGeneratorPNG;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Psr\Log\LoggerInterface;

class BarcodeGeneratorService
{
    private Filesystem $filesystem;
    private LoggerInterface $logger;

    public function __construct(
        Filesystem $filesystem,
        LoggerInterface $logger
    ) {
        $this->filesystem = $filesystem;
        $this->logger = $logger;
    }

    /**
     * @param string $newBarcodeValue
     * @param string|null $oldBarcodeImagePath
     * @return string
     * @throws FileSystemException
     */
    public function generateBarcodeImage(string $newBarcodeValue, ?string $oldBarcodeImagePath = null): string
    {
        $mediaDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);

        if (!empty($oldBarcodeImagePath)) {
            $this->deleteOldBarcodeImage($oldBarcodeImagePath, $mediaDirectory);
        }

        if (!empty($newBarcodeValue)) {
            $generator = new BarcodeGeneratorPNG();
            $imageData = $generator->getBarcode($newBarcodeValue, $generator::TYPE_CODE_128);

            // Save barcode image to media directory
            $imagePath = 'catalog/barcode/' . $newBarcodeValue . '.png';
            $mediaDirectory->writeFile($imagePath, $imageData);

            // Get URI of the saved image
            $imageUrl = $this->filesystem->getUri(DirectoryList::MEDIA) . '/'. $imagePath;
            return $imageUrl;
        }

        return '';
    }

    /**
     * Method to delete previous barcode image.
     *
     * @param string $oldBarcodeImagePath
     * @param WriteInterface $mediaDirectory
     * @return void
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    private function deleteOldBarcodeImage(string $oldBarcodeImagePath, WriteInterface $mediaDirectory): void
    {
        try {
            $mediaDirectory->delete( $this->filesystem->getDirectoryWrite(DirectoryList::PUB)->getAbsolutePath() . $oldBarcodeImagePath);
        } catch (FileSystemException $exception) {
            $this->logger->error('Failed to delete old barcode image: ' . $exception->getMessage());
        }
    }
}
