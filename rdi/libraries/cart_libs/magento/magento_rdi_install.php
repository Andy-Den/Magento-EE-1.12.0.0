<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Staging table databse functions
 *
 * PHP version 5.3
 *
 * @author     PMBliss <pmbliss@retaildimensions.com>
 * @copyright  2005-2015 Retail Dimensions Inc.
 * @version    1.0.0
 */
class rdi_cart_install extends rdi_install {

    public $cart_alter_tables = array(
        "sales_flat_order.rdi_shipper_created" => "ALTER TABLE `sales_flat_order` ADD COLUMN rdi_shipper_created INT(11) DEFAULT 0 NULL COMMENT 'RDi Shipper Created'",
        "sales_flat_order.rdi_upload_status" => "ALTER TABLE `sales_flat_order` ADD COLUMN rdi_upload_status INT(11) DEFAULT 0 NULL COMMENT 'RDi Upload Status'",
        "sales_flat_order.rdi_upload_date" => "ALTER TABLE `sales_flat_order` ADD COLUMN `rdi_upload_date` TIMESTAMP NULL  COMMENT 'RDi Upload Date' AFTER `rdi_upload_status`",
        "sales_flat_order_item.related_id" => "ALTER TABLE `sales_flat_order_item` ADD COLUMN related_id varchar(50) DEFAULT NULL COMMENT 'Related ID'",
        "sales_flat_order_item.related_parent_id" => "ALTER TABLE `sales_flat_order_item` ADD COLUMN related_parent_id varchar(50) DEFAULT NULL COMMENT 'Related Parent ID'",
        "customer_entity.related_id" => "ALTER TABLE `customer_entity` ADD COLUMN related_id VARCHAR(50) NULL",
        "customer_entity.related_id_idx" => "ALTER TABLE `customer_entity` ADD INDEX `related_id` (`related_id`)",
        "catalog_category_entity.related_id" => "ALTER TABLE `catalog_category_entity` ADD related_id VARCHAR(50)",
        "catalog_category_entity.rdi_inactive_date" => "ALTER TABLE `catalog_category_entity` ADD COLUMN rdi_inactive_date TIMESTAMP NULL",
        "catalog_category_entity.related_id_idx" => "ALTER TABLE `catalog_category_entity` ADD INDEX `related_id` (`related_id`)",
        "catalog_category_entity.rdi_last_update" => "ALTER TABLE `catalog_category_entity` ADD COLUMN `rdi_last_update` TIMESTAMP NULL AFTER `rdi_inactive_date`",
        "catalog_product_entity_varchar.value_idx" => "ALTER TABLE `catalog_product_entity_varchar`  ADD  INDEX IDX_RDi_Value (`value`)",
        "sales_flat_creditmemo.rdi_shipper_created" => "ALTER TABLE `sales_flat_creditmemo` ADD COLUMN rdi_shipper_created INT(11) DEFAULT 0 NULL COMMENT 'RDi Shipper Created'",
        "sales_flat_creditmemo.rdi_upload_status" => "ALTER TABLE `sales_flat_creditmemo` ADD COLUMN rdi_upload_status INT(11) DEFAULT 0 NULL COMMENT 'RDi Upload Status'",
        "sales_flat_creditmemo.rdi_upload_date" => "ALTER TABLE `sales_flat_creditmemo` ADD COLUMN `rdi_upload_date` TIMESTAMP NULL  COMMENT 'RDi Upload Date' AFTER `rdi_upload_status`",
        "sales_flat_creditmemo_item.related_id" => "ALTER TABLE `sales_flat_creditmemo_item` ADD COLUMN related_id varchar(50) DEFAULT NULL COMMENT 'Related ID'",
        "sales_flat_creditmemo_item.related_parent_id" => "ALTER TABLE `sales_flat_creditmemo_item` ADD COLUMN related_parent_id varchar(50) DEFAULT NULL COMMENT 'Related Parent ID'",
    );

    public function alter_cart_tables()
    {
        if (!empty($this->cart_alter_tables))
        {
            foreach ($this->cart_alter_tables as $name => $sql)
            {
                $this->exec_alter_table($sql);
            }
        }
    }

    public function install_cart_attributes()
    {
        echo "<h3> Adding Attributes </h3>";

        $mage_path = file_exists('../app/Mage.php') ? '../app/Mage.php' : dirname(dirname($_SERVER['SCRIPT_FILENAME'])) . "/app/Mage.php";
        require_once($mage_path);   // External script - Load magento framework
        Mage::app();

        $installer = Mage::getResourceModel('catalog/setup', 'catalog_setup', ''); //start the installer 

        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');
        $writeConnection = $resource->getConnection('core_write');
        //add size if it is not already there
        if (!$installer->getAttributeId(Mage_Catalog_Model_Product::ENTITY, 'size'))
        {

            $installer->addAttribute(Mage_Catalog_Model_Product::ENTITY, 'size', array(
                'group' => 'General', //adds the General group to every attribute set
                'type' => 'int',
                'label' => 'Size',
                'input' => 'select',
                'required' => false,
                'user_defined' => true,
                'searchable' => true,
                'filterable' => true,
                'comparable' => true,
                'visible_in_advanced_search' => true,
                'attribute_set' => 'Default',
                'apply_to' => Mage_Catalog_Model_Product_Type::TYPE_SIMPLE,
                    )
            );
        }
//going to load color if it is there and update it into the general group                                        
//add related_id if it is not already there
        if (!$installer->getAttributeId(Mage_Catalog_Model_Product::ENTITY, 'related_id'))
        {

            $installer->addAttribute(Mage_Catalog_Model_Product::ENTITY, 'related_id', array(
                'group' => 'RDI', //adds the RDI group to every attribute set
                'type' => 'varchar',
                'label' => 'Related ID',
                'input' => 'text',
                'required' => false,
                'user_defined' => true,
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_in_advanced_search' => false,
                    // 'apply_to'                   => Mage_Catalog_Model_Product_Type::TYPE_SIMPLE, <- this can be used for size on the default
                    )
            );
        }

//add related_parent_id
        if (!$installer->getAttributeId(Mage_Catalog_Model_Product::ENTITY, 'related_parent_id'))
        {

            $installer->addAttribute(Mage_Catalog_Model_Product::ENTITY, 'related_parent_id', array(
                'group' => 'RDI',
                'type' => 'varchar',
                'label' => 'Related Parent ID',
                'input' => 'text',
                'required' => false,
                'user_defined' => true,
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_in_advanced_search' => false,
                    // 'apply_to'                   => Mage_Catalog_Model_Product_Type::TYPE_SIMPLE,
                    )
            );
        }

        if (!$installer->getAttributeId(Mage_Catalog_Model_Product::ENTITY, 'itemnum'))
        {

            $installer->addAttribute(Mage_Catalog_Model_Product::ENTITY, 'itemnum', array(
                'group' => 'RDI',
                'type' => 'varchar',
                'label' => 'Item Number',
                'input' => 'text',
                'required' => false,
                'user_defined' => true,
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_in_advanced_search' => false,
                    // 'apply_to'                   => Mage_Catalog_Model_Product_Type::TYPE_SIMPLE,
                    )
            );
        }

        if (!$installer->getAttributeId(Mage_Catalog_Model_Product::ENTITY, 'rdi_deactivated_date'))
        {

            $installer->addAttribute(Mage_Catalog_Model_Product::ENTITY, 'rdi_deactivated_date', array(
                'group' => 'RDI',
                'type' => 'datetime',
                'label' => 'Deactivated Datetime',
                'input' => 'text',
                'required' => false,
                'user_defined' => true,
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_in_advanced_search' => false,
                    // 'apply_to'                   => Mage_Catalog_Model_Product_Type::TYPE_SIMPLE,
                    )
            );
        }

        if (!$installer->getAttributeId(Mage_Catalog_Model_Product::ENTITY, 'rdi_last_updated'))
        {

            $installer->addAttribute(Mage_Catalog_Model_Product::ENTITY, 'rdi_last_updated', array(
                'group' => 'RDI',
                'type' => 'datetime',
                'label' => 'RDI Last Updated',
                'input' => 'text',
                'required' => false,
                'user_defined' => true,
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_in_advanced_search' => false,
                    // 'apply_to'                   => Mage_Catalog_Model_Product_Type::TYPE_SIMPLE,
                    )
            );
        }

        //add rdi_avail
        if (!$installer->getAttributeId(Mage_Catalog_Model_Product::ENTITY, 'rdi_avail'))
        {

            $installer->addAttribute(Mage_Catalog_Model_Product::ENTITY, 'rdi_avail', array(
                'group' => 'RDI',
                'type' => 'varchar',
                'label' => 'RDi Avail Field',
                'input' => 'text',
                'required' => false,
                'user_defined' => true,
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_in_advanced_search' => false,
                    // 'apply_to'                   => Mage_Catalog_Model_Product_Type::TYPE_SIMPLE,
                    )
            );
        }
    }

}

?>
