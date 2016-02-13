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

//Most shoemart category would require layered nav
$catalogEavSetup->updateAttribute(Mage_Catalog_Model_Category::ENTITY, 'is_anchor', 'default_value', 1);

$catalogEavSetup->updateAttribute(Mage_Catalog_Model_Product::ENTITY, 'shoe_color_manu', 'note', 'Manufacturer specified color. Can be an arbitrary string.');
$catalogEavSetup->updateAttribute(Mage_Catalog_Model_Product::ENTITY, 'shoe_color_config', 'note', 'Magento internal color. Used for configurable/simple product association.');
$catalogEavSetup->updateAttribute(Mage_Catalog_Model_Product::ENTITY, 'shoe_color_config', 'filterable', 0);
$catalogEavSetup->updateAttribute(Mage_Catalog_Model_Product::ENTITY, 'shoe_color_config', 'filterable_in_search', 0);

$attrGroupName = 'Shoe Config Attributes';

$shoeColorIndexData =  array(
        'sort_order' => 100,
        'type' => 'int',
        'backend' => '',
        'frontend' => '',
        'label' => 'Shoe Color Index',
        'note' => '',
        'input' => 'select',
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
		'note' => 'Frontend Color for Layered Nav and Search.'
);
$catalogEavSetup->addAttribute(
		Mage_Catalog_Model_Product::ENTITY, 
		'shoe_color_index', 
		$shoeColorIndexData
);


//Add group and attributes to all attribute sets
$productAllAttributeSetIds = $catalogEavSetup->getAllAttributeSetIds(Mage_Catalog_Model_Product::ENTITY);
foreach($productAllAttributeSetIds as $productAttributeSetId){
	
	$productVendorGroupId = $catalogEavSetup->getAttributeGroupId(Mage_Catalog_Model_Product::ENTITY, $productAttributeSetId, $attrGroupName);
	if(!$productVendorGroupId){
		$catalogEavSetup->addAttributeGroup(Mage_Catalog_Model_Product::ENTITY, $productAttributeSetId, $attrGroupName, $defaultSortOrder);
		$productVendorGroupId = $catalogEavSetup->getAttributeGroupId(Mage_Catalog_Model_Product::ENTITY, $productAttributeSetId, $attrGroupName);
	}

	$catalogEavSetup->addAttributeToSet(
			Mage_Catalog_Model_Product::ENTITY, 
			$productAttributeSetId, 
			$productVendorGroupId, 
			'shoe_color_index'
	);
}

$installer->endSetup();
