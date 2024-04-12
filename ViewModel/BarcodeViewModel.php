<?php

namespace CGI\ProductBarcode\ViewModel;

use CGI\ProductBarcode\Console\Command\GenerateBarcodes;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Helper\Data as CatalogHelper;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filesystem;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class BarcodeViewModel implements ArgumentInterface
{
    private ProductRepositoryInterface $productRepository;
    private Filesystem $filesystem;
    private CatalogHelper $catalogHelper;

    public function __construct(
        ProductRepositoryInterface $productRepository,
        Filesystem $filesystem,
        CatalogHelper $catalogHelper
    ) {
        $this->productRepository = $productRepository;
        $this->filesystem = $filesystem;
        $this->catalogHelper = $catalogHelper;
    }

    /**
     * @return array|null
     */
    public function getBarcodeImageAndValue(): ?array
    {
        try {
            $product = $this->productRepository->getById($this->catalogHelper->getProduct()->getId());
            $barcodeImage = $product->getCustomAttribute(GenerateBarcodes::BARCODE_IMAGE_CODE)->getValue();
            $barcodeValue = $product->getCustomAttribute(GenerateBarcodes::BARCODE_VALUE_CODE)->getValue();

            if (!empty($barcodeImage) && !empty($barcodeValue)) {
                return [
                    'barcodeImage' => $barcodeImage,
                    'barcodeValue' => $barcodeValue
                ];
            } else {
                return null;
            }
        } catch (NoSuchEntityException $e) {
            return null;
        }
    }
}
