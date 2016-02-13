<?php

// ========== Init setup ========== //
include '../HaraTestFunctions.php';
$testFunction = new HaraTestFunctions();
$testFunction->initMage();
$testFunction->setTestName('Message:');
$testFunction->outputStartMsg();

$params = Mage::app()->getRequest()->getParams();
if (true) {
    $testFunction->outputWarningMessage('This function sends shipping tracking information from Magento to ChannelAdvisor.');
    $testFunction->outputControlMessage('No parameters needed.');
}

$exportModel = new Harapartners_HpChannelAdvisor_Model_Export();
$exportModel->postTrackingnumbers();
echo $exportModel->getResultMsg();

$testFunction->outputEndMsg();