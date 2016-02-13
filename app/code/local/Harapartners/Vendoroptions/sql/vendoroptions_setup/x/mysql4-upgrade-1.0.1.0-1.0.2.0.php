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
$attrGroupName = 'Vendor Info';
$defaultSortOrder = 0; //Put at the end

$vendorIdData =  array(
        'sort_order' => 0,
        'type' => 'int',
        'backend' => '',
        'frontend' => '',
        'label' => 'Vendor/Brand',
        'note' => '',
        'input' => 'select',
        'class' => '',
        'source' => 'vendoroptions/source_vendor',
        'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
        'visible' => true,
        'required' => false,
        'user_defined' => false,
        'visible_on_front' => false,
        'unique' => false,
        'is_configurable' => false,
		'filterable' => true,
        'filterable_in_search' => true,
		'used_in_product_listing' => true,
        'used_for_promo_rules' => false
);
$catalogEavSetup->addAttribute(
		Mage_Catalog_Model_Product::ENTITY, 
		'vendor_id', 
		$vendorIdData
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
			'vendor_id'
	);
}

$installer->endSetup();
