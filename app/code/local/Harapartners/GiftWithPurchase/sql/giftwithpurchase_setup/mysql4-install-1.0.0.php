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

$attrGroupName = 'General';
$defaultSortOrder = 9999; //Put at the end
$isGiftWithPurchaseProductData = array(
        'sort_order' => 1900,
        'type' => 'int',
        'backend' => '',
        'frontend' => '',
        'label' => 'Is Gift with Purchase Product',
        'note' => 'This is a candicate product which can be added to cart automatically based on "Gift with Purchase" logic.',
        'input' => 'select',
        'class' => '',
        'source' => 'eav/entity_attribute_source_boolean',
        'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
        'visible' => true,
        'required' => false,
        'user_defined' => false,
        'visible_on_front' => false,
        'unique' => false,
        'is_configurable' => false,
        'used_for_promo_rules' => false
);

$catalogEavSetup->addAttribute(
		Mage_Catalog_Model_Product::ENTITY, 
		'is_gift_with_purchase_product', 
		$isGiftWithPurchaseProductData
);


//Add group and attributes to all attribute sets
$productAllAttributeSetIds = $catalogEavSetup->getAllAttributeSetIds(Mage_Catalog_Model_Product::ENTITY);
foreach($productAllAttributeSetIds as $productAttributeSetId){
	
	$productGeneralGroupId = $catalogEavSetup->getAttributeGroupId(Mage_Catalog_Model_Product::ENTITY, $productAttributeSetId, $attrGroupName);
	if(!$productGeneralGroupId){
		$catalogEavSetup->addAttributeGroup(Mage_Catalog_Model_Product::ENTITY, $productAttributeSetId, $attrGroupName, $defaultSortOrder);
		$productGeneralGroupId = $catalogEavSetup->getAttributeGroupId(Mage_Catalog_Model_Product::ENTITY, $productAttributeSetId, $attrGroupName);
	}

	$catalogEavSetup->addAttributeToSet(
			Mage_Catalog_Model_Product::ENTITY, 
			$productAttributeSetId, 
			$productGeneralGroupId, 
			'is_gift_with_purchase_product'
	);
}

$installer->endSetup();
