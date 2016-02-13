<?php

require_once("../app/Mage.php");// External script - Load magento framework
    Mage::app();
    
    $installer = Mage::getResourceModel('catalog/setup', 'catalog_setup',''); //start the installer 

    $resource = Mage::getSingleton('core/resource');
    $readConnection = $resource->getConnection('core_read');
    $writeConnection = $resource->getConnection('core_write');

include 'init.php';

global $cart, $pos_type;
	
	//add rdi_avail
if (!$installer->getAttributeId(Mage_Catalog_Model_Product::ENTITY, 'rdi_avail')) {

$installer->addAttribute(Mage_Catalog_Model_Product::ENTITY, 'rdi_avail', array(
						'group'             => 'RDI',
                        'type'                       => 'varchar',
                        'label'                      => 'RDi Avail Field',
                        'input'                      => 'text',
                        'required'                   => false,
                        'user_defined'               => true,
                        'searchable'                 => false,
                        'filterable'                 => false,
                        'comparable'                 => false,
                        'visible_in_advanced_search' => false,
                       // 'apply_to'                   => Mage_Catalog_Model_Product_Type::TYPE_SIMPLE,
                    )
					);
	}

	$db1 = $cart->get_db();
	
	
	$rdi_avail = $db1->cell("select cart_field from rdi_field_mapping where cart_field = 'rdi_avail'","cart_field");

	if(empty($rdi_avail))
	{
		$rdi_132 = $db1->cell("select cart_field from rdi_field_mapping where field_mapping_id = 132","cart_field");
		
		$rdi_132 = empty($rdi_132) || !isset($rdi_132) ?"132":"9132";
		
		$db1->exec("INSERT INTO `rdi_field_mapping` (`field_mapping_id`, `field_type`, `field_classification`, `entity_type`, `cart_field`, `invisible_field`, `default_value`, `allow_update`, `special_handling`, `notes`) VALUES ('{$rdi_132}', 'product', NULL, NULL, 'rdi_avail', '0', '0', '1', NULL, NULL)");
		
		$avail_field = $pos_type == "rpro8"?"style.fldprodavail":"style.avail";
		
		$db1->exec("INSERT INTO `rdi_field_mapping_pos` (`field_mapping_id`, `pos_field`, `alternative_field`, `field_order`) VALUES ('{$rdi_132}', '{$avail_field}', NULL, '0')"); 

	}
	
	$avail_mapping_test = $db1->cell("select count(*) as test from rdi_field_mapping where field_type = 'avail'", 'test');
	if(!isset($avail_mapping_test) || $avail_mapping_test <= 0)
	{
	
		$quantity_field = $pos_type == "rpro8"?"item_availquantity":"quantity";
		$threshold_field = $pos_type == "rpro8"?"fldavailthreshold":"threshold";
	
			//add the mapping
		$db1->exec("INSERT INTO `rdi_field_mapping` (`field_mapping_id`, `field_type`, `field_classification`, `entity_type`, `cart_field`, `invisible_field`, `default_value`, `allow_update`, `special_handling`, `notes`) 
		VALUES 
		('10001', 'avail', NULL, 'simple', 'qty', '0', NULL, '1', NULL, NULL),
		('10002', 'avail', NULL, 'simple', 'min_qty', '0', NULL, '1', NULL, NULL),
		('10003', 'avail', NULL, 'simple', 'is_in_stock', '0', NULL, '1', NULL, NULL),
		('10004', 'avail', NULL, 'simple', 'use_config_min_qty', '0', '0', '1', NULL, NULL),
		('10005', 'avail', NULL, 'simple', 'manage_stock', '0', NULL, '1', NULL, NULL),
		('10006', 'avail', NULL, 'simple', 'use_config_manage_stock', '0', NULL, '1', NULL, NULL),
		('10007', 'avail', NULL, 'simple', 'backorders', '0', NULL, '1', NULL, NULL),
		('10008', 'avail', NULL, 'simple', 'use_config_backorders', '0', NULL, '1', NULL, NULL),
		('10009', 'avail', NULL, 'simple', 'qty_increments', '0', NULL, '1', NULL, NULL),
		('10010', 'avail', NULL, 'simple', 'use_config_enable_qty_inc', '0', NULL, '1', NULL, NULL),
		('10011', 'avail', NULL, 'simple', 'enable_qty_increments', '0', NULL, '1', NULL, NULL);");

		$db1->exec("INSERT INTO `rdi_field_mapping_pos` (`field_mapping_id`, `pos_field`, `alternative_field`, `field_order`)
		 VALUES 
		('10001', \"if(avail.value = 'Sell Never',0,item.{$quantity_field})\", NULL, '0'), 
		('10002', \"if(avail.value = 'Sell to Threshold',style.{$threshold_field},0)\", NULL, '0'), 
		('10003', \"if(avail.value = 'Sell Never',0,1)\", NULL, '0'), 
		('10004', \"0\", NULL, '0'), 
		('10005', \"if(avail.value = 'Sell Always',0,1)\", NULL, '0'), 
		('10006', \"if(avail.value = 'Sell Always',0,1)\", NULL, '0'), 
		('10007', \"if(avail.value = 'Allow Backorder',2,0)\", NULL, '0'), 
		('10008', \"if(avail.value = 'Allow Backorder',0,1)\", NULL, '0'), 
		('10009', \"0.0000\", NULL, '0'), 
		('10010', \"1\", NULL, '0'), 
		('10011', \"0\", NULL, '0')");




		$db1->exec("INSERT INTO `rdi_field_mapping` (`field_mapping_id`, `field_type`, `field_classification`, `entity_type`, `cart_field`, `invisible_field`, `default_value`, `allow_update`, `special_handling`, `notes`) VALUES 
		('10101', 'avail', NULL, 'configurable', 'qty', '0', NULL, '1', NULL, NULL),
		('10102', 'avail', NULL, 'configurable', 'min_qty', '0', NULL, '1', NULL, NULL),
		('10103', 'avail', NULL, 'configurable', 'is_in_stock', '0', NULL, '1', NULL, NULL),
		('10104', 'avail', NULL, 'configurable', 'use_config_min_qty', '0', NULL, '1', NULL, NULL),
		('10105', 'avail', NULL, 'configurable', 'manage_stock', '0', NULL, '1', NULL, NULL),
		('10106', 'avail', NULL, 'configurable', 'use_config_manage_stock', '0', NULL, '1', NULL, NULL),
		('10107', 'avail', NULL, 'configurable', 'backorders', '0', NULL, '1', NULL, NULL),
		('10108', 'avail', NULL, 'configurable', 'use_config_backorders', '0', NULL, '1', NULL, NULL),
		('10109', 'avail', NULL, 'configurable', 'qty_increments', '0', NULL, '1', NULL, NULL),
		('10110', 'avail', NULL, 'configurable', 'use_config_enable_qty_inc', '0', NULL, '1', NULL, NULL),
		('10111', 'avail', NULL, 'configurable', 'enable_qty_increments', '0', NULL, '1', NULL, NULL);");


		$db1->exec("INSERT INTO `rdi_field_mapping_pos` (`field_mapping_id`, `pos_field`, `alternative_field`, `field_order`) VALUES 
		('10101', \"0\", NULL, '0'), 
		('10102', \"0\", NULL, '0'), 
		('10103', \"if(avail.value = 'Sell Never',0,1)\", NULL, '0'), 
		('10104', \"0\", NULL, '0'), 
		('10105', \"if(avail.value = 'Sell Always',0,1)\", NULL, '0'), 
		('10106', \"if(avail.value = 'Sell Always',0,1)\", NULL, '0'), 
		('10107', \"if(avail.value = 'Allow Backorder',2,0)\", NULL, '0'), 
		('10108', \"if(avail.value = 'Allow Backorder',0,1)\", NULL, '0'), 
		('10109', \"0.0000\", NULL, '0'), 
		('10110', \"1\", NULL, '0'), 
		('10111', \"0\", NULL, '0')");
	}
	
	//

?>