<?php
    include 'init.php'; 
	
	require_once("../app/Mage.php");				// External script - Load magento framework
    Mage::app();
     
	
	$installer = Mage::getResourceModel('catalog/setup', 'catalog_setup',''); //start the installer 

 
$installer->startSetup();
$entityTypeId     = $installer->getEntityTypeId('catalog_category');
$attributeSetId   = $installer->getDefaultAttributeSetId($entityTypeId);
$attributeGroupId = $installer->getDefaultAttributeGroupId($entityTypeId, $attributeSetId);
$installer->addAttribute('catalog_category', 'rdi_DCS',  array(
    'type'     => 'varchar',
    'label'    => 'DCS',
    'input'    => 'text',
    'global'   => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'visible'           => false,
    'required'          => false,
    'user_defined'      => false,
    'default'           => 0
));
$installer->addAttributeToGroup(
    $entityTypeId,
    $attributeSetId,
    $attributeGroupId,
    'rdi_DCS',
    '11'                    //last Magento's attribute position in General tab is 10
);
$attributeId = $installer->getAttributeId($entityTypeId, 'rdi_DCS');
$installer->run("
INSERT INTO `{$installer->getTable('catalog_category_entity_int')}`
(`entity_type_id`, `attribute_id`, `entity_id`, `value`)
    SELECT '{$entityTypeId}', '{$attributeId}', `entity_id`, '1'
        FROM `{$installer->getTable('catalog_category_entity')}`;
");
//this will set data of your custom attribute for root category
Mage::getModel('catalog/category')
    ->load(1)
    ->setImportedCatId(0)
    ->setInitialSetupFlag(true)
    ->save();
//this will set data of your custom attribute for default category
Mage::getModel('catalog/category')
    ->load(2)
    ->setImportedCatId(0)
    ->setInitialSetupFlag(true)
    ->save();
$installer->endSetup();
	   
?>