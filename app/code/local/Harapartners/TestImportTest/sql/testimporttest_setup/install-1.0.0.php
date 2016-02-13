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
// TODO use a table prefix...
$installer = $this;
/* @var $installer Mage_Eav_Model_Entity_Setup */
$installer->startSetup();
/*$installer->run("
    INSERT INTO  `{$this->getTable('sales/order_status')}` (`status`, `label`) 
        VALUES ('edi_sent',  'Edi Sent'), ('edi_sent_failed',  'Edi Send Failed');
    INSERT INTO  `{$this->getTable('sales/order_status_state')}` (`status`, `state`, `is_default`)
        VALUES ('ebay_complete',  'processing',  '0'), ('edi_sent_failed',  'processing',  '0');
");*/
$shoemartShoes = 'shoemart_shoe';
$catProductEntityType = (int) $installer->getEntityTypeId('catalog_product');
// $installer->addAttributeSet($catProductEntityType, $shoemartShoes);
// Set based on defualt // NOTE: Need to use this, otheriwse stuff in Gneral does not get set.
$attrSet = Mage::getModel('eav/entity_attribute_set');
$attrSet->setAttributeSetName($shoemartShoes);
//try {
//    if ($smShoesAttributeSetId = $installer->getAttributeSetId($catProductEntityType, $shoemartShoes)) {
//        $attrSet->setAttributeSetId($smShoesAttributeSetId);
//    }
//} catch (Exception $e) {}
$info = $installer->getAttributeSet($catProductEntityType, $shoemartShoes, 'attribute_set_id');
$attrSet->setEntityTypeId($catProductEntityType);
$attrSet->save();
$attrSet->initFromSkeleton($installer->getDefaultAttributeSetId($catProductEntityType));
$attrSet->save();

$smShoesAttributeSetId = $installer->getAttributeSetId($catProductEntityType, $shoemartShoes);
if (! $smShoesAttributeSetId) {
    throw new Exception('Can not retrive attribute set Id !!');
}
$vendorInfoGroup = array(
    'vendor_code' => array(
        'sort_order' => 100 , 
        'type' => 'varchar' , 
        'backend' => '' , 
        'frontend' => '' , 
        'label' => 'Vendor Code' , 
        'note' => '' , 
        'input' => 'text' , 
        'class' => '' , 
        'source' => '' , 
        // 'default'=> 0, //Disabled by default
        'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL , 
        'visible' => true , 
        'required' => false , 
        'user_defined' => true , 
        'visible_on_front' => false , 
        'unique' => false , 
        'is_configurable' => false , 
        'used_for_promo_rules' => false
    ) , 
    'vendor_name' => array(
        'sort_order' => 110 , 
        'type' => 'varchar' , 
        'backend' => '' , 
        'frontend' => '' , 
        'label' => 'Vendor Name' , 
        'note' => '' , 
        'input' => 'text' , 
        'class' => '' , 
        'source' => '' , 
        'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL , 
        'visible' => true , 
        'required' => false , 
        'user_defined' => true , 
        'visible_on_front' => false , 
        'unique' => false , 
        'is_configurable' => false , 
        'used_for_promo_rules' => false
    ) , 
    'stock_number' => array(
        'sort_order' => 120 , 
        'type' => 'varchar' , 
        'backend' => '' , 
        'frontend' => '' , 
        'label' => 'Vendor Stock Number' , 
        'note' => '' , 
        'input' => 'text' , 
        'class' => '' , 
        'source' => '' , 
        'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL , 
        'visible' => true , 
        'required' => false , 
        'user_defined' => true , 
        'visible_on_front' => false , 
        'unique' => false , 
        'is_configurable' => false , 
        'used_for_promo_rules' => false
    ) , 
    'stock_name' => array(
        'sort_order' => 130 , 
        'type' => 'varchar' , 
        'backend' => '' , 
        'frontend' => '' , 
        'label' => 'Vendor Stock Name' , 
        'note' => '' , 
        'input' => 'text' , 
        'class' => '' , 
        'source' => '' , 
        'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL , 
        'visible' => true , 
        'required' => false , 
        'user_defined' => true , 
        'visible_on_front' => false , 
        'unique' => false , 
        'is_configurable' => false , 
        'used_for_promo_rules' => false
    ) , 
    'rpro_item_number' => array(
        'sort_order' => 140 , 
        'type' => 'varchar' , 
        'backend' => '' , 
        'frontend' => '' , 
        'label' => 'Vendro Rpo Number' , 
        'note' => '' , 
        'input' => 'text' , 
        'class' => '' , 
        'source' => '' , 
        'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL , 
        'visible' => true , 
        'required' => false , 
        'user_defined' => true , 
        'visible_on_front' => false , 
        'unique' => false , 
        'is_configurable' => false , 
        'used_for_promo_rules' => false
    )
);

$caInfoGroup = array(
    'ca_parent_sku' => array(
        'sort_order' => 100 , 
        'type' => 'varchar' , 
        'backend' => '' , 
        'frontend' => '' , 
        'label' => 'Ca parent SKU' , 
        'note' => '' , 
        'input' => 'text' , 
        'class' => '' , 
        'source' => '' , 
        'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL , 
        'visible' => true , 
        'required' => false , 
        'user_defined' => true , 
        'visible_on_front' => false , 
        'unique' => false , 
        'is_configurable' => false , 
        'used_for_promo_rules' => false
    ) , 
    'ca_title' => array(
        'sort_order' => 110 , 
        'type' => 'varchar' , 
        'backend' => '' , 
        'frontend' => '' , 
        'label' => 'CA Title' , 
        'note' => '' , 
        'input' => 'text' , 
        'class' => '' , 
        'source' => '' , 
        'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL , 
        'visible' => true , 
        'required' => false , 
        'user_defined' => true , 
        'visible_on_front' => false , 
        'unique' => false , 
        'is_configurable' => false , 
        'used_for_promo_rules' => false
    ) , 
    'ca_amz_search_color' => array(
        'sort_order' => 120 , 
        'type' => 'varchar' , 
        'backend' => '' , 
        'frontend' => '' , 
        'label' => 'CA Amz search color' , 
        'note' => '' , 
        'input' => 'text' , 
        'class' => '' , 
        'source' => '' , 
        'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL , 
        'visible' => true , 
        'required' => false , 
        'user_defined' => true , 
        'visible_on_front' => false , 
        'unique' => false , 
        'is_configurable' => false , 
        'used_for_promo_rules' => false
    ) , 
    'ca_ebay_search_color' => array(
        'sort_order' => 130 , 
        'type' => 'varchar' , 
        'backend' => '' , 
        'frontend' => '' , 
        'label' => 'CA Ebay Search Color' , 
        'note' => '' , 
        'input' => 'text' , 
        'class' => '' , 
        'source' => '' , 
        'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL , 
        'visible' => true , 
        'required' => false , 
        'user_defined' => true , 
        'visible_on_front' => false , 
        'unique' => false , 
        'is_configurable' => false , 
        'used_for_promo_rules' => false
    ) , 
    'ca_ebay_price' => array(
        'sort_order' => 140 , 
        'type' => 'varchar' , 
        'backend' => '' , 
        'frontend' => '' , 
        'label' => 'Ca Ebay Price' , 
        'note' => '' , 
        'input' => 'text' , 
        'class' => '' , 
        'source' => '' , 
        'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL , 
        'visible' => true , 
        'required' => false , 
        'user_defined' => true , 
        'visible_on_front' => false , 
        'unique' => false , 
        'is_configurable' => false , 
        'used_for_promo_rules' => false
    ) , 
    'ca_amz_material' => array(
        'sort_order' => 140 , 
        'type' => 'varchar' , 
        'backend' => '' , 
        'frontend' => '' , 
        'label' => 'Ca Amz Material' , 
        'note' => '' , 
        'input' => 'text' , 
        'class' => '' , 
        'source' => '' , 
        'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL , 
        'visible' => true , 
        'required' => false , 
        'user_defined' => true , 
        'visible_on_front' => false , 
        'unique' => false , 
        'is_configurable' => false , 
        'used_for_promo_rules' => false
    )
);

$shoeAttributesGroup = array(
    'shoe_waterproof' => array(
        'sort_order' => 100 , 
        'type' => 'int' , 
        'backend' => '' , 
        'frontend' => '' , 
        'label' => 'Is Waterproof' , 
        'note' => '' , 
        'input' => 'boolean' , 
        'class' => '' , 
        'source' => '' , 
        'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL , 
        'visible' => true , 
        'required' => false , 
        'user_defined' => true , 
        'visible_on_front' => false , 
        'unique' => false , 
        'is_configurable' => false , 
        'used_for_promo_rules' => false
    ) , 
    'shoe_eco_friendly' => array(
        'sort_order' => 110 , 
        'type' => 'int' , 
        'backend' => '' , 
        'frontend' => '' , 
        'label' => 'Is Eco Friendy' , 
        'note' => '' , 
        'input' => 'boolean' , 
        'class' => '' , 
        'source' => '' , 
        'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL , 
        'visible' => true , 
        'required' => false , 
        'user_defined' => true , 
        'visible_on_front' => false , 
        'unique' => false , 
        'is_configurable' => false , 
        'used_for_promo_rules' => false
    ) , 
    'shoe_occupational' => array(
        'sort_order' => 120 , 
        'type' => 'int' , 
        'backend' => '' , 
        'frontend' => '' , 
        'label' => 'Is Occupational' , 
        'note' => '' , 
        'input' => 'boolean' , 
        'class' => '' , 
        'source' => '' , 
        'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL , 
        'visible' => true , 
        'required' => false , 
        'user_defined' => true , 
        'visible_on_front' => false , 
        'unique' => false , 
        'is_configurable' => false , 
        'used_for_promo_rules' => false
    ) , 
    'shoe_steel_toe' => array(
        'sort_order' => 130 , 
        'type' => 'int' , 
        'backend' => '' , 
        'frontend' => '' , 
        'label' => 'Ca Ebay Price' , 
        'note' => '' , 
        'input' => 'boolean' , 
        'class' => '' , 
        'source' => '' , 
        'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL , 
        'visible' => true , 
        'required' => false , 
        'user_defined' => true , 
        'visible_on_front' => false , 
        'unique' => false , 
        'is_configurable' => false , 
        'used_for_promo_rules' => false
    ) , 
    'shoe_material' => array(
        'sort_order' => 140 , 
        'type' => 'varchar' , 
        'backend' => '' , 
        'frontend' => '' , 
        'label' => 'Shoe Material' , 
        'note' => '' , 
        'input' => 'text' , 
        'class' => '' , 
        'source' => '' , 
        'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL , 
        'visible' => true , 
        'required' => false , 
        'user_defined' => true , 
        'visible_on_front' => false , 
        'unique' => false , 
        'is_configurable' => false , 
        'used_for_promo_rules' => false
    )
);

$shoeConfigAttributes = array(
	'shoe_color_config' => array(
        'sort_order' => 130 , 
        'type' => 'int' , 
        'backend' => '' , 
        'frontend' => '' , 
        'label' => 'Shoe Color Used For Config' , 
        'note' => '' , 
        'input' => 'select' , 
        'class' => '' , 
        'source' => 'eav/entity_attribute_source_table' , 
        'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL , 
        'visible' => true , 
        'required' => false , 
        'user_defined' => true , 
        'visible_on_front' => false , 
        'unique' => false , 
        'is_configurable' => true , 
        'used_for_promo_rules' => false
    ),
    'shoe_size' => array(
        'sort_order' => 100 , 
        'type' => 'int' , 
        'backend' => '' , 
        'frontend' => '' , 
        'label' => 'Shoe Size' , 
        'note' => '' , 
        'input' => 'select' , 
        'class' => '' , 
        'source' => 'eav/entity_attribute_source_table' , 
        'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL , 
        'visible' => true , 
        'required' => false , 
        'user_defined' => true , 
        'visible_on_front' => false , 
        'unique' => false , 
        'is_configurable' => true , 
        'used_for_promo_rules' => false
    ) , 
    'shoe_width' => array(
        'sort_order' => 110 , 
        'type' => 'int' , 
        'backend' => '' , 
        'frontend' => '' , 
        'label' => 'Shoe Width' , 
        'note' => '' , 
        'input' => 'select' , 
        'class' => '' , 
        'source' => 'eav/entity_attribute_source_table' , 
        'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL , 
        'visible' => true , 
        'required' => false , 
        'user_defined' => true , 
        'visible_on_front' => false , 
        'unique' => false , 
        'is_configurable' => true , 
        'used_for_promo_rules' => false
    ) , 
    'shoe_color_manu' => array(
        'sort_order' => 120 , 
        'type' => 'varchar' , 
        'backend' => '' , 
        'frontend' => '' , 
        'label' => 'Shoe Color Manu' , 
        'note' => '' , 
        'input' => 'text' , 
        'class' => '' , 
        'source' => '' , 
        'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL , 
        'visible' => true , 
        'required' => false , 
        'user_defined' => true , 
        'visible_on_front' => false , 
        'unique' => false , 
        'is_configurable' => false , 
        'used_for_promo_rules' => false
    ) , 
);

$smSotreInfoGroup = array(
    'sm_price' => array(
        'sort_order' => 100 , 
        'type' => 'decimal' , 
        'backend' => 'catalog/product_attribute_backend_price' , 
        'frontend' => '' , 
        'label' => 'SM Psyical Store Price' , 
        'note' => '' , 
        'input' => 'price' , 
        'class' => '' , 
        'source' => '' , 
        'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL , 
        'visible' => true , 
        'required' => false , 
        'user_defined' => true , 
        'visible_on_front' => false , 
        'unique' => false , 
        'is_configurable' => false , 
        'used_for_promo_rules' => false
    ) , 
    'sm_sale_price' => array(
        'sort_order' => 110 , 
        'type' => 'decimal' , 
        'backend' => 'catalog/product_attribute_backend_price' , 
        'frontend' => '' , 
        'label' => 'SM Psyical Store Sale Price' , 
        'note' => '' , 
        'input' => 'price' , 
        'class' => '' , 
        'source' => '' , 
        'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL , 
        'visible' => true , 
        'required' => false , 
        'user_defined' => true , 
        'visible_on_front' => false , 
        'unique' => false , 
        'is_configurable' => false , 
        'used_for_promo_rules' => false
    ) , 
    'sm_qty' => array(
        'sort_order' => 120 , 
        'type' => 'varchar' , 
        'backend' => '' , 
        'frontend' => '' , 
        'label' => 'SM Psyical Store QTY' , 
        'note' => '' , 
        'input' => 'text' , 
        'class' => '' , 
        'source' => '' , 
        'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL , 
        'visible' => true , 
        'required' => false , 
        'user_defined' => true , 
        'visible_on_front' => false , 
        'unique' => false , 
        'is_configurable' => false , 
        'used_for_promo_rules' => false
    )
);

$random1Group = array(
    'class' => array(
        'sort_order' => 100 , 
        'type' => 'varchar' , 
        'backend' => '' , 
        'frontend' => '' , 
        'label' => 'Class' , 
        'note' => '' , 
        'input' => 'text' , 
        'class' => '' , 
        'source' => '' , 
        'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL , 
        'visible' => true , 
        'required' => false , 
        'user_defined' => true , 
        'visible_on_front' => false , 
        'unique' => false , 
        'is_configurable' => false , 
        'used_for_promo_rules' => false
    ) , 
    'order_class' => array(
        'sort_order' => 110 , 
        'type' => 'varchar' , 
        'backend' => '' , 
        'frontend' => '' , 
        'label' => 'Order Class' , 
        'note' => '' , 
        'input' => 'text' , 
        'class' => '' , 
        'source' => '' , 
        'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL , 
        'visible' => true , 
        'required' => false , 
        'user_defined' => true , 
        'visible_on_front' => false , 
        'unique' => false , 
        'is_configurable' => false , 
        'used_for_promo_rules' => false
    ) , 
    'inventory_control' => array(
        'sort_order' => 120 , 
        'type' => 'varchar' , 
        'backend' => '' , 
        'frontend' => '' , 
        'label' => 'Inventory Control' , 
        'note' => '' , 
        'input' => 'text' , 
        'class' => '' , 
        'source' => '' , 
        'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL , 
        'visible' => true , 
        'required' => false , 
        'user_defined' => true , 
        'visible_on_front' => false , 
        'unique' => false , 
        'is_configurable' => false , 
        'used_for_promo_rules' => false
    )
);

$random2Group = array(
    'store_min' => array(
        'sort_order' => 100 , 
        'type' => 'varchar' , 
        'backend' => '' , 
        'frontend' => '' , 
        'label' => 'Store Min' , 
        'note' => '' , 
        'input' => 'text' , 
        'class' => '' , 
        'source' => '' , 
        'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL , 
        'visible' => true , 
        'required' => false , 
        'user_defined' => true , 
        'visible_on_front' => false , 
        'unique' => false , 
        'is_configurable' => false , 
        'used_for_promo_rules' => false
    ) , 
    'store_max' => array(
        'sort_order' => 110 , 
        'type' => 'varchar' , 
        'backend' => '' , 
        'frontend' => '' , 
        'label' => 'Store Max' , 
        'note' => '' , 
        'input' => 'text' , 
        'class' => '' , 
        'source' => '' , 
        'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL , 
        'visible' => true , 
        'required' => false , 
        'user_defined' => true , 
        'visible_on_front' => false , 
        'unique' => false , 
        'is_configurable' => false , 
        'used_for_promo_rules' => false
    ) , 
    'company_min' => array(
        'sort_order' => 120 , 
        'type' => 'varchar' , 
        'backend' => '' , 
        'frontend' => '' , 
        'label' => 'Comapany Min' , 
        'note' => '' , 
        'input' => 'text' , 
        'class' => '' , 
        'source' => '' , 
        'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL , 
        'visible' => true , 
        'required' => false , 
        'user_defined' => true , 
        'visible_on_front' => false , 
        'unique' => false , 
        'is_configurable' => false , 
        'used_for_promo_rules' => false
    ) , 
    'company_max' => array(
        'sort_order' => 130 , 
        'type' => 'varchar' , 
        'backend' => '' , 
        'frontend' => '' , 
        'label' => 'Company Max' , 
        'note' => '' , 
        'input' => 'text' , 
        'class' => '' , 
        'source' => '' , 
        'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL , 
        'visible' => true , 
        'required' => false , 
        'user_defined' => true , 
        'visible_on_front' => false , 
        'unique' => false , 
        'is_configurable' => false , 
        'used_for_promo_rules' => false
    )
);

$allSMgroupsHere = array(
    'vendor_info' => $vendorInfoGroup , 
    'ca_info' => $caInfoGroup , 
    'shoe_attributes' => $shoeAttributesGroup , 
    'shoe_attributes_config' => $shoeConfigAttributes , 
    'sm_store_info' => $smSotreInfoGroup , 
    'class' => $random1Group , 
    'min_max' => $random2Group
);

$nameMap = array(
    'vendor_info' => 'Vendor Info' , 
    'ca_info' => 'CA Info' , 
    'shoe_attributes' => 'Shoe Attributes' , 
    'shoe_attributes_config' => 'Shoe Config Attributes' , 
    'sm_store_info' => 'Retail Store Info' , 
    'class' => 'Class Info' , 
    'min_max' => 'Min Max Info'
);

$sortOrder = 9090;
foreach ($allSMgroupsHere as $groupCode => $groupAttributes) {
    if (empty($nameMap[$groupCode])) {
        continue;
    }
    
    $sortOrder += 10;
    $groupName = $nameMap[$groupCode];
    $installer->addAttributeGroup(Mage_Catalog_Model_Product::ENTITY, $smShoesAttributeSetId, $groupName, $sortOrder);
    $currentGroupId = $installer->getAttributeGroupId(Mage_Catalog_Model_Product::ENTITY, $smShoesAttributeSetId, $groupName);
    foreach ($groupAttributes as $name => $data) {
        $installer->addAttribute(Mage_Catalog_Model_Product::ENTITY, $name, $data);
        $installer->addAttributeToSet(Mage_Catalog_Model_Product::ENTITY, $smShoesAttributeSetId, $currentGroupId, $name);
    }
}

$installer->endSetup();

//
//$vendorInfoGroupName = 'Vendor Info';
//$defaultSortOrder = 9100;
//$installer->addAttributeGroup(Mage_Catalog_Model_Product::ENTITY, $smShoesAttributeSetId, $vendorInfoGroupName, $defaultSortOrder);
//$vendorInfoGroupId = $installer->getAttributeGroupId(Mage_Catalog_Model_Product::ENTITY, $smShoesAttributeSetId, $vendorInfoGroupName);
//foreach ($vendorInfoGroup as $name => $data) {
//    $installer->addAttribute(Mage_Catalog_Model_Product::ENTITY, $name, $data);
//    $installer->addAttributeToSet(Mage_Catalog_Model_Product::ENTITY, $smShoesAttributeSetId, $vendorInfoGroupId, $name);
//}
//
//
//
//
//$installer->endSetup();
//// === END ==//
//
//
//$catalogEavSetup = new Mage_Catalog_Model_Resource_Eav_Mysql4_Setup();
//$attrGroupName = 'OrderFlow Stock Values';
//$defaultSortOrder = 9999; //Put at the end
//$subscriptionEnabledData = array(
//    'sort_order' => 100 , 
//    'type' => 'int' , 
//    'backend' => '' , 
//    'frontend' => '' , 
//    'label' => 'WMS Stock Count' , 
//    'note' => '' , 
//    'input' => 'text' , 
//    'class' => '' , 
//    'source' => '' , 
//    'default' => 0 ,  //Disabled by default
//    'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL , 
//    'visible' => true , 
//    'required' => false , 
//    'user_defined' => true , 
//    'visible_on_front' => false , 
//    'unique' => false , 
//    'is_configurable' => false , 
//    'used_for_promo_rules' => false
//);
//$subscriptionFrequencyData = array(
//    'sort_order' => 110 , 
//    'type' => 'int' , 
//    'backend' => '' , 
//    'frontend' => '' , 
//    'label' => 'DropShip Stock Count' , 
//    'note' => '' , 
//    'input' => 'text' , 
//    'class' => '' , 
//    'source' => '' , 
//    'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL , 
//    'visible' => true , 
//    'required' => false , 
//    'user_defined' => false , 
//    'visible_on_front' => false , 
//    'unique' => false , 
//    'is_configurable' => false , 
//    'used_for_promo_rules' => false
//);
//$subscriptionFrequencyDate = array(
//    'sort_order' => 120 , 
//    'type' => 'date' , 
//    'backend' => '' , 
//    'frontend' => '' , 
//    'label' => 'Dropship Valid Untill' , 
//    'note' => '' , 
//    'input' => 'date' , 
//    'class' => '' , 
//    'source' => '' , 
//    'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL , 
//    'visible' => true , 
//    'required' => false , 
//    'user_defined' => false , 
//    'visible_on_front' => false , 
//    'unique' => false , 
//    'is_configurable' => false , 
//    'used_for_promo_rules' => false
//);
//
//$catalogEavSetup->addAttribute(Mage_Catalog_Model_Product::ENTITY, //Harapartners_HpSubscription_Helper_Data::SUBSCRIPTION_ATTRIBUTE_PREFIX . 'is_allowed',
//'orderflow_wms_stock', $subscriptionEnabledData);
//$catalogEavSetup->addAttribute(Mage_Catalog_Model_Product::ENTITY, //Harapartners_HpSubscription_Helper_Data::SUBSCRIPTION_ATTRIBUTE_PREFIX . 'allowed_freqs', //Max attr code length is 30
//'orderflow_dropship_stock', $subscriptionFrequencyData);
//$catalogEavSetup->addAttribute(Mage_Catalog_Model_Product::ENTITY, //Harapartners_HpSubscription_Helper_Data::SUBSCRIPTION_ATTRIBUTE_PREFIX . 'allowed_freqs', //Max attr code length is 30
//'orderflow_dropship_stock_date', $subscriptionFrequencyDate);
//
////Add group and attributes to all attribute sets
//$productAllAttributeSetIds = $catalogEavSetup->getAllAttributeSetIds(Mage_Catalog_Model_Product::ENTITY);
//foreach ($productAllAttributeSetIds as $productAttributeSetId) {
//    $catalogEavSetup->addAttributeGroup(Mage_Catalog_Model_Product::ENTITY, $productAttributeSetId, $attrGroupName, $defaultSortOrder);
//    $productSubscriptionGroupId = $catalogEavSetup->getAttributeGroupId(Mage_Catalog_Model_Product::ENTITY, $productAttributeSetId, $attrGroupName);
//    
//    // ===== WMS STOCK ===== //
//    $catalogEavSetup->addAttributeToSet(Mage_Catalog_Model_Product::ENTITY, $productAttributeSetId, $productSubscriptionGroupId, //Harapartners_HpSubscription_Helper_Data::SUBSCRIPTION_ATTRIBUTE_PREFIX . 'is_allowed'
//    'orderflow_wms_stock');
//    // ===== DROPSHIP STOCK ===== //
//    $catalogEavSetup->addAttributeToSet(Mage_Catalog_Model_Product::ENTITY, $productAttributeSetId, $productSubscriptionGroupId, //Harapartners_HpSubscription_Helper_Data::SUBSCRIPTION_ATTRIBUTE_PREFIX . 'allowed_freqs'
//    'orderflow_dropship_stock');
//    // ===== DROPSHIP STOCK DATE ===== //
//    $catalogEavSetup->addAttributeToSet(Mage_Catalog_Model_Product::ENTITY, $productAttributeSetId, $productSubscriptionGroupId, //Harapartners_HpSubscription_Helper_Data::SUBSCRIPTION_ATTRIBUTE_PREFIX . 'allowed_freqs'
//    'orderflow_dropship_stock_date');
//}
//$installer->endSetup();
