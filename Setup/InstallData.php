<?php

namespace CGI\ProductBarcode\Setup;

use CGI\ProductBarcode\Console\Command\GenerateBarcodes;
use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class InstallData implements InstallDataInterface
{
    private $eavSetupFactory;
    private $eavConfig;

    public function __construct(
        EavSetupFactory $eavSetupFactory,
        Config $eavConfig
    ) {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->eavConfig = $eavConfig;
    }

    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

        // Add barcode_value attribute
        $eavSetup->addAttribute(
            Product::ENTITY,
            GenerateBarcodes::BARCODE_VALUE_CODE,
            [
                'type' => 'varchar',
                'label' => 'Barcode',
                'input' => 'text',
                'required' => false,
                'visible' => true,
                'user_defined' => true,
                'searchable' => true,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => true,
                'global' => ScopedAttributeInterface::SCOPE_STORE,
                'group' => 'Product Details',
            ]
        );

        // Add barcode_image attribute
        $eavSetup->addAttribute(
            Product::ENTITY,
            GenerateBarcodes::BARCODE_IMAGE_CODE,
            [
                'type' => 'varchar',
                'label' => 'Barcode Image',
                'input' => 'text',
                'required' => false,
                'visible' => false,
                'user_defined' => false,
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'global' => ScopedAttributeInterface::SCOPE_STORE,
                'group' => 'Product Details',
            ]
        );

        $barcodeValueAttribute = $this->eavConfig->getAttribute(Product::ENTITY, GenerateBarcodes::BARCODE_VALUE_CODE);
        $barcodeImageAttribute = $this->eavConfig->getAttribute(Product::ENTITY, GenerateBarcodes::BARCODE_IMAGE_CODE);

        // Add the attributes to attribute sets
        $attributeSetIds = $eavSetup->getAllAttributeSetIds(Product::ENTITY);
        foreach ($attributeSetIds as $attributeSetId) {
            $groupId = $eavSetup->getAttributeGroupId(Product::ENTITY, $attributeSetId, 'Product Details');
            $eavSetup->addAttributeToSet(Product::ENTITY, $attributeSetId, $groupId, $barcodeValueAttribute->getId());
            $eavSetup->addAttributeToSet(Product::ENTITY, $attributeSetId, $groupId, $barcodeImageAttribute->getId());
        }

        $setup->endSetup();
    }
}
