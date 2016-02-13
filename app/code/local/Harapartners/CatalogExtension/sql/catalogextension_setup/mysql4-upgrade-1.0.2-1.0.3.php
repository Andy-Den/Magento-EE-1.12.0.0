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
$catalogEavSetup->updateAttribute(Mage_Catalog_Model_Product::ENTITY, 'shoe_color_index', 'backend_model', 'eav/entity_attribute_backend_array');
$catalogEavSetup->updateAttribute(Mage_Catalog_Model_Product::ENTITY, 'shoe_color_index', 'backend_type', 'varchar');
$catalogEavSetup->updateAttribute(Mage_Catalog_Model_Product::ENTITY, 'shoe_color_index', 'frontend_input', 'multiselect');

$colorConfigAttrId = $catalogEavSetup->getAttributeId(Mage_Catalog_Model_Product::ENTITY, 'shoe_color_config');
$colorIndexAttrId = $catalogEavSetup->getAttributeId(Mage_Catalog_Model_Product::ENTITY, 'shoe_color_index');

$writeConnection = Mage::getSingleton('core/resource')->getConnection('core_write');
$deleteQuery = "DELETE FROM catalog_product_entity_int WHERE attribute_id = \"$colorIndexAttrId\";";
$writeConnection->query($deleteQuery);
$deleteQuery = "DELETE FROM catalog_product_entity_varchar WHERE attribute_id = \"$colorIndexAttrId\";";
$writeConnection->query($deleteQuery);
$deleteQuery = "DELETE FROM catalog_product_entity_text WHERE attribute_id = \"$colorIndexAttrId\";";
$writeConnection->query($deleteQuery);

$colorConfigAttr = Mage::getSingleton('eav/config')->getAttribute('catalog_product', 'shoe_color_config');
$colorConfigAttrOptionArray = $colorConfigAttr->getSource()->getAllOptions(false);

//Truncate label first
$configLabelMapping = array();
foreach($colorConfigAttrOptionArray as $colorConfigAttrOption){
	$tempLabelArray = explode("_", $colorConfigAttrOption['label']);
	$tempLabel = trim($tempLabelArray[0]);
	if(!isset($configLabelMapping[$tempLabel])){
		$configLabelMapping[$tempLabel] = array();
	}
	$configLabelMapping[$tempLabel][] = $colorConfigAttrOption['value'];
}

//Build reverse mapping
$reverseIdMappingArray = array();
$colorIndexAttr = Mage::getSingleton('eav/config')->getAttribute('catalog_product', 'shoe_color_index');
$colorIndexAttrOptionArray = $colorIndexAttr->getSource()->getAllOptions(false);
foreach($colorIndexAttrOptionArray as $colorIndexAttrOption){
	foreach($configLabelMapping[$colorIndexAttrOption['label']] as $labelId){
		$reverseIdMappingArray[$labelId] = $colorIndexAttrOption['value'];
	}
}

$minProcessedId = 1100000;
$batchProductIdSize = 50000;
$batchProductIdLowerBound = 0;
$catalogProductEntityTypeId = 4;
$adminStoreId = 0;
$writeConnection = Mage::getSingleton('core/resource')->getConnection('core_write');

do{
	$batchProductIdUpperBound = $batchProductIdLowerBound + $batchProductIdSize;

	$selectQuery = "
		SELECT `parent_id`, `value` FROM `catalog_product_super_link` 
			  JOIN `catalog_product_entity_int` ON `catalog_product_super_link`.`product_id` = `catalog_product_entity_int`.`entity_id`
			  WHERE `catalog_product_entity_int`.`attribute_id` = $colorConfigAttrId 
			  		AND `catalog_product_super_link`.`parent_id` >= $batchProductIdLowerBound
			  		AND `catalog_product_super_link`.`parent_id` < $batchProductIdUpperBound
			  GROUP BY `parent_id`, `value`;";
	$results = $writeConnection->fetchAll($selectQuery);
	
	if(count($results) > 0){
		$insertQuery = "INSERT INTO catalog_product_entity_varchar (entity_type_id, attribute_id, store_id, entity_id, value) VALUES ";
		$combinedValueArray = array();
		foreach($results as $resultRow){
			if(empty($resultRow['value'])){
				continue;
			}
			if(!isset($combinedValueArray[$resultRow['parent_id']])){
				$combinedValueArray[$resultRow['parent_id']] = array();
			}
			$mappedValue = $reverseIdMappingArray[$resultRow['value']];
			$combinedValueArray[$resultRow['parent_id']][$mappedValue] = $mappedValue; //Use a key to auto remove duplicate
		}
		
		$insertEntryArray = array();
		foreach($combinedValueArray as $parentProductId => $parentProductOptionValues){
			$insetEntry = array(
					'entity_type_id' => $catalogProductEntityTypeId,
					'attribute_id' => $colorIndexAttrId,
					'store_id' => $adminStoreId,
					'entity_id' => $parentProductId,
					'value' => '"' . implode(",", $parentProductOptionValues) . '"' //must escape comma ","
			);
			$insertEntryArray[] = "(" . implode(",", $insetEntry) . ")";
		}
		$insertQuery .= implode(",", $insertEntryArray);
		if(count($insertEntryArray) > 0){
			$writeConnection->query($insertQuery);
		}
	}
	$batchProductIdLowerBound += $batchProductIdSize;
}while (count($results) > 0 || $batchProductIdLowerBound < $minProcessedId);

$installer->endSetup();