<?php

namespace CGI\ProductBarcode\Console\Command;

use CGI\ProductBarcode\Services\BarcodeGeneratorService;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\State;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateBarcodes extends Command
{
    public const BARCODE_VALUE_CODE = 'barcode_value';
    public const BARCODE_IMAGE_CODE = 'barcode_image';
    private ProductRepositoryInterface $productRepository;
    private SearchCriteriaBuilder $searchCriteriaBuilder;
    private State $state;
    private BarcodeGeneratorService $barcodeGeneratorService;

    protected function configure()
    {
        $this->setName('cgi:generate_barcodes');
        $this->setDescription('Console command for generating barcodes');

        parent::configure();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->state = ObjectManager::getInstance()->get(State::class);
        $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_ADMINHTML);
        $this->productRepository = ObjectManager::getInstance()->get(ProductRepositoryInterface::class);
        $this->searchCriteriaBuilder = ObjectManager::getInstance()->get(SearchCriteriaBuilder::class);
        $this->barcodeGeneratorService = ObjectManager::getInstance()->get(BarcodeGeneratorService::class);
        $searchCriteria = $this->searchCriteriaBuilder->create();
        $products = $this->productRepository->getList($searchCriteria)->getItems();

        foreach ($products as $product) {
            $barcodeValue = (string)random_int(1000000000, 9999999999);
            $barcodeImage = $this->barcodeGeneratorService->generateBarcodeImage(
                $barcodeValue,
                $product->getCustomAttribute(self::BARCODE_IMAGE_CODE)->getValue()
            );
            $product->setStoreId($product->getStoreId());
            $product->setCustomAttributes([
                self::BARCODE_VALUE_CODE => $barcodeValue,
                self::BARCODE_IMAGE_CODE => $barcodeImage
            ]);
            $this->productRepository->save($product);
            $output->writeln('Barcode ' . $barcodeValue . ' was generated for a product with the name "' . $product->getName() . '" (product ID: ' . $product->getId() . ')');
        }

        $output->writeln('Barcode generation completed');
        return Command::SUCCESS;
    }
}
