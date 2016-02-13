<?php

// ========== Init setup ========== //
error_reporting(- 1);
require_once ('../../app/Mage.php');
Mage::app();

ini_set("soap.wsdl_cache_enabled", 0);
if (false) {
    $helper = Mage::helper('hpsalesforce');
    $helper->initSalesforceConnection();
}

if (false) {
    $order = Mage::getModel('sales/order')->load(93858);
    $model = new Harapartners_HpSalesforce_Model_Export();
    $model->orderMain($order);
}

if (false) {
    $order = Mage::getModel('sales/order')->load(93858);
    $shipment = $order->getShipmentsCollection()->getFirstItem();
    if ($shipment->getId()) {
        $model = new Harapartners_HpSalesforce_Model_Export();
        $model->shippingUpdate($shipment);
    }
}

if (false) {
    $order = Mage::getModel('sales/order')->load(93858);
    $hisotrys = $order->getAllStatusHistory();
    $hisotry = $hisotrys[2];
    $model = new Harapartners_HpSalesforce_Model_Export();
    $model->statusHistoryForOrder($hisotry);
}

if (false) {
    $order = Mage::getModel('sales/order')->load(93858);
    
    $model = new Harapartners_HpSalesforce_Model_Export();
    $model->orderCancel($order);
}
if(true){
    $rma = Mage::getModel('enterprise_rma/rma')->load(14);
    
    $model = new Harapartners_HpSalesforce_Model_Export();
    $model->rma($rma);
}

$break = 12323;