<?php

// ========== Init setup ========== //
set_time_limit(1000);
include '../HaraTestFunctions.php';
$testFunction = new HaraTestFunctions();
$testFunction->initMage();
$testFunction->setTestName('EDI Inventory Sync:');
$testFunction->outputStartMsg();

if (true) {
    $testFunction->outputWarningMessage('This function synchronizes the EDI inventory with Magento.');
    $testFunction->outputWarningMessage('A file that matches the EDI 846 document pattern. (846*.txt).');
    $testFunction->outputWarningMessage('Please place the file into the /"in/" folder.');
    $testFunction->outputWarningMessage('This function may timeout if run from browser. It is ment to be run from the the command line via cron. Use small files to test in a browser.');
}

$isConfigDir = true;
$relativeDir = Mage::app()->getRequest()->getParam('location');
if ($relativeDir) {
    $isConfigDir = false;
}

// if is config is false then it uses relative Dir. To mage Root
$importModel = new Harapartners_ShoemartEdi_Model_Import();
$importModel->processEdiInventoryDocuments($isConfigDir, $relativeDir);

echo $importModel->getResultMsg();

$testFunction->outputEndMsg();