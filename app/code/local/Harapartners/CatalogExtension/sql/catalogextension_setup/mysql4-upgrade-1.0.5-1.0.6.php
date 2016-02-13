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

// ==================== shoe_width should not be used for layered nav ==================== //
$catalogEavSetup->updateAttribute(Mage_Catalog_Model_Product::ENTITY, 'shoe_width', 'note', 'Magento internal width. Used for configurable/simple product association.');
$catalogEavSetup->updateAttribute(Mage_Catalog_Model_Product::ENTITY, 'shoe_width', 'filterable', 0);
$catalogEavSetup->updateAttribute(Mage_Catalog_Model_Product::ENTITY, 'shoe_width', 'filterable_in_search', 0);


// ==================== Add shoe_width_index for layered nav ==================== //
$attrGroupName = 'Shoe Config Attributes';
$shoeWidthIndexData =  array(
        'sort_order' => 300,
        'type' => 'varchar',
        'backend' => 'eav/entity_attribute_backend_array',
        'frontend' => '',
        'label' => 'Shoe Width Index',
        'note' => '',
        'input' => 'multiselect',
        'class' => '',
        'source' => '',
        'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
        'visible' => true,
        'required' => false,
        'user_defined' => false,
        'visible_on_front' => false,
        'unique' => false,
        'is_configurable' => false,
        'filterable' => true,
        'filterable_in_search' => true,
        'used_for_promo_rules' => false,
		'note' => 'Frontend Width for Layered Nav and Search.'
);
$catalogEavSetup->addAttribute(
		Mage_Catalog_Model_Product::ENTITY, 
		'shoe_width_index', 
		$shoeWidthIndexData
);

// ==================== Add group and attributes to all attribute sets ==================== //
$productAllAttributeSetIds = $catalogEavSetup->getAllAttributeSetIds(Mage_Catalog_Model_Product::ENTITY);
foreach($productAllAttributeSetIds as $productAttributeSetId){
	
	$productGroupId = $catalogEavSetup->getAttributeGroupId(Mage_Catalog_Model_Product::ENTITY, $productAttributeSetId, $attrGroupName);
	if(!$productGroupId){
		$catalogEavSetup->addAttributeGroup(Mage_Catalog_Model_Product::ENTITY, $productAttributeSetId, $attrGroupName, $defaultSortOrder);
		$productGroupId = $catalogEavSetup->getAttributeGroupId(Mage_Catalog_Model_Product::ENTITY, $productAttributeSetId, $attrGroupName);
	}

	$catalogEavSetup->addAttributeToSet(
			Mage_Catalog_Model_Product::ENTITY, 
			$productAttributeSetId, 
			$productGroupId, 
			'shoe_width_index'
	);
}

// ==================== Add options to 'shoe_width_index' attribute ==================== //
$widthMappingArray = array(
		"2A" => "Extra Narrow",
		"3A" => "Extra Narrow",
		"4A" => "Extra Narrow",
		"A" => "Extra Narrow",
		"B" => "Narrow",
		"C" => "Narrow",
		"N" => "Narrow",
		"S" => "Narrow",
		"D" => "Medium",
		"M" => "Medium",
		"MW" => "Medium",
		"R" => "Medium",
		"RM" => "Medium",
		"RT" => "Medium",
		"WRM" => "Medium",
		"2E" => "Wide",
		"E" => "Wide",
		"FM" => "Wide",
		"W" => "Wide",
		"WFM" => "Wide",
		"3E" => "Extra Wide",
		"3W" => "Extra Wide",
		"4E" => "Extra Wide",
		"5E" => "Extra Wide",
		"6E" => "Extra Wide",
		"EW" => "Extra Wide",
		"H" => "Extra Wide",
		"WW" => "Extra Wide",
		"XW" => "Extra Wide"
);

$widthIndexAttr = Mage::getSingleton('eav/config')->getAttribute('catalog_product', 'shoe_width_index');
$widthIndexAttrOptionLabelArray = array_unique(array_values($widthMappingArray));
$adminStoreId = 0;
$tempWidthIndexAttrId = 1;
$indexOptionValues = array();
foreach($widthIndexAttrOptionLabelArray as $widthIndexAttrOptionLabel){
	//Use a non-numeric option ID, i.e. 'option_'.$tempWidthIndexAttrId, so that new options will be created
	$indexOptionValues['option_'.$tempWidthIndexAttrId] = array($adminStoreId => $widthIndexAttrOptionLabel);
	$tempWidthIndexAttrId ++;
}
$widthIndexAttrOption = array(
		'attribute_id' => $widthIndexAttr->getId(),
		'value' => $indexOptionValues
);
$catalogEavSetup->addAttributeOption($widthIndexAttrOption);


// ==================== Build reverse mapping ==================== //
$optionIdMappingArray = array();
$widthAttr = Mage::getSingleton('eav/config')->getAttribute('catalog_product', 'shoe_width');
$widthAttrOptionArray = $widthAttr->getSource()->getAllOptions(false);
$widthIndexAttrOptionArray = $widthIndexAttr->getSource()->getAllOptions(false);
foreach($widthAttrOptionArray as $widthAttrOption){
	//Note certain sizes may not be mapped
	if(isset($widthMappingArray[$widthAttrOption['label']])){
		foreach($widthIndexAttrOptionArray as $widthIndexAttrOption){
			if($widthIndexAttrOption['label'] == $widthMappingArray[$widthAttrOption['label']]){
				$optionIdMappingArray[$widthAttrOption['value']] = $widthIndexAttrOption['value'];
				break;
			}
		}
	}
}

// ==================== Batch update attributes ==================== //
$minProcessedId = 1100000; //Shoemart special logic
$batchProductIdSize = 5000;
$batchProductIdLowerBound = 0;
$catalogProductEntityTypeId = 4;
$adminStoreId = 0;
$widthConfigAttrId = $catalogEavSetup->getAttributeId(Mage_Catalog_Model_Product::ENTITY, 'shoe_width');
$widthIndexAttrId = $catalogEavSetup->getAttributeId(Mage_Catalog_Model_Product::ENTITY, 'shoe_width_index');
$writeConnection = Mage::getSingleton('core/resource')->getConnection('core_write');

do{
	$batchProductIdUpperBound = $batchProductIdLowerBound + $batchProductIdSize;

	$selectQuery = "
		SELECT `parent_id`, `value` FROM `catalog_product_super_link` 
			  JOIN `catalog_product_entity_int` ON `catalog_product_super_link`.`product_id` = `catalog_product_entity_int`.`entity_id`
			  WHERE `catalog_product_entity_int`.`attribute_id` = $widthConfigAttrId 
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
			if($optionIdMappingArray[$resultRow['value']]){
				$mappedValue = $optionIdMappingArray[$resultRow['value']];
				$combinedValueArray[$resultRow['parent_id']][$mappedValue] = $mappedValue; //Use a key to auto remove duplicate
			}
		}
		
		$insertEntryArray = array();
		foreach($combinedValueArray as $parentProductId => $parentProductOptionValues){
			$insetEntry = array(
					'entity_type_id' => $catalogProductEntityTypeId,
					'attribute_id' => $widthIndexAttrId,
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
