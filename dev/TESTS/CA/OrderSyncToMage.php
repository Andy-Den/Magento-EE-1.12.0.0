<?php

// ========== Init setup ========== //
include '../HaraTestFunctions.php';
$testFunction = new HaraTestFunctions();
$testFunction->initMage();
$testFunction->setTestName('Message:');
$testFunction->outputStartMsg();

$params = Mage::app()->getRequest()->getParams();
if (true) {
    $testFunction->outputControlMessage('This function pull orders from ChannelAdvisor into Magento.');
    $testFunction->outputControlMessage('No parameters needed.');
}

$importModel = new Harapartners_HpChannelAdvisor_Model_Import();
$importModel->importNewCompleteOrders();
//echo $importModel->getResultMsg();

$testFunction->outputEndMsg();