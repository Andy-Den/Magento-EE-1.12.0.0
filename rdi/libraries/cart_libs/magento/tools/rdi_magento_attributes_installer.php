<?php
    include 'init.php'; 
	
	require_once("../app/Mage.php");				// External script - Load magento framework
    Mage::app();
     
	
	$installer = Mage::getResourceModel('catalog/setup', 'catalog_setup',''); //start the installer 

 


//add related_id if it is not already there
if (!$installer->getAttributeId(Mage_Catalog_Model_Product::ENTITY, 'related_id')) {

$installer->addAttribute(Mage_Catalog_Model_Product::ENTITY, 'related_id', array(
						'group'            			 => 'RDI',//adds the RDI group to every attribute set
                        'type'                       => 'varchar',
                        'label'                      => 'Related ID',
                        'input'                      => 'text',
                        'required'                   => false,
                        'user_defined'               => true,
                        'searchable'                 => false,
                        'filterable'                 => false,
                        'comparable'                 => false,
                        'visible_in_advanced_search' => false,
                       // 'apply_to'                   => Mage_Catalog_Model_Product_Type::TYPE_SIMPLE, <- this can be used for size on the default
                    )
					);
					}
					
//add related_parent_id
if (!$installer->getAttributeId(Mage_Catalog_Model_Product::ENTITY, 'related_parent_id')) {

$installer->addAttribute(Mage_Catalog_Model_Product::ENTITY, 'related_parent_id', array(
						'group'             => 'RDI',
                        'type'                       => 'varchar',
                        'label'                      => 'Related Parent ID',
                        'input'                      => 'text',
                        'required'                   => false,
                        'user_defined'               => true,
                        'searchable'                 => false,
                        'filterable'                 => false,
                        'comparable'                 => false,
                        'visible_in_advanced_search' => false,
                       // 'apply_to'                   => Mage_Catalog_Model_Product_Type::TYPE_SIMPLE,
                    )
					);
	}
	
if (!$installer->getAttributeId(Mage_Catalog_Model_Product::ENTITY, 'itemnum')) {

$installer->addAttribute(Mage_Catalog_Model_Product::ENTITY, 'itemnum', array(
						'group'             => 'RDI',
                        'type'                       => 'varchar',
                        'label'                      => 'Item Number',
                        'input'                      => 'text',
                        'required'                   => false,
                        'user_defined'               => true,
                        'searchable'                 => false,
                        'filterable'                 => false,
                        'comparable'                 => false,
                        'visible_in_advanced_search' => false,
                       // 'apply_to'                   => Mage_Catalog_Model_Product_Type::TYPE_SIMPLE,
                    )
					);
	}

if (!$installer->getAttributeId(Mage_Catalog_Model_Product::ENTITY, 'rdi_deactivated_date')) {

$installer->addAttribute(Mage_Catalog_Model_Product::ENTITY, 'rdi_deactivated_date', array(
						'group'             => 'RDI',
                        'type'                       => 'datetime',
                        'label'                      => 'Deactivated Datetime',
                        'input'                      => 'text',
                        'required'                   => false,
                        'user_defined'               => true,
                        'searchable'                 => false,
                        'filterable'                 => false,
                        'comparable'                 => false,
                        'visible_in_advanced_search' => false,
                       // 'apply_to'                   => Mage_Catalog_Model_Product_Type::TYPE_SIMPLE,
                    )
					);
	}	
	
if (!$installer->getAttributeId(Mage_Catalog_Model_Product::ENTITY, 'rdi_last_updated')) {

$installer->addAttribute(Mage_Catalog_Model_Product::ENTITY, 'rdi_last_updated', array(
						'group'             => 'RDI',
                        'type'                       => 'datetime',
                        'label'                      => 'RDI Last Updated',
                        'input'                      => 'text',
                        'required'                   => false,
                        'user_defined'               => true,
                        'searchable'                 => false,
                        'filterable'                 => false,
                        'comparable'                 => false,
                        'visible_in_advanced_search' => false,
                       // 'apply_to'                   => Mage_Catalog_Model_Product_Type::TYPE_SIMPLE,
                    )
					);
	}	
    //$installer->installEntities();

	   
?>