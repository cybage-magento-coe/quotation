<?php

namespace Training4\Vendor\Setup;

use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class InstallData implements InstallDataInterface {

    /**
     * EAV setup factory
     *
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * Init
     *
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(EavSetupFactory $eavSetupFactory) {
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context) {
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

        /**
         * Add attributes to the eav/attribute
         */
        $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY, 'vendor', [
                'type' => 'varchar',
                'label' => 'Vendor',
                'input' => 'select',
                'source' => 'Training4\Vendor\Model\Vendorsource',
                'required' => false,
                'sort_order' => 3,
                'global'=>\Magento\Catalog\Model\ResourceModel\Eav\Attribute::SCOPE_GLOBAL,
                'group' => 'Product Details',
                'used_in_product_listing' => true,
                'visible_on_front' => true
                ]
        );
    }

}
