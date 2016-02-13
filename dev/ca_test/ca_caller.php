<?php

// ========== Init setup ========== //
error_reporting(0);
require_once ('../../app/Mage.php');
Mage::app();
Mage::setIsDeveloperMode(1);

if (false) {
    $testUpdate = new Harapartners_HpOrderWorkflow_Model_Inventory();
    $product = mage::getModel('catalog/product')->setId(1060434);
    $testUpdate->saveWMSAttribute($product, 2);
}
if (false) {
    $caExport = new Harapartners_HpChannelAdvisor_Model_Export();
    $caExportInv = new Harapartners_HpChannelAdvisor_Model_Export_Inventory();
    $caExportInv->syncOneItemQtyPrice(Mage::getModel('catalog/product')->load(1060434));
}
if (false) {
    $caExport = new Harapartners_HpChannelAdvisor_Model_Export();
    $caExport->fullInventorySync(strtotime('-2 minute'));
}
if (true) {
    $caExport = new Harapartners_HpChannelAdvisor_Model_Export();
    $caExport->fullProductSync();
}

if (false) {
	// Import Orders
    $caImport = new Harapartners_HpChannelAdvisor_Model_Import();
    $caImport->importNewCompleteOrders();
}

if (false) {
    $caExport = Mage::getModel('hpchanneladvisor/export');
    $caExport->postTrackingnumbers();
}

if (false) {
    $caExport = Mage::getModel('hpchanneladvisor/import');
    $caExport->authAccount();
}

if (false) {
    $caExport = Mage::getModel('hpchanneladvisor/import');
    $caExport->getAuthKey();
}

//== NEWER ==//
if(false){
	$caExport = new Harapartners_HpChannelAdvisor_Model_Export();
	
	$product = Mage::getModel('catalog/product')->load(1060918);
	$caInventory = new Harapartners_HpChannelAdvisor_Model_Export_Inventory();
	$result = $caInventory->syncOneItem($product);
}