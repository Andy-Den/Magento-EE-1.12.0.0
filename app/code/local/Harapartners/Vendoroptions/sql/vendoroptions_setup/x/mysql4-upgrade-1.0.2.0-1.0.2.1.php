<?php

/*
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User Software Agreement (EULA).
 * It is also available through the world-wide-web at this URL:
 * http://www.harapartners.com/license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to eula@harapartners.com so we can send you a copy immediately.
 * 
 */

$installer = $this;
$installer->startSetup();

$catalogEavSetup = new Mage_Catalog_Model_Resource_Eav_Mysql4_Setup();
$vendorCodeMapping = Mage::getModel('vendoroptions/source_vendor')->getCodeValues();

$vendorIdAttrId = $catalogEavSetup->getAttributeId(Mage_Catalog_Model_Product::ENTITY, 'vendor_id');
$vendorCodeAttrId = $catalogEavSetup->getAttributeId(Mage_Catalog_Model_Product::ENTITY, 'vendor_code');

$batchProcessingSize = 2000;
$batchProcessingOffset = 0;
$writeConnection = Mage::getSingleton('core/resource')->getConnection('core_write');

do{
	$selectQuery = "SELECT entity_type_id, attribute_id, store_id, entity_id, value FROM `catalog_product_entity_varchar` WHERE `attribute_id` = $vendorCodeAttrId LIMIT $batchProcessingOffset, $batchProcessingSize";
	$results = $writeConnection->fetchAll($selectQuery);
	
	if(count($results) > 0){
		$insertQuery = "INSERT INTO catalog_product_entity_int (entity_type_id, attribute_id, store_id, entity_id, value) VALUES ";
		$insertEntryArray = array();
		foreach($results as $resultRow){
			if(empty($resultRow['value'])){
				continue;
			}
			//Replace by vendor ID
			$resultRow['value'] = array_search($resultRow['value'], $vendorCodeMapping);
			$resultRow['attribute_id'] = $vendorIdAttrId;
			$insertEntryArray[] = "(" . implode(",", $resultRow) . ")";
		}
		$insertQuery .= implode(",", $insertEntryArray);
	}
	$writeConnection->query($insertQuery);
	$batchProcessingOffset += $batchProcessingSize;
}while (count($results) >= $batchProcessingSize);

$installer->endSetup();
