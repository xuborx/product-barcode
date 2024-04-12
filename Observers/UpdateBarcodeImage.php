<?php

namespace CGI\ProductBarcode\Observers;

use CGI\ProductBarcode\Console\Command\GenerateBarcodes;
use CGI\ProductBarcode\Services\BarcodeGeneratorService;
use Magento\Backend\App\Action\Context;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class UpdateBarcodeImage implements ObserverInterface
{
    private $barcodeGeneratorService;
    private ProductRepositoryInterface $productRepository;
    private Context $context;

    /**
     * @param BarcodeGeneratorService $barcodeGeneratorService
     */
    public function __construct(BarcodeGeneratorService $barcodeGeneratorService, ProductRepositoryInterface $productRepository, Context $context)
    {
        $this->barcodeGeneratorService = $barcodeGeneratorService;
        $this->productRepository = $productRepository;
        $this->context = $context;
    }

    /**
     * @param Observer $observer
     * @return $this
     */
    public function execute(Observer $observer): static
    {
        if ($this->context->getRequest()->getFullActionName() == 'catalog_product_save') {
            $product = $observer->getEvent()->getProduct();
            $oldBarcodeValue = $product->getOrigData(GenerateBarcodes::BARCODE_VALUE_CODE);
            $newBarcodeValue = $product->getData(GenerateBarcodes::BARCODE_VALUE_CODE);
            $newBarcodeValue = $newBarcodeValue ? trim($product->getData(GenerateBarcodes::BARCODE_VALUE_CODE)) : '';

            if ($oldBarcodeValue != $newBarcodeValue) {
                $this->generateBarcodeImageAndSave(
                    $newBarcodeValue,
                    $product->getOrigData(GenerateBarcodes::BARCODE_IMAGE_CODE),
                    $product->getData('entity_id')
                );
            }
        }

        return $this;
    }

    /**
     * @param $barcodeValue
     * @param $oldBarcodeImagePath
     * @param $productId
     * @return void
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\StateException
     */
    private function generateBarcodeImageAndSave($barcodeValue, $oldBarcodeImagePath, $productId): void
    {
        $barcodeImage = $this->barcodeGeneratorService->generateBarcodeImage($barcodeValue, $oldBarcodeImagePath);
        $product = $this->productRepository->getById($productId);
        $product->setStoreId($product->getStoreId());
        $product->setCustomAttribute(GenerateBarcodes::BARCODE_IMAGE_CODE, $barcodeImage);
        $this->productRepository->save($product);
    }
}
