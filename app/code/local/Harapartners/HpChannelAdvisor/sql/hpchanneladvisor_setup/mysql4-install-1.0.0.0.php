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
 * @package     Harapartners\HpChannelAdvisor\sql
 * @author      Steven Hoffman <s.hoffman@harapartners.com>
 * @copyright   Copyright (c) 2013 Harapartners Inc.
 */

// app/code/local/Harapartners/HpChannelAdvisor/sql/hpchanneladvisor_setup/mysql4-install-1.0.0.0.php
$installer = $this;
$installer->startSetup();


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
    
$installer->getConnection()
    ->addColumn($installer->getTable('sales/shipment'), 'service_flag', array(
        'TYPE'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
        'NULLABLE'  => true,
        'COMMENT'   => 'Service Flag For Sync'
    ));
$installer->endSetup();