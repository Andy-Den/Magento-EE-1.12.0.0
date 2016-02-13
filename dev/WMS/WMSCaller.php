<?php

// ========== Init setup ========== //
error_reporting(- 1);
require_once ('../../app/Mage.php');
Mage::app();

if (false) {
    $importModel = new Harapartners_HpIntWms_Model_Import();
    $importModel->processTrackings();
}
if (false) {
    $skuArray = array(
        'TIM_25629_9_W'
    ); //Women's Pinkham Notch Leather Fabric Thong Grey-Teal
    $exportModel = new Harapartners_HpIntWms_Model_Export();
    $exportModel->createItemBySkuArray($skuArray);
}

if (false) {
    $importModel = new Harapartners_HpIntWms_Model_Import();
    $importModel->syncInventory(false, 1);
}

if (false) {
    $importModel = new Harapartners_HpIntWms_Model_Import();
    
    $data = array(
        'website_id' => 1 , 
        'store_id' => 1 , 
        'customer_group' => 4
    );
    //$importModel->syncInventory(null);
    $importModel->processTrackings('no_location');
}

if (false) {
    $rma = Mage::getModel('enterprise_rma/rma')->load(2);
    $exportModel = new Harapartners_HpIntWms_Model_Export();
    $exportModel->submitRma($rma);
    $i = 0;
}

if (false) {
    $rmaImport = Mage::getModel('hpintwms/import')->syncRmas('now');
}

if (false) {
    $product = Mage::getModel('catalog/product')->load(71357);
    $product->setRelatedLinkData(array());
    /*$product->setRelatedLinkData(array(
        71358 => array(
            'position' => 0
        )
    ));*/
    $product->save();
}
$breakproint = 222;
