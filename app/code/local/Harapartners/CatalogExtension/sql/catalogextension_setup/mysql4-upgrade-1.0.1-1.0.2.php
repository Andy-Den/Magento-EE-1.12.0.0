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
$colorConfigAttrId = $catalogEavSetup->getAttributeId(Mage_Catalog_Model_Product::ENTITY, 'shoe_color_config');
$colorIndexAttrId = $catalogEavSetup->getAttributeId(Mage_Catalog_Model_Product::ENTITY, 'shoe_color_index');

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

//Add options to 'shoe_color_index' attribute
$colorIndexAttr = Mage::getSingleton('eav/config')->getAttribute('catalog_product', 'shoe_color_index');

$colorIndexAttrOptionArray = array('value' => array());
$adminStoreId = 0;
$tempColorIndexAttrId = 1;
$indexOptionValues = array();
foreach($configLabelMapping as $label => $labelIds){
	//Use a non-numeric option ID, i.e. 'option_'.$tempColorIndexAttrId, so that new options will be created
	$indexOptionValues['option_'.$tempColorIndexAttrId] = array($adminStoreId => $label);
	$tempColorIndexAttrId ++;
}
$colorIndexAttrOption = array(
		'attribute_id' => $colorIndexAttrId,
		'value' => $indexOptionValues
);
$catalogEavSetup->addAttributeOption($colorIndexAttrOption);

//Build reverse mapping
$reverseIdMappingArray = array();
$colorIndexAttrOptionArray = $colorIndexAttr->getSource()->getAllOptions(false);
foreach($colorIndexAttrOptionArray as $colorIndexAttrOption){
	foreach($configLabelMapping[$colorIndexAttrOption['label']] as $labelId){
		$reverseIdMappingArray[$labelId] = $colorIndexAttrOption['value'];
	}
}

$batchProcessingSize = 2000;
$batchProcessingOffset = 0;
$writeConnection = Mage::getSingleton('core/resource')->getConnection('core_write');

do{
	$selectQuery = "SELECT entity_type_id, attribute_id, store_id, entity_id, value FROM `catalog_product_entity_int` WHERE `attribute_id` = $colorConfigAttrId LIMIT $batchProcessingOffset, $batchProcessingSize";
	$results = $writeConnection->fetchAll($selectQuery);
	
	if(count($results) > 0){
		$insertQuery = "INSERT INTO catalog_product_entity_int (entity_type_id, attribute_id, store_id, entity_id, value) VALUES ";
		$insertEntryArray = array();
		foreach($results as $resultRow){
			if(empty($resultRow['value'])){
				continue;
			}
			//Replace by vendor ID
			$resultRow['value'] = $reverseIdMappingArray[$resultRow['value']];
			$resultRow['attribute_id'] = $colorIndexAttrId;
			$insertEntryArray[] = "(" . implode(",", $resultRow) . ")";
		}
		$insertQuery .= implode(",", $insertEntryArray);
		$writeConnection->query($insertQuery);
	}
	
	$batchProcessingOffset += $batchProcessingSize;
}while (count($results) >= $batchProcessingSize);

$installer->endSetup();
