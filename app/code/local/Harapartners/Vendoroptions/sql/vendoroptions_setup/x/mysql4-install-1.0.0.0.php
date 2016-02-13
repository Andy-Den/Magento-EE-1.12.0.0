<?php 
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User Software Agreement (EULA).
 * It is also available through the world-wide-web at this URL:
 * http://www.harapartners.com/license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to eula@harapartners.com so we can send you a copy immediately.
 * 
 * @package     Harapartners\Webservice\sql
 * @author      Richu Leong <r.leong@harapartners.com>
 * @copyright   Copyright (c) 2012 Harapartners Inc.
 */

// app/code/local/Harapartners/Vendoroptions/sql/vendoroptions_setup/mysql4-install-0.1.0.0.php
$installer = $this;
$installer->startSetup();


$table = $installer->getConnection()
    ->newTable($installer->getTable('vendoroptions/vendoroptions_configure'))
    ->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Vendor Id')
	->addColumn('code', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'nullable'  => false,
        'default'   => '0',
        ), 'Vendor Code')
    ->addColumn('name', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'nullable'  => false,
        'default'   => '0',
        ), 'Vendor Name')
	->addColumn('account_number', Varien_Db_Ddl_Table::TYPE_VARCHAR, '255', array(
        ), 'Main Account Number')
	->addColumn('phone', Varien_Db_Ddl_Table::TYPE_VARCHAR, '255', array(
        ), 'Vendor Contact Phone')
	->addColumn('size_type', Varien_Db_Ddl_Table::TYPE_VARCHAR, '255', array(
        ), 'Default sizing')
	->addColumn('url_path', Varien_Db_Ddl_Table::TYPE_VARCHAR, '255', array(
        ), 'Current URL path for Cat')
	->addColumn('has_map', Varien_Db_Ddl_Table::TYPE_BOOLEAN, null, array(
       'nullable'  => true,
       ), 'Does vendor have a MAP')
    ->addColumn('dropship', Varien_Db_Ddl_Table::TYPE_BOOLEAN, null, array(
        'nullable'  => false,
        'default'   => 0,
        ), 'Vendor Supports DropShip')
	->addColumn('dropship_account', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'nullable'  => false,
        'default'   => 0,
        ), 'Dropship Account Number')
    ->addColumn('dropship_method', Varien_Db_Ddl_Table::TYPE_VARCHAR, '32', array(
        ), 'The way to communicate with the vendor')
	->addColumn('dropship_email', Varien_Db_Ddl_Table::TYPE_VARCHAR, '255', array(
        ), 'Email Number of vendor')
	->addColumn('dropship_fax', Varien_Db_Ddl_Table::TYPE_VARCHAR, '255', array(
        ), 'Fax Number of vendor')
	->addColumn('edi', Varien_Db_Ddl_Table::TYPE_BOOLEAN, null, array(
        ), 'Does Vendor USE edi to talk')
	->addColumn('edi_inventory_feed', Varien_Db_Ddl_Table::TYPE_BOOLEAN, null, array(
        ), 'Does Vendor provide an Inventory feed')
	->addColumn('edi_inventory_control', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        ), 'Edi inventory control method')
	->addColumn('ship_usps', Varien_Db_Ddl_Table::TYPE_BOOLEAN, null, array(
       'nullable'  => true,
       ), 'Does vendor use USPS')
	->addColumn('ship_fedex', Varien_Db_Ddl_Table::TYPE_BOOLEAN, null, array(
       'nullable'  => true,
       ), 'Does vendor use Fedex')
	->addColumn('edi_doc_id', Varien_Db_Ddl_Table::TYPE_VARCHAR, '255', array(
        ), 'EDI document ID')
	->addColumn('edi_catalog_id', Varien_Db_Ddl_Table::TYPE_VARCHAR, '255', array(
        ), 'EDI catalog ID')
	->addColumn('sell_ebay', Varien_Db_Ddl_Table::TYPE_BOOLEAN, null, array(
       'nullable'  => true,
       ), 'Does vendor allow Ebay')
	->addColumn('sell_amz', Varien_Db_Ddl_Table::TYPE_BOOLEAN, null, array(
       'nullable'  => true,
       ), 'Does vendor allow Amazon')
	->addColumn('ship_intl', Varien_Db_Ddl_Table::TYPE_BOOLEAN, null, array(
       'nullable'  => true,
       ), 'Does vendor allow International')
	->addColumn('edi_drop_ship', Varien_Db_Ddl_Table::TYPE_BOOLEAN, null, array(
       'nullable'  => true,
       ), 'Does vendor accept EDI dropship')
	->addColumn('edi_special', Varien_Db_Ddl_Table::TYPE_BOOLEAN, null, array(
       'nullable'  => true,
       ), 'Does vendor accept EDI special orders')
	->addColumn('ebay_schedule', Varien_Db_Ddl_Table::TYPE_VARCHAR, '255', array(
        ), 'Ebay Schedule')
	->addColumn('amz_label', Varien_Db_Ddl_Table::TYPE_VARCHAR, '255', array(
        ), 'Amazon Labels in use')
    ->addColumn('fedex_codes', Varien_Db_Ddl_Table::TYPE_VARCHAR, '255', array(
       ), 'Vendor FedEx Codes')
	->addColumn('ups_codes', Varien_Db_Ddl_Table::TYPE_VARCHAR, '255', array(
        ), 'Vendor UPS Codes')
	->addColumn('usps_codes', Varien_Db_Ddl_Table::TYPE_VARCHAR, '255', array(
        ), 'Vendor USPS Codes')     
	->addColumn('updated_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        'nullable'  => false,
        ), 'Updated At')
    ->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        'nullable'  => false,
        ), 'Created At')
    ->setComment('Basic Vendor Information');
$installer->getConnection()->createTable( $table );

$installer->endSetup();
/*
$installer->getConnection()
    ->addColumn($installer->getTable('sales/order'), 'service_type', array(
        'TYPE'      => Varien_Db_Ddl_Table::TYPE_TEXT,
        'LENGTH'    => 255,
        'NULLABLE'  => true,
        'COMMENT'   => 'Amazon or eBay Service Type'
    ));
$installer->getConnection()
    ->addColumn($installer->getTable('sales/order'), 'service_transactionid', array(
        'TYPE'      => Varien_Db_Ddl_Table::TYPE_TEXT,
        'LENGTH'    => 255,
        'NULLABLE'  => true,
        'COMMENT'   => 'Amazon or eBay Transaction ID'
    ));
$installer->getConnection()
    ->addColumn($installer->getTable('sales/order'), 'service_data', array(
        'TYPE'      => Varien_Db_Ddl_Table::TYPE_TEXT,
        'LENGTH'    => 255,
        'NULLABLE'  => true,
        'COMMENT'   => 'Amazon or eBay Transaction Data'
    ));
*/