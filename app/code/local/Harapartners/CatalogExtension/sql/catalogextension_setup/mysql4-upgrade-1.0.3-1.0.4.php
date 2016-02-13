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

// ===== Create configurator product attributes and put it in the configurator group ===== //
$catalogEavSetup = new Mage_Catalog_Model_Resource_Eav_Mysql4_Setup();
$attrGroupName = 'General Information';
$defaultSortOrder = 9999; //Put at the end
$isCatalogRootData = array(
        'sort_order' => 200,
        'type' => 'int',
        'backend' => '',
        'frontend' => '',
        'label' => 'Is Catalog Root',
        'note' => 'This flag marks the category as a Catalog Root. Such categories are used for organizing catalog struture only. They should be excluded from the top navigation. They are essential for the "Brand view page" to render the category tree.',
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
		Mage_Catalog_Model_Category::ENTITY, 
		'is_catalog_root', 
		$isCatalogRootData
);

//Add group and attributes to all attribute sets
$categoryAllAttributeSetIds = $catalogEavSetup->getAllAttributeSetIds(Mage_Catalog_Model_Category::ENTITY);
foreach($categoryAllAttributeSetIds as $categoryAttributeSetId){
	$categoryAttrGroupId = $catalogEavSetup->getAttributeGroupId(Mage_Catalog_Model_Category::ENTITY, $categoryAttributeSetId, $attrGroupName);
	$catalogEavSetup->addAttributeToSet(
			Mage_Catalog_Model_Category::ENTITY, 
			$categoryAttributeSetId, 
			$categoryAttrGroupId, 
			'is_catalog_root'
	);
}

$installer->endSetup();
