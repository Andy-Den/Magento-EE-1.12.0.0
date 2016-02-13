<?php

// ========== Init setup ========== //
include '../HaraTestFunctions.php';
$testFunction = new HaraTestFunctions();
$testFunction->initMage();
$testFunction->setTestName('Message:');
$testFunction->outputStartMsg();

if (true) {
    $testFunction->outputWarningMessage('This function synchronizes the inventory quantity and pricing from Magento to ChannelAdvisor.');
    $testFunction->outputControlMessage('Optional Parameter is time = #_of_seconds back in time, e.g. , e.g. InventorySync.php?time=300 for the last 5 minutes.');
    $testFunction->outputWarningMessage('The default value is 5 minutes.');
    $testFunction->outputWarningMessage('Use the parameter "time=full" for a full sync, i.e. since the begining of time so to say.');
}

$time = Mage::app()->getRequest()->getParam('time');
if ($time == 'full') {
    $testFunction->outputWarningMessage('You chose to do a full Sync.');
    $syncTime = 1; // 30 or so years ago
} elseif (is_numeric($time)) {
    $testFunction->outputWarningMessage('You chose to sync the last ' . $time . ' seconds.');
    $syncTime = time() - $time;
} else {
    $testFunction->outputWarningMessage('You did not provide any parameter and it will sync the last 5 minutes.');
    $syncTime = time() - (60 * 5);
}
$exportModel = new Harapartners_HpChannelAdvisor_Model_Export();
$exportModel->fullInventorySync($syncTime); // this is 5 min ago
//echo $exportModel->getResultMsg();


$testFunction->outputEndMsg();