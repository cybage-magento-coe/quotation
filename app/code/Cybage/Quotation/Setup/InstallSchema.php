<?php

namespace Cybage\Quotation\Setup;

use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;

class InstallSchema implements InstallSchemaInterface
{

    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {

        $installer = $setup;
        $installer->startSetup();
        $sql = "DROP TABLE IF EXISTS {$installer->getTable('b2b_quotation')};";
        $installer->run($sql);
        
        $sql="CREATE TABLE `{$installer->getTable('b2b_quotation')}` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `customer_id` int(10) unsigned DEFAULT NULL,
          `order_id` int(11) DEFAULT NULL,
          `total_product_price` float DEFAULT NULL,
          `total_proposed_price` float DEFAULT NULL,
          `quotation_status` enum('0','1','2','3','4','5','6','7') DEFAULT '7' 
          COMMENT '0->requested,1->approved,2->responded, 3->rejected, 4->re_requested, 5->accepted, 6->completed, 7->intermediate',
          `delivery_date` date DEFAULT NULL,
          `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
          `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`),
          KEY `customer_id` (`customer_id`),
          CONSTRAINT `FK_cybage_quotation` FOREIGN KEY (`customer_id`) REFERENCES `customer_entity` (`entity_id`) ON DELETE CASCADE
        ) ENGINE=InnoDB AUTO_INCREMENT=71 DEFAULT CHARSET=latin1;";
        
        $installer->run($sql);  
        
        $sql = "DROP TABLE IF EXISTS `{$installer->getTable('b2b_quotation_comment')}`;";
        $installer->run($sql);

    $sql = "CREATE TABLE {$installer->getTable('b2b_quotation_comment')} (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `quotation_id` int(11) DEFAULT NULL,
      `customer_id` int(10) unsigned DEFAULT NULL,
      `admin_id` int(10) unsigned DEFAULT NULL,
      `comment` text,
      `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
      `commentted_by` enum('a','c') DEFAULT 'a' COMMENT 'a->admin,c->customer',
      PRIMARY KEY (`id`),
      KEY `quotation_id` (`quotation_id`),
      KEY `cusyomer_id` (`customer_id`),
      CONSTRAINT `FK_cybage_quotation_comment` FOREIGN KEY (`quotation_id`) REFERENCES {$installer->getTable('b2b_quotation')} (`id`) ON DELETE CASCADE,
      CONSTRAINT `cu_b2b_quotation_comment` FOREIGN KEY (`customer_id`) REFERENCES `customer_entity` (`entity_id`) ON DELETE CASCADE
    ) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=latin1;";
    $installer->run($sql);
    
$sql = "DROP TABLE IF EXISTS {$installer->getTable('b2b_quotation_item')};";
$installer->run($sql);

$sql = "CREATE TABLE {$installer->getTable('b2b_quotation_item')} (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `quotation_id` int(11) DEFAULT NULL,
  `product_id` int(10) unsigned DEFAULT NULL,
  `qty` int(11) DEFAULT NULL,
  `product_price` float DEFAULT NULL,
  `proposed_price` float DEFAULT NULL,
  `sku` varchar(100) DEFAULT NULL,
  `options` text,
  `parent_id` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `quotation_id` (`quotation_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `FK_cybage_quotation_item` FOREIGN KEY (`quotation_id`) REFERENCES {$installer->getTable('b2b_quotation')} (`id`) ON DELETE CASCADE,
  CONSTRAINT `pr_cybage_quotation_item` FOREIGN KEY (`product_id`) REFERENCES `catalog_product_entity` (`entity_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=293 DEFAULT CHARSET=latin1;";
$installer->run($sql);

$sql = "DROP TABLE IF EXISTS {$installer->getTable('b2b_quotation_item_log')};";
$installer->run($sql);

$sql = "CREATE TABLE {$installer->getTable('b2b_quotation_item_log')} (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `quotation_item_id` int(11) DEFAULT NULL,
  `qty` int(11) DEFAULT NULL,
  `product_price` float DEFAULT NULL,
  `proposed_price` float DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `quotation_item_id` (`quotation_item_id`),
  CONSTRAINT `FK_b2b_quotation_item_log` FOREIGN KEY (`quotation_item_id`) REFERENCES {$installer->getTable('b2b_quotation_item')} (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=65 DEFAULT CHARSET=latin1;";
$installer->run($sql);

$sql = "DROP TABLE IF EXISTS {$installer->getTable('b2b_quotation_log')};";
$installer->run($sql);

$sql = "CREATE TABLE {$installer->getTable('b2b_quotation_log')} (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `quotation_id` int(11) DEFAULT NULL,
  `total_product_price` float DEFAULT NULL,
  `total_proposed_price` float DEFAULT NULL,
  `delivery_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `quotation_id` (`quotation_id`),
  CONSTRAINT `FK_cybage_quotation_log` FOREIGN KEY (`quotation_id`) REFERENCES {$installer->getTable('b2b_quotation')} (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=100 DEFAULT CHARSET=latin1;";
$installer->run($sql);


        $installer->endSetup();
    }

}
