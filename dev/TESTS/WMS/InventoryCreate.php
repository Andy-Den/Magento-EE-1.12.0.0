<?php

// ========== Init setup ========== //
include '../HaraTestFunctions.php';
$testFunction = new HaraTestFunctions();
$testFunction->initMage();
$testFunction->setTestName('WMS Inventory Creation:');
$testFunction->outputStartMsg();


$skuArray = Mage::app()->getRequest()->getParams();
if(empty($skuArray)){
	$testFunction->outputControlMessage('This function creates products in the WMS from Magento.');
	$testFunction->outputControlMessage('This function takes SKUs as parameters, e.g. InventoryCreate.php?name1=TIM_25629_9_W?name2=TIM_25629_9_M.');
	$testFunction->outputControlMessage('The names in name= are ignored. The function only creates the SKUs with all associated values in the WMS.');
}else{
	$exportModel = new Harapartners_HpIntWms_Model_Export();
	$errors = $exportModel->createItemBySkuArray($skuArray);
	echo $exportModel->getResultMsg();
}

$testFunction->outputEndMsg();