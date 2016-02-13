<?php

// ========== Init setup ========== //
include '../HaraTestFunctions.php';
$testFunction = new HaraTestFunctions();
$testFunction->initMage();
$testFunction->setTestName('RMA Sync To Magento:');
$testFunction->outputStartMsg();

$params = Mage::app()->getRequest()->getParams();
if (true) {
    $testFunction->outputControlMessage('This function check the /'actualQty/' in the WMS and completes an RMA in Magento if that value is greater than zero.');
}

$importModel = new Harapartners_HpIntWms_Model_Import();
$importModel->syncRmas();
echo $importModel->getResultMsg();

$testFunction->outputEndMsg();