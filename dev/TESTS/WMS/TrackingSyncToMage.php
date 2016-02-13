<?php

// ========== Init setup ========== //
include '../HaraTestFunctions.php';
$testFunction = new HaraTestFunctions();
$testFunction->initMage();
$testFunction->setTestName('WMS Tracking Sync To Magento:');
$testFunction->outputStartMsg();

$params = Mage::app()->getRequest()->getParams();
if (true) {
    $testFunction->outputControlMessage('This function sends shipping trackinf information from the WMS to Magento.');
}

$importModel = new Harapartners_HpIntWms_Model_Import();
$importModel->processTrackings();
echo $importModel->getResultMsg();

$testFunction->outputEndMsg();