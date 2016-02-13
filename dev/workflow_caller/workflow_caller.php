<?php

// ========== Init setup ========== //
error_reporting(0);
require_once ('../../app/Mage.php');
Mage::app();

if (false) {
    /* @var $importModel Harapartners_HpOrderWorkflow_Model_Order */
    $order = Mage::GetModel('sales/order')->load(93822); // sh placed order
    $importModel = Mage::getModel('hporderworkflow/order');
    $importModel->processOrder($order);
}

if (false) {
    $data = array(
        'website_id' => 1 , 
        'store_id' => 1 , 
        'customer_group' => 4
    );
    //$importModel->proc ( 'ImportOrderComplete', './Orders_asc.csv', './Order_Items.csv', $data );
}

if(true){
	Mage::app()->setCurrentStore('admin');
	$model = new Harapartners_HpOrderWorkflow_Model_Inventory();
	$model->removeOldDropshipStock();
}