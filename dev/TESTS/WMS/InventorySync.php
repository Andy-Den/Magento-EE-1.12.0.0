<?php

// ========== Init setup ========== //
include '../HaraTestFunctions.php';
$testFunction = new HaraTestFunctions();
$testFunction->initMage();
$testFunction->setTestName('WMS Inventory Sync: ');
$testFunction->outputStartMsg();

if (true) {
    $testFunction->outputControlMessage('This function synchronizes the WMS inventory with Magento.');
}

$importModel = new Harapartners_HpIntWms_Model_Import();
$importModel->syncInventory();
//$importModel->syncInventory(false, 1);
echo $importModel->getResultMsg();

$testFunction->outputEndMsg();